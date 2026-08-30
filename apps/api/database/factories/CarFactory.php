<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CarStatus;
use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Enums\VehicleCondition;
use App\Models\Brand;
use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Car>
 */
final class CarFactory extends Factory
{
    protected $model = Car::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->numberBetween(2010, 2024);
        $models = ['Corolla', 'RAV4', 'Hilux', 'Land Cruiser', 'Yaris', 'Avensis', 'Prado', 'Civic', 'CR-V', '208', '3008', 'Duster', 'Tucson', 'Sportage'];
        $model = fake()->randomElement($models);
        $uniqueSuffix = fake()->unique()->numberBetween(100, 99999);
        $slug = Str::slug("toyota-{$model}-{$year}-{$uniqueSuffix}");

        return [
            'slug' => $slug,
            'brand_id' => Brand::factory(),
            'model' => $model,
            'year' => $year,
            'mileage_km' => fake()->numberBetween(15000, 180000),
            'price_xaf' => fake()->numberBetween(30, 250) * 100000, // Multiples of 100,000 FCFA
            'fuel' => fake()->randomElement(FuelType::cases()),
            'transmission' => fake()->randomElement(TransmissionType::cases()),
            'color' => fake()->randomElement(['Noir', 'Blanc', 'Gris métallisé', 'Bleu nuit', 'Rouge', 'Argent']),
            'condition' => fake()->randomElement(VehicleCondition::cases()),
            'description' => fake()->optional(0.8)->paragraph(3),
            'status' => CarStatus::Available,
            'is_featured' => fake()->boolean(20),
            'published_at' => now()->subDays(fake()->numberBetween(1, 60)),
            'sold_at' => null,
            'views_count' => fake()->numberBetween(0, 500),
            'whatsapp_clicks_count' => fake()->numberBetween(0, 50),
        ];
    }

    /**
     * Indicate that the car is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CarStatus::Draft,
            'published_at' => null,
            'sold_at' => null,
            'is_featured' => false,
        ]);
    }

    /**
     * Indicate that the car is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CarStatus::Available,
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'sold_at' => null,
        ]);
    }

    /**
     * Indicate that the car is reserved.
     */
    public function reserved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CarStatus::Reserved,
            'published_at' => now()->subDays(fake()->numberBetween(5, 30)),
            'sold_at' => null,
        ]);
    }

    /**
     * Indicate that the car is sold.
     */
    public function sold(): static
    {
        $publishedAt = now()->subDays(fake()->numberBetween(30, 120));

        return $this->state(fn (array $attributes) => [
            'status' => CarStatus::Sold,
            'published_at' => $publishedAt,
            'sold_at' => (clone $publishedAt)->addDays(fake()->numberBetween(5, 25)),
            'is_featured' => false,
        ]);
    }

    /**
     * Indicate that the car is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'status' => CarStatus::Available,
            'published_at' => now()->subDays(fake()->numberBetween(1, 10)),
        ]);
    }
}
