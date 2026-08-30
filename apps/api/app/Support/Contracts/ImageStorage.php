<?php

declare(strict_types=1);

namespace App\Support\Contracts;

use App\Data\Media\ObjectMeta;
use App\Data\Media\SignedUpload;
use App\Data\Media\UploadConstraints;
use App\Enums\ImageTransformPreset;

interface ImageStorage
{
    /**
     * Generate restrictive signed upload parameters for direct client upload.
     */
    public function signedUploadParams(string $folder, UploadConstraints $constraints): SignedUpload;

    /**
     * Generate a CDN derivative URL for a stored image.
     */
    public function derivativeUrl(string $key, ImageTransformPreset|string $preset): string;

    /**
     * Verify if the object exists and return its metadata.
     */
    public function exists(string $key): ?ObjectMeta;

    /**
     * Delete an image from storage.
     */
    public function delete(string $key): void;
}
