<?php

declare(strict_types=1);

namespace Tests\Feature\Integration;

use App\Models\Car;
use App\Models\Media;
use App\Models\Post;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\PostSeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WorkshopAndContentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_m2_service_and_post_lifecycle(): void
    {
        $admin = User::factory()->create();
        $token = $admin->createToken('admin')->plainTextToken;

        // 1. Admin creates two services
        $service1Response = $this->withToken($token)->postJson('/api/v1/admin/services', [
            'title' => 'Diagnostic Électronique',
            'excerpt' => 'Analyse des calculateurs et codes défauts.',
            'description' => 'Diagnostic approfondi avec valise constructeur.',
            'icon' => 'laptop-medical',
            'price_from_xaf' => 25000,
            'position' => 2,
            'is_active' => true,
        ]);
        $service1Response->assertCreated();
        $service1Id = $service1Response->json('data.id');
        $service1Slug = $service1Response->json('data.slug');

        $service2Response = $this->withToken($token)->postJson('/api/v1/admin/services', [
            'title' => 'Entretien & Vidange',
            'excerpt' => 'Forfaits révision et vidange moteur.',
            'price_from_xaf' => 35000,
            'position' => 1,
            'is_active' => true,
        ]);
        $service2Response->assertCreated();
        $service2Id = $service2Response->json('data.id');
        $service2Slug = $service2Response->json('data.slug');

        // 2. Public view verifies ordering by position (service2 position 1 comes first)
        $publicServices = $this->getJson('/api/v1/services');
        $publicServices->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', $service2Slug)
            ->assertJsonPath('data.1.slug', $service1Slug);

        // 3. Admin creates a draft post linked to service 1
        $draftResponse = $this->withToken($token)->postJson('/api/v1/admin/posts', [
            'title' => 'Pourquoi faire un diagnostic avant un long trajet ?',
            'excerpt' => 'Les 5 points critiques à vérifier.',
            'body' => 'Contenu détaillé expliquant les risques et les solutions...',
            'service_id' => $service1Id,
            'status' => 'draft',
        ]);
        $draftResponse->assertCreated();
        $postSlug = $draftResponse->json('data.slug');
        $postId = $draftResponse->json('data.id');

        // 4. Verification: Draft is invisible to public
        $this->getJson('/api/v1/posts')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson("/api/v1/posts/{$postSlug}")->assertNotFound();

        // 5. Admin publishes the post and attaches a cover image
        $car = Car::factory()->create();
        $coverMedia = Media::factory()->galleryPhoto()->create([
            'car_id' => $car->id,
        ]);

        $publishResponse = $this->withToken($token)->patchJson("/api/v1/admin/posts/{$postId}", [
            'status' => 'published',
            'cover_media_id' => $coverMedia->id,
        ]);
        $publishResponse->assertOk()
            ->assertJsonPath('data.status.value', 'published');

        // 6. Public can now read the published post in list and detail
        $publicPosts = $this->getJson('/api/v1/posts');
        $publicPosts->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $postSlug)
            ->assertJsonPath('data.0.service.slug', $service1Slug);

        $detailResponse = $this->getJson("/api/v1/posts/{$postSlug}");
        $detailResponse->assertOk()
            ->assertJsonPath('data.slug', $postSlug)
            ->assertJsonPath('data.service.title', 'Diagnostic Électronique')
            ->assertJsonPath('data.cover_media.url', $coverMedia->published_url ?? $coverMedia->url);

        // 7. Filter public posts by service
        $this->getJson("/api/v1/posts?service={$service1Slug}")->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/v1/posts?service={$service2Slug}")->assertOk()->assertJsonCount(0, 'data');

        // 8. Admin posts_count reflects the published post on service1
        $adminServices = $this->withToken($token)->getJson('/api/v1/admin/services');
        $adminServices->assertOk();
        $service1Data = collect($adminServices->json('data'))->firstWhere('id', $service1Id);
        $this->assertEquals(1, $service1Data['posts_count']);

        // 9. Admin deactivates service 1 -> It leaves the public services listing
        $this->withToken($token)->patchJson("/api/v1/admin/services/{$service1Id}", [
            'is_active' => false,
        ])->assertOk();

        $updatedPublicServices = $this->getJson('/api/v1/services');
        $updatedPublicServices->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $service2Slug);

        // But post is still accessible publicly (immutable content preserved)
        $this->getJson("/api/v1/posts/{$postSlug}")->assertOk();
    }

    public function test_seeders_populate_services_and_posts_correctly(): void
    {
        $this->seed(ServiceSeeder::class);
        $this->seed(PostSeeder::class);

        $this->assertGreaterThanOrEqual(6, Service::query()->count());
        $this->assertGreaterThanOrEqual(5, Post::query()->count());

        // Ensure active services and published posts are visible via public API
        $servicesResponse = $this->getJson('/api/v1/services');
        $servicesResponse->assertOk();
        $this->assertNotEmpty($servicesResponse->json('data'));

        $postsResponse = $this->getJson('/api/v1/posts');
        $postsResponse->assertOk();
        $this->assertNotEmpty($postsResponse->json('data'));
    }
}
