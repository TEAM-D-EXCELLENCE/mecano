<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\IntegrationQuota;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IntegrationQuota>
 */
final class IntegrationQuotaFactory extends Factory
{
    protected $model = IntegrationQuota::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => 'removebg',
            'period' => now()->format('Y-m'),
            'used' => fake()->numberBetween(0, 20),
            'limit' => 50,
            'updated_at' => now(),
        ];
    }
}
