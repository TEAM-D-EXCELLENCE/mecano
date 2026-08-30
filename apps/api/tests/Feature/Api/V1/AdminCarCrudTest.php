<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Brand;
use App\Models\Car;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminCarCrudTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Auth guard
    // -------------------------------------------------------------------------

    public function test_admin_cars_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/cars')->assertStatus(401);
        $this->postJson('/api/v1/admin/cars', [])->assertStatus(401);
        $this->getJson('/api/v1/admin/cars/1')->assertStatus(401);
        $this->patchJson('/api/v1/admin/cars/1', [])->assertStatus(401);
        $this->deleteJson('/api/v1/admin/cars/1')->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function test_admin_cars_index_includes_drafts_and_all_statuses(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $brand = Brand::factory()->create();
        Car::factory()->draft()->for($brand)->create(['model' => 'DraftCar']);
        Car::factory()->available()->for($brand)->create(['model' => 'AvailableCar']);
        Car::factory()->sold()->for($brand)->create(['model' => 'SoldCar']);

        $response = $this->withToken($token)->getJson('/api/v1/admin/cars');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_admin_cars_index_can_filter_by_status(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $brand = Brand::factory()->create();
        Car::factory()->draft()->for($brand)->create();
        Car::factory()->available()->for($brand)->create();

        $response = $this->withToken($token)->getJson('/api/v1/admin/cars?status=draft');

        $response->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status.value', 'draft');
    }

    public function test_admin_cars_index_can_search_by_keyword(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $brand = Brand::factory()->create(['name' => 'Hyundai']);
        Car::factory()->available()->for($brand)->create(['model' => 'Tucson']);
        Car::factory()->available()->for($brand)->create(['model' => 'Elantra']);

        $response = $this->withToken($token)->getJson('/api/v1/admin/cars?recherche=Tucson');

        $response->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.model', 'Tucson');
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_admin_can_create_car_with_immutable_slug(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $brand = Brand::factory()->create(['name' => 'Toyota', 'slug' => 'toyota']);

        $response = $this->withToken($token)->postJson('/api/v1/admin/cars', [
            'brand_id' => $brand->id,
            'model' => 'Corolla',
            'year' => 2020,
            'mileage_km' => 50000,
            'price_xaf' => 8500000,
            'fuel' => 'essence',
            'transmission' => 'automatique',
            'color' => 'Blanc',
            'condition' => 'excellent',
            'description' => 'Bonne voiture.',
        ]);

        $response->assertStatus(201);

        $slug = $response->json('data.slug');
        $this->assertStringContainsString('toyota-corolla-2020-', $slug);
        // Slug includes car ID as final suffix (immutable)
        $carId = $response->json('data.id');
        $this->assertStringEndsWith((string) $carId, $slug);

        // Status defaults to draft
        $this->assertSame('draft', $response->json('data.status.value'));
        $this->assertDatabaseHas('cars', ['slug' => $slug, 'status' => 'draft']);
    }

    public function test_admin_car_creation_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/admin/cars', []);

        $response->assertStatus(422)
            ->assertJson(['error' => ['code' => 'VALIDATION_FAILED']]);

        $details = $response->json('error.details');
        $this->assertArrayHasKey('brand_id', $details);
        $this->assertArrayHasKey('model', $details);
        $this->assertArrayHasKey('price_xaf', $details);
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_admin_can_show_car_detail(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $brand = Brand::factory()->create();
        $car = Car::factory()->available()->for($brand)->create();
        Photo::factory()->mainPhoto()->for($car)->create();

        $response = $this->withToken($token)->getJson("/api/v1/admin/cars/{$car->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'slug', 'brand', 'model', 'year', 'price_xaf',
                    'status', 'is_publishable', 'views_count', 'whatsapp_clicks_count',
                    'main_photo', 'photos', 'videos',
                    'created_at', 'updated_at', 'deleted_at',
                ],
            ]);
    }

    public function test_admin_show_returns_404_for_unknown_car(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/admin/cars/999999')
            ->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_admin_can_update_car_attributes_but_not_slug(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $brand = Brand::factory()->create();
        $car = Car::factory()->available()->for($brand)->create([
            'price_xaf' => 5000000,
            'slug' => 'toyota-corolla-2020-99',
        ]);

        $response = $this->withToken($token)->patchJson("/api/v1/admin/cars/{$car->id}", [
            'price_xaf' => 7500000,
            'mileage_km' => 60000,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.price_xaf', 7500000);

        // Slug must remain immutable
        $this->assertSame('toyota-corolla-2020-99', $response->json('data.slug'));
        $this->assertDatabaseHas('cars', ['id' => $car->id, 'price_xaf' => 7500000]);
    }

    // -------------------------------------------------------------------------
    // Delete (soft)
    // -------------------------------------------------------------------------

    public function test_admin_can_soft_delete_car(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $brand = Brand::factory()->create();
        $car = Car::factory()->available()->for($brand)->create();

        $this->withToken($token)->deleteJson("/api/v1/admin/cars/{$car->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('cars', ['id' => $car->id]);

        // Public endpoint no longer returns soft-deleted car
        $this->getJson("/api/v1/cars/{$car->slug}")->assertStatus(404);
    }
}
