<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
final class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'excerpt' => fake()->sentence(10),
            'description' => fake()->paragraphs(2, true),
            'icon' => fake()->randomElement(['wrench', 'car', 'gauge', 'zap', 'shield-check', 'truck']),
            'price_from_xaf' => fake()->randomElement([15000, 25000, 50000, 80000, null]),
            'is_active' => true,
            'position' => fake()->numberBetween(1, 20),
        ];
    }

    public function active(): self
    {
        return $this->state(fn () => ['is_active' => true]);
    }

    public function inactive(): self
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
