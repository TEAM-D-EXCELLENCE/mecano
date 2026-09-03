<?php

declare(strict_types=1);

namespace Tests\Feature\Revalidation;

use App\Enums\CarStatus;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Media;
use App\Models\Service;
use App\Models\User;
use App\Services\Revalidation\FakeFrontendRevalidator;
use App\Support\Contracts\FrontendRevalidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RevalidationTriggersTest extends TestCase
{
    use RefreshDatabase;

    private FakeFrontendRevalidator $revalidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->revalidator = app(FrontendRevalidator::class);
        $this->revalidator->reset();
    }

    #[Test]
    public function car_status_change_revalidates_car_and_catalog_and_home_tags(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $brand = Brand::factory()->create();
        $car = Car::factory()->create([
            'brand_id' => $brand->id,
            'status' => CarStatus::Draft,
        ]);

        Media::factory()->mainPhoto()->create([
            'car_id' => $car->id,
        ]);

        $this->patchJson("/api/v1/admin/cars/{$car->id}/status", [
            'status' => 'available',
        ])->assertOk();

        $this->revalidator->assertRevalidated([
            "car:{$car->slug}",
            'cars',
            'home',
        ]);
    }

    #[Test]
    public function service_creation_and_update_revalidates_services_tag(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/admin/services', [
            'title' => 'Diagnostic Électronique',
            'excerpt' => 'Diagnostic complet de votre véhicule',
            'description' => 'Recherche approfondie des pannes',
            'icon' => 'wrench',
            'price_from_xaf' => 15000,
            'is_active' => true,
            'position' => 1,
        ])->assertCreated();

        $this->revalidator->assertRevalidated(['services']);
    }

    #[Test]
    public function post_creation_and_update_revalidates_post_and_posts_tags(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $service = Service::factory()->create();

        $response = $this->postJson('/api/v1/admin/posts', [
            'title' => 'Comment entretenir sa climatisation',
            'excerpt' => 'Les bons réflexes pour l\'été',
            'body' => 'La climatisation nécessite un contrôle annuel...',
            'service_id' => $service->id,
            'status' => 'published',
        ])->assertCreated();

        $slug = (string) $response->json('data.slug');

        $this->revalidator->assertRevalidated([
            "post:{$slug}",
            'posts',
        ]);
    }

    #[Test]
    public function setting_update_revalidates_settings_and_home_tags(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/admin/settings', [
            'settings' => [
                'garage_phone' => '+237690000000',
            ],
        ])->assertOk();

        $this->revalidator->assertRevalidated([
            'settings',
            'home',
        ]);
    }
}
