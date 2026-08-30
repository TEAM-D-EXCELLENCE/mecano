<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_services_returns_only_active_services_in_order(): void
    {
        Service::factory()->active()->create(['title' => 'Second', 'position' => 2]);
        Service::factory()->active()->create(['title' => 'First', 'position' => 1]);
        Service::factory()->inactive()->create(['title' => 'Hidden', 'position' => 3]);

        $response = $this->getJson('/api/v1/services');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'First')
            ->assertJsonPath('data.1.title', 'Second');

        // is_active must NOT be exposed in the public endpoint
        $this->assertArrayNotHasKey('is_active', $response->json('data.0'));
        $this->assertArrayNotHasKey('posts_count', $response->json('data.0'));
    }

    public function test_admin_services_list_requires_auth(): void
    {
        $this->getJson('/api/v1/admin/services')->assertStatus(401);
        $this->postJson('/api/v1/admin/services', [])->assertStatus(401);
        $this->patchJson('/api/v1/admin/services/1', [])->assertStatus(401);
    }

    public function test_admin_services_list_includes_inactive_and_posts_count(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        Service::factory()->active()->create(['position' => 1]);
        Service::factory()->inactive()->create(['position' => 2]);

        $response = $this->withToken($token)->getJson('/api/v1/admin/services');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [['id', 'slug', 'is_active', 'posts_count']]]);
    }

    public function test_admin_can_create_service_with_auto_slug(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/admin/services', [
            'title' => 'Diagnostic électronique avancé',
            'excerpt' => 'Analyse complète des calculateurs.',
            'price_from_xaf' => 25000,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.slug', 'diagnostic-electronique-avance')
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('services', ['slug' => 'diagnostic-electronique-avance']);
    }

    public function test_admin_can_deactivate_service_without_deleting_it(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $service = Service::factory()->active()->create();

        $response = $this->withToken($token)->patchJson("/api/v1/admin/services/{$service->id}", [
            'is_active' => false,
        ]);

        $response->assertOk()->assertJsonPath('data.is_active', false);

        // Service still exists in DB (not deleted)
        $this->assertDatabaseHas('services', ['id' => $service->id, 'is_active' => false]);
    }

    public function test_admin_can_update_service_attributes(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $service = Service::factory()->active()->create(['price_from_xaf' => 20000]);

        $response = $this->withToken($token)->patchJson("/api/v1/admin/services/{$service->id}", [
            'title' => 'Nouveau titre',
            'price_from_xaf' => 35000,
            'icon' => 'wrench',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Nouveau titre')
            ->assertJsonPath('data.price_from_xaf', 35000)
            ->assertJsonPath('data.icon', 'wrench');
    }
}
