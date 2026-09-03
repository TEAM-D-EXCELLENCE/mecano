<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Data\Media\EnhancedImageResult;
use App\Enums\MediaProvider;
use App\Models\Media;
use App\Support\Contracts\ImageEnhancer;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Suppression de fond par remove.bg (CDC §3.2, BE-34).
 *
 * Enveloppe l'améliorateur Cloudinary : l'amélioration automatique et le
 * recadrage restent de simples transformations d'URL, seule la suppression de
 * fond exige un aller-retour réseau. remove.bg renvoie l'image détourée en
 * binaire, sans l'héberger : elle est donc reversée chez Cloudinary, sinon il
 * n'y aurait aucune URL à publier.
 *
 * Toute panne remonte en exception : c'est ce qui déclenche le remboursement du
 * quota dans RequestMediaEnhancement. Un échec silencieux consommerait un
 * crédit sur les cinquante du mois sans rien produire.
 */
final readonly class RemoveBgImageEnhancer implements ImageEnhancer
{
    private const ENDPOINT = 'https://api.remove.bg/v1.0/removebg';

    public function __construct(
        private ImageEnhancer $inner,
        private string $apiKey,
        private string $cloudName,
        private string $cloudApiKey,
        private string $cloudApiSecret,
        private string $folder = 'mecano/enhanced',
        private int $timeoutSeconds = 60,
    ) {}

    public static function fromConfig(ImageEnhancer $inner): self
    {
        return new self(
            inner: $inner,
            apiKey: (string) config('media.removebg.api_key', ''),
            cloudName: (string) config('media.cloudinary.cloud_name', ''),
            cloudApiKey: (string) config('media.cloudinary.api_key', ''),
            cloudApiSecret: (string) config('media.cloudinary.api_secret', ''),
            folder: (string) config('media.removebg.folder', 'mecano/enhanced'),
            timeoutSeconds: (int) config('media.removebg.timeout_seconds', 60),
        );
    }

    public function autoImprove(Media $media): EnhancedImageResult
    {
        return $this->inner->autoImprove($media);
    }

    public function smartCrop(Media $media, int $width = 1280, int $height = 960): EnhancedImageResult
    {
        return $this->inner->smartCrop($media, $width, $height);
    }

    public function removeBackground(Media $media): EnhancedImageResult
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('Clé remove.bg absente : la suppression de fond est indisponible.');
        }

        $cutout = $this->requestCutout($this->sourceUrl($media));
        $uploaded = $this->storeOnCloudinary($cutout, $media);

        return new EnhancedImageResult(
            resultKey: $uploaded['public_id'],
            resultUrl: $uploaded['secure_url'],
            provider: MediaProvider::RemoveBg,
            costUnits: 1,
            params: ['size' => 'auto', 'format' => 'png', 'source' => 'remove.bg'],
        );
    }

    /**
     * URL publique de l'original, seule forme que remove.bg sait consommer sans
     * que l'API n'ait à retélécharger le fichier elle-même.
     */
    private function sourceUrl(Media $media): string
    {
        $url = $media->url;

        if (is_string($url) && $url !== '') {
            return $url;
        }

        return "https://res.cloudinary.com/{$this->cloudName}/image/upload/".ltrim((string) $media->storage_key, '/');
    }

    private function requestCutout(string $sourceUrl): string
    {
        $response = Http::withHeaders(['X-Api-Key' => $this->apiKey])
            ->timeout($this->timeoutSeconds)
            ->asMultipart()
            ->post(self::ENDPOINT, [
                ['name' => 'image_url', 'contents' => $sourceUrl],
                ['name' => 'size', 'contents' => 'auto'],
                ['name' => 'format', 'contents' => 'png'],
            ]);

        if ($response->failed()) {
            throw new RuntimeException("remove.bg a refusé la demande ({$response->status()}).");
        }

        $body = $response->body();

        if ($body === '') {
            throw new RuntimeException('remove.bg a renvoyé une image vide.');
        }

        return $body;
    }

    /**
     * @return array{public_id: string, secure_url: string}
     */
    private function storeOnCloudinary(string $contents, Media $media): array
    {
        $timestamp = now()->timestamp;
        $publicId = "{$this->folder}/{$media->id}-bg-removed-{$timestamp}";

        $signature = sha1("public_id={$publicId}&timestamp={$timestamp}".$this->cloudApiSecret);

        $response = Http::timeout($this->timeoutSeconds)
            ->attach('file', $contents, 'cutout.png')
            ->post("https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload", [
                'api_key' => $this->cloudApiKey,
                'timestamp' => $timestamp,
                'public_id' => $publicId,
                'signature' => $signature,
            ]);

        if ($response->failed()) {
            throw new RuntimeException("Cloudinary a refusé le dérivé détouré ({$response->status()}).");
        }

        $payload = $response->json();

        if (! is_array($payload) || ! isset($payload['secure_url'], $payload['public_id'])) {
            throw new RuntimeException('Réponse Cloudinary inexploitable après détourage.');
        }

        return [
            'public_id' => (string) $payload['public_id'],
            'secure_url' => (string) $payload['secure_url'],
        ];
    }
}
