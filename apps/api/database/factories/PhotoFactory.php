<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MediaKind;
use App\Enums\MediaProvider;
use App\Enums\MediaRole;
use App\Models\Car;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Photo>
 */
final class PhotoFactory extends Factory
{
    protected $model = Photo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uuid = Str::uuid()->toString();
        $url = "https://res.cloudinary.com/garage/image/upload/v1724000000/cars/{$uuid}.jpg";

        return [
            'car_id' => Car::factory(),
            'kind' => MediaKind::Photo,
            'role' => MediaRole::Gallery,
            'provider' => MediaProvider::Cloudinary,
            'storage_key' => "cars/{$uuid}",
            'url' => $url,
            'published_url' => $url,
            'mime' => 'image/jpeg',
            'bytes' => fake()->numberBetween(150000, 850000),
            'width' => 1920,
            'height' => 1080,
            'duration_s' => null,
            'position' => fake()->numberBetween(0, 10),
            'alt' => fake()->optional(0.7)->sentence(4),
            'confirmed_at' => now(),
        ];
    }

    /**
     * Indicate that the photo is the main photo.
     */
    public function mainPhoto(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MediaRole::Main,
            'position' => 0,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the photo is a gallery photo.
     */
    public function galleryPhoto(int $position = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MediaRole::Gallery,
            'position' => $position,
            'confirmed_at' => now(),
        ]);
    }
}
