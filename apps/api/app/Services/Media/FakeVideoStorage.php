<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Data\Media\ObjectMeta;
use App\Data\Media\SignedUpload;
use App\Data\Media\UploadConstraints;
use App\Support\Contracts\VideoStorage;
use Illuminate\Support\Str;

final class FakeVideoStorage implements VideoStorage
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
        private readonly string $publicBaseUrl = 'https://res.cloudinary.com/fake/video/upload',
    ) {}

    public function signedUploadParams(string $key, UploadConstraints $constraints): SignedUpload
    {
        $trimmedKey = ltrim($key, '/');
        $expiresAt = now()->addSeconds($constraints->ttlSeconds);

        return new SignedUpload(
            uploadUrl: 'https://api.cloudinary.com/v1_1/fake/video/upload',
            fields: [
                'api_key' => 'fake-key',
                'timestamp' => (string) now()->timestamp,
                'public_id' => $trimmedKey,
                'signature' => Str::random(40),
            ],
            storageKey: $trimmedKey,
            expiresAt: $expiresAt->toIso8601String(),
        );
    }

    public function publicUrl(string $key): string
    {
        $trimmedKey = ltrim($key, '/');

        return "{$this->publicBaseUrl}/{$trimmedKey}";
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

    public function fakeObject(
        string $key,
        int $sizeBytes = 1024 * 1024 * 20,
        string $mimeType = 'video/mp4',
    ): ObjectMeta {
        $trimmedKey = ltrim($key, '/');
        $meta = new ObjectMeta(
            key: $trimmedKey,
            sizeBytes: $sizeBytes,
            mimeType: $mimeType,
            url: $this->publicUrl($trimmedKey),
        );

        $this->objects[$trimmedKey] = $meta;

        return $meta;
    }

    public function hasDeleted(string $key): bool
    {
        return in_array(ltrim($key, '/'), $this->deletedKeys, true);
    }
}
