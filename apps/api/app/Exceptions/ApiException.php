<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

abstract class ApiException extends Exception
{
    abstract public function statusCode(): int;

    abstract public function errorCode(): string;

    /**
     * @return array<string, mixed>|null
     */
    public function details(): ?array
    {
        return null;
    }
}
