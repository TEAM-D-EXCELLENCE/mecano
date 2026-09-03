<?php

declare(strict_types=1);

namespace App\Actions\Admin\Media;

use App\Data\Media\CreateUploadSignatureData;
use App\Data\Media\SignedUpload;
use App\Data\Media\UploadConstraints;
use App\Enums\MediaKind;
use App\Exceptions\VideoLimitExceededException;
use App\Models\Car;
use App\Support\Contracts\ImageStorage;
use App\Support\Contracts\VideoStorage;
use Illuminate\Support\Str;

final readonly class CreateUploadSignature
{
    public function __construct(
        private ImageStorage $imageStorage,
        private VideoStorage $videoStorage,
    ) {}

    /**
     * Generate signed upload parameters for direct upload to Cloudinary (photos and videos).
     *
     * @throws VideoLimitExceededException
     */
    public function handle(CreateUploadSignatureData $data): SignedUpload
    {
        /** @var Car $car */
        $car = Car::query()->findOrFail($data->carId);

        if ($data->kind === MediaKind::Photo) {
            $constraints = UploadConstraints::forPhotos();
            $folder = config('media.photos.upload_folder', 'mecano/cars')."/{$car->id}";

            return $this->imageStorage->signedUploadParams($folder, $constraints);
        }

        // Check video limit (max 2 videos per car: 1 interior + 1 exterior)
        $existingVideosCount = $car->videos()->count();
        $maxVideos = (int) config('media.videos.max_count_per_car', 2);

        if ($existingVideosCount >= $maxVideos) {
            throw new VideoLimitExceededException;
        }

        $constraints = new UploadConstraints(
            maxSizeBytes: (int) config('media.videos.max_size_bytes', 200 * 1024 * 1024),
            allowedMimes: (array) config('media.videos.allowed_mimes', ['video/mp4', 'video/quicktime']),
            ttlSeconds: (int) config('media.videos.signature_ttl_seconds', 900),
        );

        // Cloudinary identifie la ressource par son `public_id`, sans
        // extension : c'est le point d'entrée `video` qui fixe le type.
        $folder = config('media.videos.upload_folder', 'mecano/cars');
        $key = "{$folder}/{$car->id}/videos/".(string) Str::uuid();

        return $this->videoStorage->signedUploadParams($key, $constraints);
    }
}
