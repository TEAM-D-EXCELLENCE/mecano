<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Data\Media\ObjectMeta;
use App\Data\Media\SignedUpload;
use App\Data\Media\UploadConstraints;
use App\Enums\ImageTransformPreset;
use App\Support\Contracts\ImageStorage;
use Illuminate\Support\Str;

final class FakeImageStorage implements ImageStorage
{
    /**
     * @var array<string, ObjectMeta>
     */
    private array $objects = [];

    /**
     * @var list<string>
     */
    private array $deletedKeys = [];

    public function __construct(
        private readonly string $baseUrl = 'https://fake-cdn.example.com',
    ) {}

    public function signedUploadParams(string $folder, UploadConstraints $constraints): SignedUpload
    {
        $uniqueId = (string) Str::uuid();
        $storageKey = "{$folder}/{$uniqueId}";

        return new SignedUpload(
            uploadUrl: "{$this->baseUrl}/upload",
            fields: [
                'key' => $storageKey,
                'max_size' => $constraints->maxSizeBytes,
                'signature' => 'fake_signature_'.Str::random(16),
            ],
            storageKey: $storageKey,
            expiresAt: now()->addSeconds($constraints->ttlSeconds)->toIso8601String(),
        );
    }

    public function derivativeUrl(string $key, ImageTransformPreset|string $preset): string
    {
        $presetName = $preset instanceof ImageTransformPreset ? $preset->value : $preset;
        $trimmedKey = ltrim($key, '/');

        if ($presetName !== '') {
            return "{$this->baseUrl}/transformed/{$presetName}/{$trimmedKey}";
        }

        return "{$this->baseUrl}/images/{$trimmedKey}";
    }

    public function exists(string $key): ?ObjectMeta
    {
        $trimmedKey = ltrim($key, '/');

        return $this->objects[$trimmedKey] ?? null;
    }

    public function delete(string $key): void
    {
        $trimmedKey = ltrim($key, '/');
        unset($this->objects[$trimmedKey]);
        $this->deletedKeys[] = $trimmedKey;
    }

    // -------------------------------------------------------------------------
    // Testing helpers
    // -------------------------------------------------------------------------

    public function fakeObject(
        string $key,
        int $sizeBytes = 1024 * 500,
        string $mimeType = 'image/jpeg',
        int $width = 1920,
        int $height = 1080,
    ): ObjectMeta {
        $trimmedKey = ltrim($key, '/');
        $meta = new ObjectMeta(
            key: $trimmedKey,
            sizeBytes: $sizeBytes,
            mimeType: $mimeType,
            width: $width,
            height: $height,
            url: $this->derivativeUrl($trimmedKey, ''),
        );

        $this->objects[$trimmedKey] = $meta;

        return $meta;
    }

    /**
     * @return list<string>
     */
    public function getDeletedKeys(): array
    {
        return $this->deletedKeys;
    }

    public function hasDeleted(string $key): bool
    {
        return in_array(ltrim($key, '/'), $this->deletedKeys, true);
    }
}
