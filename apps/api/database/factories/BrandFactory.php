<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Brand>
 */
final class BrandFactory extends Factory
{
    protected $model = Brand::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Toyota', 'Mercedes-Benz', 'Peugeot', 'Renault', 'Hyundai',
            'Kia', 'Nissan', 'Volkswagen', 'BMW', 'Audi',
            'Ford', 'Honda', 'Mitsubishi', 'Suzuki', 'Mazda',
        ]);

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'logo_url' => null,
            'position' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the brand is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
