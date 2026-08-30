<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

final class MediaUploadNotFoundException extends ApiException
{
    public function __construct(string $storageKey)
    {
        parent::__construct(
            "Le fichier associé à la clé de stockage «{$storageKey}» n'a pas été trouvé chez le fournisseur de stockage."
        );
    }

    public function statusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    public function errorCode(): string
    {
        return 'MEDIA_NOT_FOUND_IN_STORAGE';
    }
}
