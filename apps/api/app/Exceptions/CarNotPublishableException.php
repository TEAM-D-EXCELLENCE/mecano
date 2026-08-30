<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

final class CarNotPublishableException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            'Cette annonce ne peut pas être publiée : elle doit avoir au moins une photo principale.'
        );
    }

    public function statusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }

    public function errorCode(): string
    {
        return 'CAR_NOT_PUBLISHABLE';
    }
}
