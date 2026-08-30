<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\CarEventType;
use App\Enums\CarStatus;
use App\Enums\MediaKind;
use App\Enums\MediaRole;
use App\Models\Brand;
use App\Models\Car;
use App\Models\CarEvent;
use App\Models\Media;
use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SeederAndFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_runs_successfully_and_is_idempotent(): void
    {
        // First run
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('brands', 16);
        $this->assertDatabaseCount('cars', 15);
        $this->assertTrue(Media::query()->count() >= 15);
        $this->assertDatabaseHas('settings', ['key' => 'whatsapp_number']);

        // Second run must be idempotent without primary/unique key collision
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('brands', 16);
        $this->assertDatabaseCount('cars', 15);
    }

    public function test_seeded_cars_contain_all_required_edge_cases(): void
    {
        $this->seed(DatabaseSeeder::class);

        // 1. Available cars exist
        $this->assertTrue(Car::query()->where('status', CarStatus::Available)->count() >= 8);

        // 2. Reserved car exists
        $reservedCar = Car::query()->where('status', CarStatus::Reserved)->first();
        $this->assertNotNull($reservedCar);

        // 3. Sold cars exist with valid sold_at date
        $soldCars = Car::query()->where('status', CarStatus::Sold)->get();
        $this->assertCount(2, $soldCars);
        foreach ($soldCars as $soldCar) {
            $this->assertNotNull($soldCar->sold_at);
            $this->assertNotNull($soldCar->published_at);
        }

        // 4. Draft car exists without published_at
        $draftCar = Car::query()->where('status', CarStatus::Draft)->first();
        $this->assertNotNull($draftCar);
        $this->assertNull($draftCar->published_at);

        // 5. Featured cars exist
        $this->assertTrue(Car::query()->where('is_featured', true)->count() >= 3);

        // 6. Null description car exists
        $nullDescCar = Car::query()->whereNull('description')->first();
        $this->assertNotNull($nullDescCar);

        // 7. Long description car exists
        $longDescCar = Car::query()->where('slug', 'volkswagen-tiguan-r-line-2020-13')->first();
        $this->assertNotNull($longDescCar);
        $this->assertStringContainsString('110 points', (string) $longDescCar->description);

        // 8. Every car in the seed has a main photo
        $cars = Car::query()->with('mainPhoto')->get();
        foreach ($cars as $car) {
            $this->assertNotNull($car->mainPhoto, "Car {$car->slug} must have a main photo");
            $this->assertSame(MediaRole::Main, $car->mainPhoto->role);
        }

        // 9. Cars with videos exist
        $carsWithVideos = Car::query()->whereHas('videos')->count();
        $this->assertTrue($carsWithVideos >= 3);

        // 10. Deactivated brand Chevrolet exists (D11)
        $inactiveBrand = Brand::query()->where('slug', 'chevrolet')->first();
        $this->assertNotNull($inactiveBrand);
        $this->assertFalse($inactiveBrand->is_active);
    }

    public function test_factories_create_valid_instances_with_relations(): void
    {
        $brand = Brand::factory()->create();
        $this->assertModelExists($brand);

        $car = Car::factory()->available()->for($brand)->create();
        $this->assertModelExists($car);
        $this->assertSame($brand->id, $car->brand_id);

        $mainPhoto = Media::factory()->mainPhoto()->for($car)->create();
        $this->assertModelExists($mainPhoto);
        $this->assertSame(MediaKind::Photo, $mainPhoto->kind);
        $this->assertSame(MediaRole::Main, $mainPhoto->role);

        $galleryPhoto = Media::factory()->galleryPhoto(1)->for($car)->create();
        $this->assertModelExists($galleryPhoto);

        $interiorVideo = Media::factory()->interiorVideo()->for($car)->create();
        $this->assertModelExists($interiorVideo);
        $this->assertSame(MediaKind::Video, $interiorVideo->kind);
        $this->assertSame(MediaRole::VideoInterior, $interiorVideo->role);

        $event = CarEvent::factory()->view()->for($car)->create();
        $this->assertModelExists($event);
        $this->assertSame(CarEventType::View, $event->type);
    }

    public function test_setting_model_helpers(): void
    {
        Setting::set('test_key', ['value' => 123]);

        $this->assertSame(['value' => 123], Setting::get('test_key'));
        $this->assertSame('default', Setting::get('non_existent', 'default'));
    }
}
