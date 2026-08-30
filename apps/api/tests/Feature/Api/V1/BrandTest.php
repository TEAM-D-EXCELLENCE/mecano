<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Brand;
use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_brands_endpoint_returns_only_active_brands_ordered_by_position(): void
    {
        Brand::factory()->create([
            'slug' => 'toyota',
            'name' => 'Toyota',
            'position' => 1,
            'is_active' => true,
        ]);

        Brand::factory()->create([
            'slug' => 'mercedes-benz',
            'name' => 'Mercedes-Benz',
            'position' => 2,
            'is_active' => true,
        ]);

        // Inactive brand must not appear in public list
        Brand::factory()->create([
            'slug' => 'chevrolet',
            'name' => 'Chevrolet',
            'position' => 0,
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/brands');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'toyota')
            ->assertJsonPath('data.1.slug', 'mercedes-benz');

        $data = $response->json('data');
        $this->assertArrayNotHasKey('is_active', $data[0]);
        $this->assertArrayNotHasKey('cars_count', $data[0]);
    }

    public function test_admin_brands_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/admin/brands');

        $response->assertStatus(401)
            ->assertJson([
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                ],
            ]);
    }

    public function test_admin_brands_endpoint_returns_all_brands_with_cars_count(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $brand1 = Brand::factory()->create([
            'slug' => 'toyota',
            'name' => 'Toyota',
            'position' => 1,
            'is_active' => true,
        ]);

        $brand2 = Brand::factory()->create([
            'slug' => 'chevrolet',
            'name' => 'Chevrolet',
            'position' => 2,
            'is_active' => false,
        ]);

        // Add cars to brand1
        Car::factory()->for($brand1)->count(3)->create();

        $response = $this->withToken($token)->getJson('/api/v1/admin/brands');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'toyota')
            ->assertJsonPath('data.0.cars_count', 3)
            ->assertJsonPath('data.0.is_active', true)
            ->assertJsonPath('data.1.slug', 'chevrolet')
            ->assertJsonPath('data.1.cars_count', 0)
            ->assertJsonPath('data.1.is_active', false);
    }

    public function test_admin_can_create_brand_with_auto_generated_slug(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/admin/brands', [
            'name' => 'Alfa Romeo',
            'position' => 12,
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Alfa Romeo')
            ->assertJsonPath('data.slug', 'alfa-romeo')
            ->assertJsonPath('data.position', 12)
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('brands', [
            'name' => 'Alfa Romeo',
            'slug' => 'alfa-romeo',
        ]);
    }

    public function test_admin_brand_creation_validates_required_fields_and_uniqueness(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        Brand::factory()->create(['slug' => 'lexus', 'name' => 'Lexus']);

        // Empty name
        $response = $this->withToken($token)->postJson('/api/v1/admin/brands', [
            'name' => '',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => [
                    'code' => 'VALIDATION_FAILED',
                    'message' => 'Certains champs sont invalides.',
                ],
            ]);

        // Duplicate slug
        $responseDuplicate = $this->withToken($token)->postJson('/api/v1/admin/brands', [
            'name' => 'Lexus Auto',
            'slug' => 'lexus',
        ]);

        $responseDuplicate->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_FAILED');
    }
}
