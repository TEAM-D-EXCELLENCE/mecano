<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class BrandAndCarMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_brands_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('brands'));

        $this->assertTrue(Schema::hasColumns('brands', [
            'id',
            'slug',
            'name',
            'logo_url',
            'position',
            'is_active',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_cars_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('cars'));

        $this->assertTrue(Schema::hasColumns('cars', [
            'id',
            'slug',
            'brand_id',
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
            'published_at',
            'sold_at',
            'views_count',
            'whatsapp_clicks_count',
            'created_at',
            'updated_at',
            'deleted_at',
        ]));
    }

    public function test_brand_and_car_insertion_and_foreign_key_relation(): void
    {
        $brandId = DB::table('brands')->insertGetId([
            'slug' => 'toyota',
            'name' => 'Toyota',
            'logo_url' => null,
            'position' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $carId = DB::table('cars')->insertGetId([
            'slug' => 'toyota-corolla-2018-1',
            'brand_id' => $brandId,
            'model' => 'Corolla',
            'year' => 2018,
            'mileage_km' => 85000,
            'price_xaf' => 4500000,
            'fuel' => 'essence',
            'transmission' => 'automatique',
            'color' => 'Gris métallisé',
            'condition' => 'bon',
            'description' => 'Véhicule en parfait état.',
            'status' => 'available',
            'is_featured' => true,
            'published_at' => now(),
            'sold_at' => null,
            'views_count' => 0,
            'whatsapp_clicks_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('brands', ['id' => $brandId, 'slug' => 'toyota']);
        $this->assertDatabaseHas('cars', ['id' => $carId, 'slug' => 'toyota-corolla-2018-1', 'brand_id' => $brandId]);
    }
}
