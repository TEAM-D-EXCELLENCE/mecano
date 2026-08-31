<?php

declare(strict_types=1);

namespace App\Actions\Admin\Media;

use App\Enums\CarStatus;
use App\Enums\MediaKind;
use App\Enums\MediaRole;
use App\Exceptions\LastPhotoOfPublishedCarException;
use App\Models\Media;
use App\Support\Contracts\FrontendRevalidator;
use App\Support\Contracts\ImageStorage;
use App\Support\Contracts\VideoStorage;
use Illuminate\Support\Facades\DB;

final readonly class DeleteMedia
{
    public function __construct(
        private ImageStorage $imageStorage,
        private VideoStorage $videoStorage,
        private FrontendRevalidator $revalidator,
    ) {}

    /**
     * Delete a media item from DB and from the external storage provider.
     * If the deleted media was the main photo, automatically promote the first
     * remaining gallery photo to main photo to preserve catalog integrity.
     *
     * @throws LastPhotoOfPublishedCarException
     */
    public function handle(Media $media): void
    {
        $car = $media->car;

        $this->guardLastPhotoOfPublishedCar($media);

        // 1. Delete from external storage
        if ($media->kind === MediaKind::Photo) {
            $this->imageStorage->delete($media->storage_key);
        } else {
            $this->videoStorage->delete($media->storage_key);
        }

        // 2. Database cleanup within a transaction
        DB::transaction(function () use ($media): void {
            $carId = $media->car_id;
            $wasMain = $media->role === MediaRole::Main;

            $media->delete();

            // If the deleted photo was main, promote the first gallery photo if available
            if ($wasMain) {
                $nextMainPhoto = Media::query()
                    ->where('car_id', $carId)
                    ->where('kind', MediaKind::Photo)
                    ->where('role', MediaRole::Gallery)
                    ->orderBy('position')
                    ->first();

                if ($nextMainPhoto !== null) {
                    $nextMainPhoto->role = MediaRole::Main;
                    $nextMainPhoto->save();
                }
            }
        });

        if ($car !== null) {
            $this->revalidator->revalidate(["car:{$car->slug}"]);
        }
    }

    /**
     * Une annonce en ligne garde toujours une photo principale.
     *
     * `ChangeCarStatus` interdit de publier sans photo principale, mais rien
     * n'empêchait de retirer la dernière photo d'une annonce déjà publiée :
     * elle restait alors servie au public sans aucun visuel. L'invariant est
     * métier, il est donc vérifié ici et pas seulement dans l'interface
     * (docs/01-architecture/03-modele-de-donnees.md).
     *
     * @throws LastPhotoOfPublishedCarException
     */
    private function guardLastPhotoOfPublishedCar(Media $media): void
    {
        if ($media->kind !== MediaKind::Photo) {
            return;
        }

        $car = $media->car;

        if ($car === null || $car->status === CarStatus::Draft) {
            return;
        }

        $remainingPhotos = Media::query()
            ->where('car_id', $media->car_id)
            ->where('kind', MediaKind::Photo)
            ->whereKeyNot($media->id)
            ->count();

        if ($remainingPhotos === 0) {
            throw new LastPhotoOfPublishedCarException;
        }
    }
}
