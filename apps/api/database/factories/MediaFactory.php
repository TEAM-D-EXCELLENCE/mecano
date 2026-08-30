<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MediaKind;
use App\Enums\MediaProvider;
use App\Enums\MediaRole;
use App\Models\Car;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Media>
 */
final class MediaFactory extends Factory
{
    protected $model = Media::class;

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
     * Indicate that the media is the main photo.
     */
    public function mainPhoto(): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => MediaKind::Photo,
            'role' => MediaRole::Main,
            'provider' => MediaProvider::Cloudinary,
            'position' => 0,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the media is a gallery photo.
     */
    public function galleryPhoto(int $position = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => MediaKind::Photo,
            'role' => MediaRole::Gallery,
            'provider' => MediaProvider::Cloudinary,
            'position' => $position,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the media is an interior video.
     */
    public function interiorVideo(): static
    {
        $uuid = Str::uuid()->toString();
        $url = "https://media.garage.com/videos/{$uuid}.mp4";

        return $this->state(fn (array $attributes) => [
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
        ]);
    }

    /**
     * Indicate that the media is an exterior video.
     */
    public function exteriorVideo(): static
    {
        $uuid = Str::uuid()->toString();
        $url = "https://media.garage.com/videos/{$uuid}.mp4";

        return $this->state(fn (array $attributes) => [
            'kind' => MediaKind::Video,
            'role' => MediaRole::VideoExterior,
            'provider' => MediaProvider::R2,
            'storage_key' => "videos/{$uuid}.mp4",
            'url' => $url,
            'published_url' => $url,
            'mime' => 'video/mp4',
            'bytes' => fake()->numberBetween(20000000, 60000000),
            'width' => null,
            'height' => null,
            'duration_s' => fake()->numberBetween(45, 120),
            'position' => 11,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the media is unconfirmed (orphan).
     */
    public function unconfirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'confirmed_at' => null,
        ]);
    }
}
