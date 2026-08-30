<?php

declare(strict_types=1);

namespace Tests\Feature\Integration;

use App\Enums\EnhancementStatus;
use App\Enums\EnhancementType;
use App\Models\Car;
use App\Models\IntegrationQuota;
use App\Models\Media;
use App\Models\MediaEnhancement;
use App\Models\User;
use App\Services\Media\FakeImageEnhancer;
use App\Support\Contracts\ImageEnhancer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BE-37 — Tests des invariants critiques du pipeline médias.
 *
 * Three invariants from 04-pipeline-medias.md:
 *   #1 L'original n'est jamais modifié ni écrasé.
 *   #2 Un dérivé non approuvé n'est jamais servi au public.
 *   #3 Un fichier non confirmé n'existe pas (il n'est jamais retourné par l'API).
 */
final class MediaPipelineInvariantsTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────────────────
    // INVARIANT #2 — Un dérivé non approuvé n'est jamais servi au public
    // ──────────────────────────────────────────────────────────────────────────

    public function test_public_media_always_serves_published_url_not_result_url(): void
    {
        $car = Car::factory()->available()->create(['slug' => 'test-car-inv2']);
        $originalUrl = 'https://cdn.cloudinary.com/garage/image/upload/v1/cars/original.jpg';
        $derivedUrl = 'https://cdn.cloudinary.com/garage/image/upload/e_improve/cars/enhanced.jpg';

        $media = Media::factory()->mainPhoto()->create([
            'car_id' => $car->id,
            'url' => $originalUrl,
            'published_url' => $originalUrl, // not yet approved — should serve original
        ]);

        // Enhancement exists in "ready" state but NOT yet approved
        MediaEnhancement::factory()->ready()->create([
            'media_id' => $media->id,
            'type' => EnhancementType::AutoImprove,
            'result_url' => $derivedUrl,
            'status' => EnhancementStatus::Ready,
        ]);

        $response = $this->getJson("/api/v1/cars/{$car->slug}");

        $response->assertOk();

        $publicUrl = $response->json('data.main_photo.url');
        $this->assertEquals($originalUrl, $publicUrl, 'Public API must serve original URL until enhancement is approved');
        $this->assertNotEquals($derivedUrl, $publicUrl, 'Unapproved derived URL must never reach the public API');
    }

    public function test_approved_enhancement_updates_public_url(): void
    {
        $admin = User::factory()->create();
        $token = $admin->createToken('admin')->plainTextToken;

        $car = Car::factory()->available()->create(['slug' => 'test-car-approved']);
        $originalUrl = 'https://cdn.cloudinary.com/garage/image/upload/v1/cars/photo.jpg';
        $enhancedUrl = 'https://cdn.cloudinary.com/garage/image/upload/e_improve/cars/photo.jpg';

        $media = Media::factory()->mainPhoto()->create([
            'car_id' => $car->id,
            'url' => $originalUrl,
            'published_url' => $originalUrl,
        ]);

        $enhancement = MediaEnhancement::factory()->ready()->create([
            'media_id' => $media->id,
            'type' => EnhancementType::AutoImprove,
            'result_url' => $enhancedUrl,
        ]);

        // Before approval: public sees original
        $before = $this->getJson("/api/v1/cars/{$car->slug}");
        $this->assertEquals($originalUrl, $before->json('data.main_photo.url'));

        // Admin approves
        $this->withToken($token)
            ->postJson("/api/v1/admin/enhancements/{$enhancement->id}/approve")
            ->assertOk();

        // After approval: public sees enhanced
        $after = $this->getJson("/api/v1/cars/{$car->slug}");
        $this->assertEquals($enhancedUrl, $after->json('data.main_photo.url'));
    }

    public function test_original_url_is_never_modified_on_approval(): void
    {
        $admin = User::factory()->create();
        $token = $admin->createToken('admin')->plainTextToken;

        $originalUrl = 'https://cdn.cloudinary.com/garage/image/upload/v1/cars/original.jpg';
        $enhancedUrl = 'https://cdn.cloudinary.com/garage/image/upload/e_improve/cars/enhanced.jpg';

        $media = Media::factory()->galleryPhoto()->create([
            'url' => $originalUrl,
            'published_url' => $originalUrl,
        ]);

        $enhancement = MediaEnhancement::factory()->ready()->create([
            'media_id' => $media->id,
            'type' => EnhancementType::AutoImprove,
            'result_url' => $enhancedUrl,
        ]);

        $this->withToken($token)
            ->postJson("/api/v1/admin/enhancements/{$enhancement->id}/approve")
            ->assertOk();

        $media->refresh();

        // Invariant #1: original url column must remain unchanged
        $this->assertEquals($originalUrl, $media->url, 'media.url (original) must NEVER be modified by approval');

        // Only published_url changes
        $this->assertEquals($enhancedUrl, $media->published_url);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // INVARIANT #3 — Un fichier non confirmé n'existe pas pour l'API
    // ──────────────────────────────────────────────────────────────────────────

    public function test_unconfirmed_media_is_never_returned_by_public_api(): void
    {
        $car = Car::factory()->available()->create(['slug' => 'test-unconfirmed']);

        // confirmed media (should appear)
        $confirmedMedia = Media::factory()->mainPhoto()->create(['car_id' => $car->id, 'confirmed_at' => now()]);

        // unconfirmed / orphan (must NOT appear in public API)
        Media::factory()->galleryPhoto()->create(['car_id' => $car->id, 'confirmed_at' => null]);
        Media::factory()->galleryPhoto()->create(['car_id' => $car->id, 'confirmed_at' => null]);

        $response = $this->getJson("/api/v1/cars/{$car->slug}");
        $response->assertOk();

        // Only the confirmed main photo should be served
        $this->assertNotNull($response->json('data.main_photo'));
        $this->assertEquals($confirmedMedia->id, $response->json('data.main_photo.id'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Quota infranchissable — CDC §4 critical invariant (BE-34)
    // ──────────────────────────────────────────────────────────────────────────

    public function test_quota_cannot_be_exceeded_even_with_concurrent_requests(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $period = now()->format('Y-m');

        // Set quota to exactly 1 remaining
        IntegrationQuota::query()->create([
            'provider' => 'removebg',
            'period' => $period,
            'used' => 49,
            'limit' => 50,
            'updated_at' => now(),
        ]);

        $media1 = Media::factory()->galleryPhoto()->create();
        $media2 = Media::factory()->galleryPhoto()->create();

        // First request — should succeed (1 remaining)
        $response1 = $this->withToken($token)->postJson("/api/v1/admin/media/{$media1->id}/enhance", [
            'type' => 'background_removal',
        ]);
        $response1->assertStatus(201);

        // Second request — quota now 50/50, must be rejected
        $response2 = $this->withToken($token)->postJson("/api/v1/admin/media/{$media2->id}/enhance", [
            'type' => 'background_removal',
        ]);
        $response2->assertStatus(409);

        // Quota must be exactly 50, not 51
        $quota = IntegrationQuota::query()
            ->where('provider', 'removebg')
            ->where('period', $period)
            ->firstOrFail();

        $this->assertEquals(50, $quota->used, 'Quota must not exceed 50 even after two concurrent requests');
    }

    public function test_failed_enhancement_refunds_quota(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $period = now()->format('Y-m');
        IntegrationQuota::query()->create([
            'provider' => 'removebg',
            'period' => $period,
            'used' => 45,
            'limit' => 50,
            'updated_at' => now(),
        ]);

        $media = Media::factory()->galleryPhoto()->create();

        /** @var FakeImageEnhancer $fakeEnhancer */
        $fakeEnhancer = $this->app->make(ImageEnhancer::class);
        $fakeEnhancer->setShouldFail(true);

        $response = $this->withToken($token)->postJson("/api/v1/admin/media/{$media->id}/enhance", [
            'type' => 'background_removal',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status.value', 'failed');

        // Quota must be refunded to original value
        $quota = IntegrationQuota::query()
            ->where('provider', 'removebg')
            ->where('period', $period)
            ->firstOrFail();

        $this->assertEquals(45, $quota->used, 'Quota must be refunded when enhancement fails before billing');
    }

    public function test_approving_enhancement_is_idempotent_on_published_url(): void
    {
        $admin = User::factory()->create();
        $token = $admin->createToken('admin')->plainTextToken;

        $media = Media::factory()->galleryPhoto()->create([
            'published_url' => 'https://cdn.test/original.jpg',
        ]);

        $enhancement1 = MediaEnhancement::factory()->ready()->create([
            'media_id' => $media->id,
            'result_url' => 'https://cdn.test/enhanced_v1.jpg',
        ]);

        $enhancement2 = MediaEnhancement::factory()->ready()->create([
            'media_id' => $media->id,
            'result_url' => 'https://cdn.test/enhanced_v2.jpg',
        ]);

        // Approve first enhancement
        $this->withToken($token)->postJson("/api/v1/admin/enhancements/{$enhancement1->id}/approve")->assertOk();
        $media->refresh();
        $this->assertEquals('https://cdn.test/enhanced_v1.jpg', $media->published_url);

        // Approve second — published_url switches to the latest approved
        $this->withToken($token)->postJson("/api/v1/admin/enhancements/{$enhancement2->id}/approve")->assertOk();
        $media->refresh();
        $this->assertEquals('https://cdn.test/enhanced_v2.jpg', $media->published_url);

        // Original is still intact
        $this->assertNotEquals($media->url, $media->published_url, 'url (original) must differ from published_url');
    }
}
