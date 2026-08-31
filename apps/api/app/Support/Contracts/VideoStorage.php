<?php

declare(strict_types=1);

namespace App\Support\Contracts;

use App\Data\Media\ObjectMeta;
use App\Data\Media\SignedUpload;
use App\Data\Media\UploadConstraints;

interface VideoStorage
{
    /**
     * Paramètres signés pour un envoi direct depuis le navigateur.
     */
    public function presignedPutUrl(string $key, UploadConstraints $constraints): SignedUpload;

    /**
     * Generate a public CDN delivery URL for a video.
     */
    public function publicUrl(string $key): string;

    /**
     * Check if a video exists in storage.
     */
    public function exists(string $key): ?ObjectMeta;

    /**
     * Delete a video from storage.
     */
    public function delete(string $key): void;
}
