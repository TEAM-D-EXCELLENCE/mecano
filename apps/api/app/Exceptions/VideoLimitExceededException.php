<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

final class VideoLimitExceededException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            'Ce véhicule a déjà atteint la limite maximale autorisée de 2 vidéos (intérieur et extérieur).'
        );
    }

    public function statusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }

    public function errorCode(): string
    {
        return 'VIDEO_LIMIT_EXCEEDED';
    }
}
