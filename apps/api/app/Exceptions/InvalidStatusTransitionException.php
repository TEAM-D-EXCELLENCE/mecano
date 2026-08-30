<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

final class InvalidStatusTransitionException extends ApiException
{
    public function __construct(string $from, string $to)
    {
        parent::__construct(
            "La transition de statut «{$from}» vers «{$to}» n'est pas autorisée."
        );
    }

    public function statusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }

    public function errorCode(): string
    {
        return 'INVALID_STATUS_TRANSITION';
    }
}
