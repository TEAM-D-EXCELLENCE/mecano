<?php

declare(strict_types=1);

namespace App\Exceptions;

final class EnhancementNotApprovableException extends ApiException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return 422;
    }

    public function errorCode(): string
    {
        return 'ENHANCEMENT_NOT_APPROVABLE';
    }
}
