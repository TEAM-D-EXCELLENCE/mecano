<?php

declare(strict_types=1);

namespace App\Data\Media;

final readonly class UploadConstraints
{
    /**
     * @param  list<string>  $allowedMimes
     */
    public function __construct(
        public int $maxSizeBytes,
        public array $allowedMimes,
        public int $ttlSeconds = 600,
    ) {}

    public static function forPhotos(): self
    {
        return new self(
            maxSizeBytes: (int) config('media.photos.max_size_bytes', 15 * 1024 * 1024),
            allowedMimes: (array) config('media.photos.allowed_mimes', ['image/jpeg', 'image/png', 'image/webp', 'image/heic']),
            ttlSeconds: (int) config('media.photos.signature_ttl_seconds', 600),
        );
    }
}
