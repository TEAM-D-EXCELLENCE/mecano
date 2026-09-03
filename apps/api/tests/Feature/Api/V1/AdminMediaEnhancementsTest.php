<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\EnhancementStatus;
use App\Enums\EnhancementType;
use App\Models\IntegrationQuota;
use App\Models\Media;
use App\Models\MediaEnhancement;
use App\Models\User;
use App\Services\Media\FakeImageEnhancer;
use App\Support\Contracts\ImageEnhancer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminMediaEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_enhancement_endpoints_require_auth(): void
    {
        $this->postJson('/api/v1/admin/media/1/enhance', [])->assertStatus(401);
        $this->getJson('/api/v1/admin/media/1/enhancements')->assertStatus(401);
        $this->postJson('/api/v1/admin/enhancements/1/approve')->assertStatus(401);
    }

    public function test_auto_improve_enhancement_is_free_and_ready(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;
        $media = Media::factory()->galleryPhoto()->create();

        $response = $this->withToken($token)->postJson("/api/v1/admin/media/{$media->id}/enhance", [
            'type' => 'auto_improve',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type.value', 'auto_improve')
            ->assertJsonPath('data.status.value', 'ready')
            ->assertJsonPath('data.cost_units', 0)
            ->assertJsonStructure(['data' => ['id', 'result_url', 'status']]);

        $this->assertDatabaseHas('media_enhancements', [
            'media_id' => $media->id,
            'type' => 'auto_improve',
            'status' => 'ready',
            'cost_units' => 0,
        ]);
    }

    public function test_background_removal_consumes_quota(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;
        $media = Media::factory()->galleryPhoto()->create();

        $response = $this->withToken($token)->postJson("/api/v1/admin/media/{$media->id}/enhance", [
            'type' => 'background_removal',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type.value', 'background_removal')
            ->assertJsonPath('data.status.value', 'ready')
            ->assertJsonPath('data.cost_units', 1);

        $period = now()->format('Y-m');
        $quota = IntegrationQuota::query()
            ->where('provider', 'removebg')
            ->where('period', $period)
            ->firstOrFail();

        $this->assertEquals(1, $quota->used);
    }

    public function test_background_removal_returns_409_when_quota_exhausted(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;
        $media = Media::factory()->galleryPhoto()->create();

        $period = now()->format('Y-m');
        IntegrationQuota::query()->create([
            'provider' => 'removebg',
            'period' => $period,
            'used' => 50,
            'limit' => 50,
            'updated_at' => now(),
        ]);

        $response = $this->withToken($token)->postJson("/api/v1/admin/media/{$media->id}/enhance", [
            'type' => 'background_removal',
        ]);

        $response->assertStatus(409);
    }

    public function test_background_removal_refunds_quota_on_enhancer_failure(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;
        $media = Media::factory()->galleryPhoto()->create();

        /** @var FakeImageEnhancer $fakeEnhancer */
        $fakeEnhancer = $this->app->make(ImageEnhancer::class);
        $fakeEnhancer->setShouldFail(true);

        $response = $this->withToken($token)->postJson("/api/v1/admin/media/{$media->id}/enhance", [
            'type' => 'background_removal',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status.value', 'failed');

        // Quota refunded: used should be 0
        $period = now()->format('Y-m');
        $quota = IntegrationQuota::query()
            ->where('provider', 'removebg')
            ->where('period', $period)
            ->firstOrFail();

        $this->assertEquals(0, $quota->used);
    }

    public function test_approving_enhancement_updates_published_url(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;
        $media = Media::factory()->galleryPhoto()->create();

        $enhancement = MediaEnhancement::factory()->ready()->create([
            'media_id' => $media->id,
            'type' => EnhancementType::AutoImprove,
            'result_url' => 'https://cdn.test/enhanced/v2.jpg',
        ]);

        $response = $this->withToken($token)->postJson("/api/v1/admin/enhancements/{$enhancement->id}/approve");

        $response->assertOk()
            ->assertJsonPath('data.status.value', 'approved');

        // media.published_url updated
        $media->refresh();
        $this->assertEquals('https://cdn.test/enhanced/v2.jpg', $media->published_url);
    }

    public function test_approving_non_ready_enhancement_returns_422(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $enhancement = MediaEnhancement::factory()->create([
            'status' => EnhancementStatus::Pending,
        ]);

        $this->withToken($token)
            ->postJson("/api/v1/admin/enhancements/{$enhancement->id}/approve")
            ->assertStatus(422);
    }

    public function test_list_enhancements_for_media(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;
        $media = Media::factory()->galleryPhoto()->create();

        MediaEnhancement::factory()->count(3)->create(['media_id' => $media->id]);

        $response = $this->withToken($token)->getJson("/api/v1/admin/media/{$media->id}/enhancements");

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_quotas_endpoint_returns_removebg_usage(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $period = now()->format('Y-m');
        IntegrationQuota::query()->create([
            'provider' => 'removebg',
            'period' => $period,
            'used' => 12,
            'limit' => 50,
            'updated_at' => now(),
        ]);

        $response = $this->withToken($token)->getJson('/api/v1/admin/quotas');

        $response->assertOk()
            ->assertJsonPath('data.provider', 'removebg')
            ->assertJsonPath('data.used', 12)
            ->assertJsonPath('data.limit', 50)
            ->assertJsonPath('data.available', 38);
    }
}
