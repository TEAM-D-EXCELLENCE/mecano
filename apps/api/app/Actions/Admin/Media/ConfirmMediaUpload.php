<?php

declare(strict_types=1);

namespace App\Actions\Admin\Media;

use App\Data\Media\ConfirmMediaUploadData;
use App\Enums\MediaKind;
use App\Enums\MediaProvider;
use App\Enums\MediaRole;
use App\Exceptions\MediaUploadNotFoundException;
use App\Jobs\Media\GenerateDerivatives;
use App\Models\Car;
use App\Models\Media;
use App\Support\Contracts\FrontendRevalidator;
use App\Support\Contracts\ImageStorage;
use App\Support\Contracts\VideoStorage;
use Illuminate\Support\Facades\DB;

final readonly class ConfirmMediaUpload
{
    public function __construct(
        private ImageStorage $imageStorage,
        private VideoStorage $videoStorage,
        private FrontendRevalidator $revalidator,
    ) {}

    /**
     * Confirm that a media has been successfully uploaded to the storage provider
     * and persist it in the database with verified metadata.
     *
     * @throws MediaUploadNotFoundException
     */
    public function handle(int $carId, ConfirmMediaUploadData $data): Media
    {
        /** @var Car $car */
        $car = Car::query()->findOrFail($carId);

        $isPhoto = in_array($data->role, [MediaRole::Main, MediaRole::Gallery], true);
        $kind = $isPhoto ? MediaKind::Photo : MediaKind::Video;
        $provider = MediaProvider::Cloudinary;

        // Verify that the object actually exists in the external storage provider (CDC §4 Pipeline)
        $meta = $isPhoto
            ? $this->imageStorage->exists($data->storageKey)
            : $this->videoStorage->exists($data->storageKey);

        if ($meta === null) {
            throw new MediaUploadNotFoundException($data->storageKey);
        }

        $url = $isPhoto
            ? $this->imageStorage->derivativeUrl($data->storageKey, '')
            : $this->videoStorage->publicUrl($data->storageKey);

        $mime = $data->mime ?? $meta->mimeType;
        $bytes = $data->bytes ?? $meta->sizeBytes;
        $width = $data->width ?? $meta->width;
        $height = $data->height ?? $meta->height;

        /** @var Media $media */
        $media = DB::transaction(function () use ($car, $kind, $provider, $data, $url, $mime, $bytes, $width, $height): Media {
            // Handle exclusive role constraints:
            // 1. If this is a new main photo, demote any existing main photo to gallery photo
            if ($data->role === MediaRole::Main) {
                Media::query()
                    ->where('car_id', $car->id)
                    ->where('role', MediaRole::Main)
                    ->update(['role' => MediaRole::Gallery]);
            }

            // 2. If this is an interior/exterior video, remove any existing video with that same role
            if (in_array($data->role, [MediaRole::VideoInterior, MediaRole::VideoExterior], true)) {
                $existingExclusiveVideo = Media::query()
                    ->where('car_id', $car->id)
                    ->where('role', $data->role)
                    ->first();

                if ($existingExclusiveVideo !== null) {
                    $this->videoStorage->delete($existingExclusiveVideo->storage_key);
                    $existingExclusiveVideo->delete();
                }
            }

            $nextPosition = ((int) Media::query()->where('car_id', $car->id)->max('position')) + 1;

            return Media::query()->create([
                'car_id' => $car->id,
                'kind' => $kind,
                'role' => $data->role,
                'provider' => $provider,
                'storage_key' => $data->storageKey,
                'url' => $url,
                'published_url' => $url,
                'mime' => $mime,
                'bytes' => $bytes,
                'width' => $width,
                'height' => $height,
                'duration_s' => null,
                'position' => $nextPosition,
                'alt' => $data->alt,
                'confirmed_at' => now(),
            ]);
        });

        // Dispatch background job to generate/prewarm derivatives for photos
        if ($isPhoto) {
            GenerateDerivatives::dispatch($media->id);
        }

        $this->revalidator->revalidate(["car:{$car->slug}"]);

        return $media;
    }
}
