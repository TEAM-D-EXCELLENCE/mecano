<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class MediaCarEventsSettingsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tables_have_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('media'));
        $this->assertTrue(Schema::hasColumns('media', [
            'id',
            'car_id',
            'kind',
            'role',
            'provider',
            'storage_key',
            'url',
            'published_url',
            'mime',
            'bytes',
            'width',
            'height',
            'duration_s',
            'position',
            'alt',
            'confirmed_at',
            'exclusive_role',
            'created_at',
            'updated_at',
        ]));

        $this->assertTrue(Schema::hasTable('car_events'));
        $this->assertTrue(Schema::hasColumns('car_events', [
            'id',
            'car_id',
            'type',
            'ip_hash',
            'referer',
            'created_at',
        ]));

        $this->assertTrue(Schema::hasTable('settings'));
        $this->assertTrue(Schema::hasColumns('settings', [
            'key',
            'value',
            'updated_at',
        ]));
    }

    public function test_media_exclusive_role_uniqueness_prevents_duplicate_main_photo(): void
    {
        $brandId = DB::table('brands')->insertGetId([
            'slug' => 'toyota',
            'name' => 'Toyota',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $carId = DB::table('cars')->insertGetId([
            'slug' => 'toyota-yaris-2020-1',
            'brand_id' => $brandId,
            'model' => 'Yaris',
            'year' => 2020,
            'mileage_km' => 45000,
            'price_xaf' => 6000000,
            'fuel' => 'essence',
            'transmission' => 'automatique',
            'color' => 'Blanc',
            'condition' => 'excellent',
            'status' => 'available',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert first main photo
        DB::table('media')->insert([
            'car_id' => $carId,
            'kind' => 'photo',
            'role' => 'main',
            'provider' => 'cloudinary',
            'storage_key' => 'cars/yaris-main-1',
            'url' => 'https://res.cloudinary.com/garage/image/upload/v1/cars/yaris-main-1.jpg',
            'published_url' => 'https://res.cloudinary.com/garage/image/upload/v1/cars/yaris-main-1.jpg',
            'mime' => 'image/jpeg',
            'bytes' => 150000,
            'width' => 1920,
            'height' => 1080,
            'position' => 0,
            'confirmed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert gallery photos (multiple gallery photos are allowed)
        DB::table('media')->insert([
            'car_id' => $carId,
            'kind' => 'photo',
            'role' => 'gallery',
            'provider' => 'cloudinary',
            'storage_key' => 'cars/yaris-gallery-1',
            'url' => 'https://res.cloudinary.com/garage/image/upload/v1/cars/yaris-gallery-1.jpg',
            'mime' => 'image/jpeg',
            'bytes' => 120000,
            'position' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('media')->insert([
            'car_id' => $carId,
            'kind' => 'photo',
            'role' => 'gallery',
            'provider' => 'cloudinary',
            'storage_key' => 'cars/yaris-gallery-2',
            'url' => 'https://res.cloudinary.com/garage/image/upload/v1/cars/yaris-gallery-2.jpg',
            'mime' => 'image/jpeg',
            'bytes' => 130000,
            'position' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseCount('media', 3);

        // Attempting to insert a second 'main' photo for the SAME car must fail
        $this->expectException(QueryException::class);

        DB::table('media')->insert([
            'car_id' => $carId,
            'kind' => 'photo',
            'role' => 'main', // duplicate exclusive role!
            'provider' => 'cloudinary',
            'storage_key' => 'cars/yaris-main-duplicate',
            'url' => 'https://res.cloudinary.com/garage/image/upload/v1/cars/yaris-main-duplicate.jpg',
            'mime' => 'image/jpeg',
            'bytes' => 160000,
            'position' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_car_events_and_settings_insertion(): void
    {
        $brandId = DB::table('brands')->insertGetId([
            'slug' => 'peugeot',
            'name' => 'Peugeot',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $carId = DB::table('cars')->insertGetId([
            'slug' => 'peugeot-208-2019-1',
            'brand_id' => $brandId,
            'model' => '208',
            'year' => 2019,
            'mileage_km' => 60000,
            'price_xaf' => 5500000,
            'fuel' => 'essence',
            'transmission' => 'manuelle',
            'color' => 'Bleu',
            'condition' => 'bon',
            'status' => 'available',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('car_events')->insert([
            'car_id' => $carId,
            'type' => 'view',
            'ip_hash' => hash('sha256', '127.0.0.1_salt_secret'),
            'referer' => 'google.com',
            'created_at' => now(),
        ]);

        DB::table('settings')->insert([
            'key' => 'whatsapp_number',
            'value' => json_encode('+237600000000'),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('car_events', ['car_id' => $carId, 'type' => 'view']);
        $this->assertDatabaseHas('settings', ['key' => 'whatsapp_number']);
    }
}
