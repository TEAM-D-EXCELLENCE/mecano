<?php

declare(strict_types=1);

namespace App\Jobs\Media;

use App\Enums\ImageTransformPreset;
use App\Models\Media;
use App\Support\Contracts\ImageStorage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class GenerateDerivatives implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 60;

    public function __construct(
        public int $mediaId,
    ) {}

    public function handle(ImageStorage $imageStorage): void
    {
        /** @var Media|null $media */
        $media = Media::query()->find($this->mediaId);

        if ($media === null || $media->kind->value !== 'photo') {
            return;
        }

        // Precompute or ensure all standard derivative URLs can be generated
        foreach (ImageTransformPreset::cases() as $preset) {
            $imageStorage->derivativeUrl($media->storage_key, $preset);
        }
    }
}
