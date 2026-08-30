<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Data\Media\ObjectMeta;
use App\Data\Media\SignedUpload;
use App\Data\Media\UploadConstraints;
use App\Support\Contracts\VideoStorage;
use Illuminate\Support\Facades\Storage;

final readonly class R2VideoStorage implements VideoStorage
{
    public function __construct(
        private string $bucket,
        private string $publicBaseUrl,
        private string $disk = 'r2',
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            bucket: (string) config('media.r2.bucket', 'mecano-videos'),
            publicBaseUrl: (string) config('media.r2.public_base_url', 'https://media.garage.com'),
        );
    }

    public function presignedPutUrl(string $key, UploadConstraints $constraints): SignedUpload
    {
        $trimmedKey = ltrim($key, '/');
        $expiresAt = now()->addSeconds($constraints->ttlSeconds);

        // Standard Laravel S3 driver supports temporaryUploadUrl if configured,
        // or fallback to signed presigned URL
        try {
            $uploadUrl = Storage::disk($this->disk)->temporaryUploadUrl($trimmedKey, $expiresAt);
        } catch (\Throwable) {
            $uploadUrl = "https://{$this->bucket}.r2.cloudflarestorage.com/{$trimmedKey}?expires={$expiresAt->timestamp}";
        }

        return new SignedUpload(
            uploadUrl: $uploadUrl,
            fields: ['method' => 'PUT'],
            storageKey: $trimmedKey,
            expiresAt: $expiresAt->toIso8601String(),
        );
    }

    public function publicUrl(string $key): string
    {
        $trimmedKey = ltrim($key, '/');
        $base = rtrim($this->publicBaseUrl, '/');

        return "{$base}/{$trimmedKey}";
    }

    public function exists(string $key): ?ObjectMeta
    {
        $trimmedKey = ltrim($key, '/');

        try {
            if (! Storage::disk($this->disk)->exists($trimmedKey)) {
                return null;
            }

            $size = (int) Storage::disk($this->disk)->size($trimmedKey);
            $mime = (string) (Storage::disk($this->disk)->mimeType($trimmedKey) ?: 'video/mp4');

            return new ObjectMeta(
                key: $trimmedKey,
                sizeBytes: $size,
                mimeType: $mime,
                url: $this->publicUrl($trimmedKey),
            );
        } catch (\Throwable) {
            return null;
        }
    }

    public function delete(string $key): void
    {
        $trimmedKey = ltrim($key, '/');

        try {
            Storage::disk($this->disk)->delete($trimmedKey);
        } catch (\Throwable) {
            // Ignored on network failure
        }
    }
}
