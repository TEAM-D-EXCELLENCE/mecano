<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Data\Media\ObjectMeta;
use App\Data\Media\SignedUpload;
use App\Data\Media\UploadConstraints;
use App\Support\Contracts\VideoStorage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Vidéos sur Cloudinary, comme les photos.
 *
 * Remplace `R2VideoStorage`, qui s'appuyait sur un disque `r2` absent de
 * `config/filesystems.php` : chaque appel tombait dans son `catch` et rendait
 * une URL sans signature, qu'aucun hébergeur n'aurait acceptée. L'échec était
 * silencieux, et aucun test ne le voyait puisque l'environnement de test lie
 * l'implémentation factice.
 *
 * Le mécanisme est identique à celui des photos : signature restrictive,
 * envoi direct depuis le navigateur, confirmation ensuite.
 */
final readonly class CloudinaryVideoStorage implements VideoStorage
{
    public function __construct(
        private string $cloudName,
        private string $apiKey,
        private string $apiSecret,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            cloudName: (string) config('media.cloudinary.cloud_name', ''),
            apiKey: (string) config('media.cloudinary.api_key', ''),
            apiSecret: (string) config('media.cloudinary.api_secret', ''),
        );
    }

    public function presignedPutUrl(string $key, UploadConstraints $constraints): SignedUpload
    {
        $folder = trim($key, '/') !== '' ? trim($key, '/') : (string) config('media.photos.upload_folder');
        $timestamp = now()->timestamp;
        $publicId = $folder.'/'.Str::uuid();

        $params = [
            'folder' => $folder,
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];
        ksort($params);

        $toSign = collect($params)
            ->map(static fn (int|string $value, string $name): string => "{$name}={$value}")
            ->implode('&');

        return new SignedUpload(
            uploadUrl: "https://api.cloudinary.com/v1_1/{$this->cloudName}/video/upload",
            fields: [
                'api_key' => $this->apiKey,
                'timestamp' => $timestamp,
                'folder' => $folder,
                'public_id' => $publicId,
                'signature' => sha1($toSign.$this->apiSecret),
            ],
            storageKey: $publicId,
            expiresAt: now()->addSeconds($constraints->ttlSeconds)->toIso8601String(),
        );
    }

    public function publicUrl(string $key): string
    {
        return "https://res.cloudinary.com/{$this->cloudName}/video/upload/{$key}";
    }

    public function exists(string $key): ?ObjectMeta
    {
        $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
            ->timeout(10)
            ->get("https://api.cloudinary.com/v1_1/{$this->cloudName}/resources/video/upload/{$key}");

        if (! $response->successful()) {
            return null;
        }

        return new ObjectMeta(
            key: $key,
            sizeBytes: (int) $response->json('bytes', 0),
            mimeType: 'video/'.((string) $response->json('format', 'mp4')),
            width: $response->json('width') !== null ? (int) $response->json('width') : null,
            height: $response->json('height') !== null ? (int) $response->json('height') : null,
            url: (string) $response->json('secure_url', $this->publicUrl($key)),
        );
    }

    public function delete(string $key): void
    {
        $timestamp = now()->timestamp;
        $signature = sha1("public_id={$key}&timestamp={$timestamp}".$this->apiSecret);

        Http::timeout(10)->asForm()->post(
            "https://api.cloudinary.com/v1_1/{$this->cloudName}/video/destroy",
            [
                'public_id' => $key,
                'timestamp' => $timestamp,
                'api_key' => $this->apiKey,
                'signature' => $signature,
            ]
        );
    }
}
