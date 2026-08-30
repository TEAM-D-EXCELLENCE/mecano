<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

final class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['slug' => 'toyota', 'name' => 'Toyota', 'position' => 1, 'is_active' => true],
            ['slug' => 'mercedes-benz', 'name' => 'Mercedes-Benz', 'position' => 2, 'is_active' => true],
            ['slug' => 'peugeot', 'name' => 'Peugeot', 'position' => 3, 'is_active' => true],
            ['slug' => 'hyundai', 'name' => 'Hyundai', 'position' => 4, 'is_active' => true],
            ['slug' => 'kia', 'name' => 'Kia', 'position' => 5, 'is_active' => true],
            ['slug' => 'nissan', 'name' => 'Nissan', 'position' => 6, 'is_active' => true],
            ['slug' => 'renault', 'name' => 'Renault', 'position' => 7, 'is_active' => true],
            ['slug' => 'volkswagen', 'name' => 'Volkswagen', 'position' => 8, 'is_active' => true],
            ['slug' => 'bmw', 'name' => 'BMW', 'position' => 9, 'is_active' => true],
            ['slug' => 'ford', 'name' => 'Ford', 'position' => 10, 'is_active' => true],
            ['slug' => 'mitsubishi', 'name' => 'Mitsubishi', 'position' => 11, 'is_active' => true],
            ['slug' => 'land-rover', 'name' => 'Land Rover', 'position' => 12, 'is_active' => true],
            ['slug' => 'audi', 'name' => 'Audi', 'position' => 13, 'is_active' => true],
            ['slug' => 'honda', 'name' => 'Honda', 'position' => 14, 'is_active' => true],
            ['slug' => 'suzuki', 'name' => 'Suzuki', 'position' => 15, 'is_active' => true],
            // Inactive brand test case (D11: brand deactivated without breaking cars)
            ['slug' => 'chevrolet', 'name' => 'Chevrolet', 'position' => 99, 'is_active' => false],
        ];

        foreach ($brands as $brand) {
            Brand::query()->updateOrCreate(
                ['slug' => $brand['slug']],
                [
                    'name' => $brand['name'],
                    'position' => $brand['position'],
                    'is_active' => $brand['is_active'],
                ]
            );
        }
    }
}
