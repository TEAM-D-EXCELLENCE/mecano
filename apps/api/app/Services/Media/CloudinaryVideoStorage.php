<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Data\Media\ObjectMeta;
use App\Data\Media\SignedUpload;
use App\Data\Media\UploadConstraints;
use App\Support\Contracts\VideoStorage;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Vidéos hébergées chez Cloudinary.
 *
 * Même mécanique que les photos — signature restrictive, envoi direct du
 * navigateur, confirmation ensuite — mais sur le point d'entrée `video` de
 * Cloudinary, qui transcode et sert le fichier.
 *
 * Le fichier ne traverse jamais l'API : elle ne délivre qu'une signature à
 * durée limitée (ADR 0007).
 */
final readonly class CloudinaryVideoStorage implements VideoStorage
{
    public function __construct(
        private string $cloudName,
        private string $apiKey,
        private string $apiSecret,
        private bool $secure = true,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            cloudName: (string) config('media.cloudinary.cloud_name', ''),
            apiKey: (string) config('media.cloudinary.api_key', ''),
            apiSecret: (string) config('media.cloudinary.api_secret', ''),
            secure: (bool) config('media.cloudinary.secure', true),
        );
    }

    public function signedUploadParams(string $key, UploadConstraints $constraints): SignedUpload
    {
        $publicId = ltrim($key, '/');
        $timestamp = now()->timestamp;

        $signature = $this->sign([
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ]);

        return new SignedUpload(
            uploadUrl: "https://api.cloudinary.com/v1_1/{$this->cloudName}/video/upload",
            fields: [
                'api_key' => $this->apiKey,
                'timestamp' => $timestamp,
                'public_id' => $publicId,
                'signature' => $signature,
            ],
            storageKey: $publicId,
            expiresAt: now()->addSeconds($constraints->ttlSeconds)->toIso8601String(),
        );
    }

    public function publicUrl(string $key): string
    {
        $scheme = $this->secure ? 'https' : 'http';
        $trimmedKey = ltrim($key, '/');

        // `f_auto,q_auto` laisse Cloudinary choisir le conteneur et le débit
        // selon le lecteur : sur une connexion mobile, servir le fichier
        // d'origine coûterait plusieurs dizaines de mégaoctets inutiles.
        return "{$scheme}://res.cloudinary.com/{$this->cloudName}/video/upload/f_auto,q_auto/{$trimmedKey}";
    }

    public function exists(string $key): ?ObjectMeta
    {
        $trimmedKey = ltrim($key, '/');
        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/resources/video/upload/{$trimmedKey}";

        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->timeout(5)
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            $format = (string) ($data['format'] ?? 'mp4');

            return new ObjectMeta(
                key: $trimmedKey,
                sizeBytes: (int) ($data['bytes'] ?? 0),
                mimeType: $format === 'mov' ? 'video/quicktime' : 'video/mp4',
                width: isset($data['width']) ? (int) $data['width'] : null,
                height: isset($data['height']) ? (int) $data['height'] : null,
                url: (string) ($data['secure_url'] ?? $this->publicUrl($trimmedKey)),
            );
        } catch (Throwable) {
            return null;
        }
    }

    public function delete(string $key): void
    {
        $trimmedKey = ltrim($key, '/');
        $timestamp = now()->timestamp;

        $signature = $this->sign([
            'public_id' => $trimmedKey,
            'timestamp' => $timestamp,
        ]);

        try {
            Http::timeout(5)->post("https://api.cloudinary.com/v1_1/{$this->cloudName}/video/destroy", [
                'public_id' => $trimmedKey,
                'api_key' => $this->apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ]);
        } catch (Throwable) {
            // La suppression distante est réessayée par la purge horaire des
            // orphelins : échouer ici bloquerait la suppression en base.
        }
    }

    /**
     * @param  array<string, scalar>  $params
     */
    private function sign(array $params): string
    {
        ksort($params);

        $toSign = collect($params)
            ->map(static fn ($value, $name) => "{$name}={$value}")
            ->implode('&');

        return sha1($toSign.$this->apiSecret);
    }
}
