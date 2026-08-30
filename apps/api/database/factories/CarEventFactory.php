<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CarEventType;
use App\Models\Car;
use App\Models\CarEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarEvent>
 */
final class CarEventFactory extends Factory
{
    protected $model = CarEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'car_id' => Car::factory(),
            'type' => fake()->randomElement(CarEventType::cases()),
            'ip_hash' => hash('sha256', fake()->ipv4().'_salt_demo'),
            'referer' => fake()->randomElement(['google.com', 'facebook.com', 'whatsapp.com', null]),
            'created_at' => now()->subDays(fake()->numberBetween(0, 30)),
        ];
    }

    /**
     * Indicate that the event is a view.
     */
    public function view(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CarEventType::View,
        ]);
    }

    /**
     * Indicate that the event is a WhatsApp click.
     */
    public function whatsappClick(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CarEventType::WhatsappClick,
        ]);
    }
}
