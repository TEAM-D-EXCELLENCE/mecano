<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Brand;
use App\Models\Car;
use App\Models\Photo;
use App\Models\Setting;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CarPublicEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_cars_index_returns_paginated_list_with_standard_shapes(): void
    {
        $brand = Brand::factory()->create(['slug' => 'toyota', 'name' => 'Toyota']);

        $car = Car::factory()->available()->for($brand)->create([
            'slug' => 'toyota-corolla-2020-1',
            'model' => 'Corolla',
            'year' => 2020,
            'price_xaf' => 8500000,
        ]);

        Photo::factory()->mainPhoto()->for($car)->create([
            'width' => 1920,
            'height' => 1080,
            'url' => 'https://res.cloudinary.com/garage/corolla-main.jpg',
        ]);

        // Draft that must not be returned
        Car::factory()->draft()->for($brand)->create(['model' => 'SecretDraft']);

        $response = $this->getJson('/api/v1/cars');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'slug',
                        'brand' => ['id', 'slug', 'name', 'logo_url', 'position'],
                        'model',
                        'year',
                        'mileage_km',
                        'price_xaf',
                        'fuel' => ['value', 'label'],
                        'transmission' => ['value', 'label'],
                        'condition' => ['value', 'label'],
                        'status' => ['value', 'label'],
                        'is_featured',
                        'main_photo' => [
                            'id',
                            'kind' => ['value', 'label'],
                            'role' => ['value', 'label'],
                            'url',
                            'width',
                            'height',
                            'position',
                            'alt',
                        ],
                        'published_at',
                        'sold_at',
                    ],
                ],
                'meta' => ['current_page', 'from', 'last_page', 'per_page', 'to', 'total'],
                'links' => ['first', 'last', 'prev', 'next'],
            ]);

        $this->assertCount(1, $response->json('data'));
        $this->assertSame('toyota-corolla-2020-1', $response->json('data.0.slug'));
        $this->assertSame(1920, $response->json('data.0.main_photo.width'));
        $this->assertSame(1080, $response->json('data.0.main_photo.height'));
    }

    public function test_public_car_detail_returns_rich_data_and_whatsapp_url(): void
    {
        Setting::set('whatsapp_number', '+237699001122');

        $brand = Brand::factory()->create(['slug' => 'mercedes-benz', 'name' => 'Mercedes-Benz']);

        $car = Car::factory()->available()->for($brand)->create([
            'slug' => 'mercedes-benz-c200-2019-2',
            'model' => 'Classe C 200',
            'year' => 2019,
            'color' => 'Noir Obsidienne',
            'description' => 'Berline premium en parfait état.',
        ]);

        Photo::factory()->mainPhoto()->for($car)->create();
        Photo::factory()->galleryPhoto(1)->for($car)->create();
        Video::factory()->interiorVideo()->for($car)->create();

        $response = $this->getJson('/api/v1/cars/mercedes-benz-c200-2019-2');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'slug',
                    'brand' => ['id', 'slug', 'name'],
                    'model',
                    'year',
                    'mileage_km',
                    'price_xaf',
                    'fuel',
                    'transmission',
                    'color',
                    'condition',
                    'description',
                    'status',
                    'is_featured',
                    'main_photo',
                    'photos' => [
                        '*' => ['id', 'url', 'width', 'height', 'position'],
                    ],
                    'videos' => [
                        '*' => ['id', 'url', 'role', 'duration_s'],
                    ],
                    'published_at',
                    'sold_at',
                    'whatsapp_url',
                ],
            ]);

        $this->assertSame('Mercedes-Benz', $response->json('data.brand.name'));
        $this->assertCount(2, $response->json('data.photos'));
        $this->assertCount(1, $response->json('data.videos'));

        $whatsappUrl = (string) $response->json('data.whatsapp_url');
        $this->assertStringStartsWith('https://wa.me/237699001122?text=', $whatsappUrl);
        $this->assertStringContainsString('mercedes-benz-c200-2019-2', urldecode($whatsappUrl));
    }

    public function test_public_car_detail_returns_404_for_draft_car(): void
    {
        $brand = Brand::factory()->create();

        Car::factory()->draft()->for($brand)->create([
            'slug' => 'secret-draft-car',
        ]);

        $response = $this->getJson('/api/v1/cars/secret-draft-car');

        $response->assertStatus(404)
            ->assertJson([
                'error' => [
                    'code' => 'NOT_FOUND',
                ],
            ]);
    }

    public function test_public_car_detail_returns_404_for_non_existent_car(): void
    {
        $response = $this->getJson('/api/v1/cars/non-existent-slug');

        $response->assertStatus(404)
            ->assertJson([
                'error' => [
                    'code' => 'NOT_FOUND',
                ],
            ]);
    }

    public function test_public_car_detail_returns_sold_car(): void
    {
        $brand = Brand::factory()->create();

        $car = Car::factory()->sold()->for($brand)->create([
            'slug' => 'sold-toyota-rav4',
        ]);

        Photo::factory()->mainPhoto()->for($car)->create();

        $response = $this->getJson('/api/v1/cars/sold-toyota-rav4');

        $response->assertOk()
            ->assertJsonPath('data.status.value', 'sold');

        $this->assertNotNull($response->json('data.sold_at'));
    }
}
