<?php

declare(strict_types=1);

namespace App\Exceptions;

final class InvalidCredentialsException extends ApiException
{
    public function __construct(string $message = 'Identifiants incorrects.')
    {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return 401;
    }

    public function errorCode(): string
    {
        return 'INVALID_CREDENTIALS';
    }
}
