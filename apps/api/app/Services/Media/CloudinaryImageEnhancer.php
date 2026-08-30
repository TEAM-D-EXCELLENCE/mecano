<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Data\Media\EnhancedImageResult;
use App\Enums\MediaProvider;
use App\Models\Media;
use App\Support\Contracts\ImageEnhancer;

final class CloudinaryImageEnhancer implements ImageEnhancer
{
    public function __construct(
        private readonly string $cloudName,
    ) {}

    public function autoImprove(Media $media): EnhancedImageResult
    {
        $transformedKey = "enhanced/{$media->storage_key}_improved";
        $url = "https://res.cloudinary.com/{$this->cloudName}/image/upload/e_improve,e_auto_contrast,e_sharpen/{$media->storage_key}";

        return new EnhancedImageResult(
            resultKey: $transformedKey,
            resultUrl: $url,
            provider: MediaProvider::Cloudinary,
            costUnits: 0,
            params: ['effect' => 'improve,auto_contrast,sharpen'],
        );
    }

    public function smartCrop(Media $media, int $width = 1280, int $height = 960): EnhancedImageResult
    {
        $transformedKey = "enhanced/{$media->storage_key}_cropped_{$width}x{$height}";
        $url = "https://res.cloudinary.com/{$this->cloudName}/image/upload/c_fill,g_auto,w_{$width},h_{$height}/{$media->storage_key}";

        return new EnhancedImageResult(
            resultKey: $transformedKey,
            resultUrl: $url,
            provider: MediaProvider::Cloudinary,
            costUnits: 0,
            params: ['crop' => 'fill', 'gravity' => 'auto', 'width' => $width, 'height' => $height],
        );
    }

    public function removeBackground(Media $media): EnhancedImageResult
    {
        $transformedKey = "enhanced/{$media->storage_key}_bg_removed";
        $url = "https://res.cloudinary.com/{$this->cloudName}/image/upload/e_background_removal/{$media->storage_key}";

        return new EnhancedImageResult(
            resultKey: $transformedKey,
            resultUrl: $url,
            provider: MediaProvider::RemoveBg,
            costUnits: 1,
            params: ['effect' => 'background_removal'],
        );
    }
}
