<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EnhancementStatus;
use App\Enums\EnhancementType;
use App\Enums\MediaProvider;
use App\Models\Media;
use App\Models\MediaEnhancement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaEnhancement>
 */
final class MediaEnhancementFactory extends Factory
{
    protected $model = MediaEnhancement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'media_id' => Media::factory(),
            'type' => EnhancementType::AutoImprove,
            'provider' => MediaProvider::Cloudinary,
            'status' => EnhancementStatus::Pending,
            'params' => null,
            'result_key' => null,
            'result_url' => null,
            'error' => null,
            'cost_units' => 0,
            'approved_at' => null,
        ];
    }

    public function autoImprove(): self
    {
        return $this->state(fn () => [
            'type' => EnhancementType::AutoImprove,
            'provider' => MediaProvider::Cloudinary,
            'cost_units' => 0,
        ]);
    }

    public function backgroundRemoval(): self
    {
        return $this->state(fn () => [
            'type' => EnhancementType::BackgroundRemoval,
            'provider' => MediaProvider::RemoveBg,
            'cost_units' => 1,
        ]);
    }

    public function ready(): self
    {
        return $this->state(fn () => [
            'status' => EnhancementStatus::Ready,
            'result_key' => 'enhanced/result_123.jpg',
            'result_url' => 'https://res.cloudinary.com/garage/image/upload/v1724000000/enhanced/result_123.jpg',
        ]);
    }

    public function approved(): self
    {
        return $this->state(fn () => [
            'status' => EnhancementStatus::Approved,
            'result_key' => 'enhanced/result_123.jpg',
            'result_url' => 'https://res.cloudinary.com/garage/image/upload/v1724000000/enhanced/result_123.jpg',
            'approved_at' => now(),
        ]);
    }

    public function failed(): self
    {
        return $this->state(fn () => [
            'status' => EnhancementStatus::Failed,
            'error' => 'API rate limit or invalid image format',
        ]);
    }
}
