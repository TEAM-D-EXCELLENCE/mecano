<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Data\Media\EnhancedImageResult;
use App\Enums\MediaProvider;
use App\Models\Media;
use App\Support\Contracts\ImageEnhancer;
use RuntimeException;

final class FakeImageEnhancer implements ImageEnhancer
{
    private bool $shouldFail = false;

    public function setShouldFail(bool $fail): void
    {
        $this->shouldFail = $fail;
    }

    public function autoImprove(Media $media): EnhancedImageResult
    {
        if ($this->shouldFail) {
            throw new RuntimeException('Cloudinary service unavailable');
        }

        return new EnhancedImageResult(
            resultKey: "fake_enhanced/{$media->storage_key}_improved.jpg",
            resultUrl: "https://fake-cdn.test/enhanced/{$media->storage_key}_improved.jpg",
            provider: MediaProvider::Cloudinary,
            costUnits: 0,
            params: ['effect' => 'improve'],
        );
    }

    public function smartCrop(Media $media, int $width = 1280, int $height = 960): EnhancedImageResult
    {
        if ($this->shouldFail) {
            throw new RuntimeException('Cloudinary service unavailable');
        }

        return new EnhancedImageResult(
            resultKey: "fake_enhanced/{$media->storage_key}_cropped.jpg",
            resultUrl: "https://fake-cdn.test/enhanced/{$media->storage_key}_cropped.jpg",
            provider: MediaProvider::Cloudinary,
            costUnits: 0,
            params: ['crop' => 'smart', 'width' => $width, 'height' => $height],
        );
    }

    public function removeBackground(Media $media): EnhancedImageResult
    {
        if ($this->shouldFail) {
            throw new RuntimeException('remove.bg API rate limit or service failure');
        }

        return new EnhancedImageResult(
            resultKey: "fake_enhanced/{$media->storage_key}_nobg.png",
            resultUrl: "https://fake-cdn.test/enhanced/{$media->storage_key}_nobg.png",
            provider: MediaProvider::RemoveBg,
            costUnits: 1,
            params: ['provider' => 'removebg'],
        );
    }
}
