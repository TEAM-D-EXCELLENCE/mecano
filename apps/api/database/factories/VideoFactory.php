<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MediaKind;
use App\Enums\MediaProvider;
use App\Enums\MediaRole;
use App\Models\Car;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Video>
 */
final class VideoFactory extends Factory
{
    protected $model = Video::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uuid = Str::uuid()->toString();
        $url = "https://media.garage.com/videos/{$uuid}.mp4";

        return [
            'car_id' => Car::factory(),
            'kind' => MediaKind::Video,
            'role' => MediaRole::VideoInterior,
            'provider' => MediaProvider::R2,
            'storage_key' => "videos/{$uuid}.mp4",
            'url' => $url,
            'published_url' => $url,
            'mime' => 'video/mp4',
            'bytes' => fake()->numberBetween(15000000, 45000000),
            'width' => null,
            'height' => null,
            'duration_s' => fake()->numberBetween(30, 90),
            'position' => 10,
            'confirmed_at' => now(),
        ];
    }

    /**
     * Indicate that the video is an interior video.
     */
    public function interiorVideo(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MediaRole::VideoInterior,
            'position' => 10,
        ]);
    }

    /**
     * Indicate that the video is an exterior video.
     */
    public function exteriorVideo(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MediaRole::VideoExterior,
            'position' => 11,
        ]);
    }
}
