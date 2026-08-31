<?php

declare(strict_types=1);

namespace App\Exceptions;

final class MediaNotEnhanceableException extends ApiException
{
    public function __construct(string $message = 'Seules les photos peuvent être améliorées.')
    {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return 422;
    }

    public function errorCode(): string
    {
        return 'MEDIA_NOT_ENHANCEABLE';
    }
}
