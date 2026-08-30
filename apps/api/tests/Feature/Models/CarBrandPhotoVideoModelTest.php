<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Enums\CarStatus;
use App\Enums\FuelType;
use App\Enums\MediaKind;
use App\Enums\MediaProvider;
use App\Enums\MediaRole;
use App\Enums\TransmissionType;
use App\Enums\VehicleCondition;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Photo;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CarBrandPhotoVideoModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_photo_model_auto_scopes_and_sets_kind(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->for($brand)->create();

        // Create Photo instance
        $photo = Photo::query()->create([
            'car_id' => $car->id,
            'role' => MediaRole::Main,
            'provider' => MediaProvider::Cloudinary,
            'storage_key' => 'cars/sample-photo',
            'url' => 'https://res.cloudinary.com/garage/sample.jpg',
            'mime' => 'image/jpeg',
            'bytes' => 200000,
            'width' => 1920,
            'height' => 1080,
            'position' => 0,
            'confirmed_at' => now(),
        ]);

        $this->assertSame(MediaKind::Photo, $photo->kind);
        $this->assertCount(1, Photo::all());

        // Create Video instance
        $video = Video::query()->create([
            'car_id' => $car->id,
            'role' => MediaRole::VideoInterior,
            'provider' => MediaProvider::R2,
            'storage_key' => 'videos/sample-video.mp4',
            'url' => 'https://media.garage.com/sample.mp4',
            'mime' => 'video/mp4',
            'bytes' => 15000000,
            'duration_s' => 30,
            'position' => 10,
            'confirmed_at' => now(),
        ]);

        $this->assertSame(MediaKind::Video, $video->kind);
        $this->assertCount(1, Video::all());

        // Verify distinct scopes
        $this->assertCount(1, Photo::all());
        $this->assertCount(1, Video::all());
    }

    public function test_car_relations_and_is_publishable_business_logic(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->for($brand)->create();

        $this->assertFalse($car->isPublishable());

        // Add gallery photo (not main) -> still not publishable
        Photo::query()->create([
            'car_id' => $car->id,
            'role' => MediaRole::Gallery,
            'provider' => MediaProvider::Cloudinary,
            'storage_key' => 'cars/gallery-1',
            'url' => 'https://res.cloudinary.com/garage/gallery-1.jpg',
            'mime' => 'image/jpeg',
            'bytes' => 180000,
            'position' => 1,
        ]);

        $car->unsetRelation('mainPhoto');
        $this->assertFalse($car->isPublishable());

        // Add main photo -> becomes publishable
        $mainPhoto = Photo::query()->create([
            'car_id' => $car->id,
            'role' => MediaRole::Main,
            'provider' => MediaProvider::Cloudinary,
            'storage_key' => 'cars/main-1',
            'url' => 'https://res.cloudinary.com/garage/main-1.jpg',
            'mime' => 'image/jpeg',
            'bytes' => 250000,
            'position' => 0,
        ]);

        $car->unsetRelation('mainPhoto');
        $this->assertTrue($car->isPublishable());
        $this->assertSame($mainPhoto->id, $car->mainPhoto->id);
    }

    public function test_enums_expose_french_labels_and_array_format(): void
    {
        $status = CarStatus::Available;
        $this->assertSame('Disponible', $status->label());
        $this->assertSame(['value' => 'available', 'label' => 'Disponible'], $status->toArray());

        $fuel = FuelType::Diesel;
        $this->assertSame('Diesel', $fuel->label());
        $this->assertSame(['value' => 'diesel', 'label' => 'Diesel'], $fuel->toArray());

        $transmission = TransmissionType::Automatique;
        $this->assertSame('Automatique', $transmission->label());
        $this->assertSame(['value' => 'automatique', 'label' => 'Automatique'], $transmission->toArray());

        $condition = VehicleCondition::Excellent;
        $this->assertSame('Excellent état', $condition->label());
        $this->assertSame(['value' => 'excellent', 'label' => 'Excellent état'], $condition->toArray());
    }
}
