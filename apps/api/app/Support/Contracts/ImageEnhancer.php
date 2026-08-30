<?php

declare(strict_types=1);

namespace App\Support\Contracts;

use App\Data\Media\EnhancedImageResult;
use App\Models\Media;

interface ImageEnhancer
{
    /**
     * Auto improve image (contrast, brightness, sharpness).
     */
    public function autoImprove(Media $media): EnhancedImageResult;

    /**
     * Smart crop centered on vehicle subject.
     */
    public function smartCrop(Media $media, int $width = 1280, int $height = 960): EnhancedImageResult;

    /**
     * Remove background and replace with clean neutral studio background.
     */
    public function removeBackground(Media $media): EnhancedImageResult;
}
