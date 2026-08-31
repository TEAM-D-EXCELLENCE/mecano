<?php

declare(strict_types=1);

namespace App\Exceptions;

final class LastPhotoOfPublishedCarException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            "Cette annonce est en ligne : elle doit garder au moins une photo principale. "
            ."Ajoutez une autre photo, ou retirez l'annonce de la vente avant de supprimer celle-ci."
        );
    }

    public function statusCode(): int
    {
        return 409;
    }

    public function errorCode(): string
    {
        return 'LAST_PHOTO_OF_PUBLISHED_CAR';
    }
}
