<?php

declare(strict_types=1);

namespace App\Exceptions;

final class QuotaExceededException extends ApiException
{
    public function __construct(
        private readonly string $provider,
        private readonly int $used,
        private readonly int $limit,
        private readonly string $resetsAt,
    ) {
        parent::__construct(
            "Quota mensuel atteint ({$used}/{$limit}), disponible le 1er du mois."
        );
    }

    public function statusCode(): int
    {
        return 409;
    }

    public function errorCode(): string
    {
        return 'QUOTA_EXCEEDED';
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return [
            'provider' => $this->provider,
            'used' => $this->used,
            'limit' => $this->limit,
            'resets_at' => $this->resetsAt,
        ];
    }
}
