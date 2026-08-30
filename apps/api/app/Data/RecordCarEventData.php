<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\CarEventType;
use App\Http\Requests\Public\RecordCarEventRequest;

final readonly class RecordCarEventData
{
    public function __construct(
        public CarEventType $type,
        public ?string $ipHash,
        public ?string $referer,
    ) {}

    public static function fromRequest(RecordCarEventRequest $request): self
    {
        $type = CarEventType::from((string) $request->validated('type'));

        // Compute salted SHA-256 IP hash (never store raw IP addresses)
        $ip = (string) $request->ip();
        $salt = (string) config('app.key');
        $ipHash = $ip !== '' ? hash('sha256', "{$ip}_{$salt}") : null;

        // Clean referer: extract domain only to preserve privacy
        $rawReferer = $request->validated('referer') ?? $request->header('referer');
        $referer = null;

        if (is_string($rawReferer) && $rawReferer !== '') {
            $parsedHost = parse_url($rawReferer, PHP_URL_HOST);
            $referer = is_string($parsedHost) ? substr($parsedHost, 0, 255) : substr($rawReferer, 0, 255);
        }

        return new self(
            type: $type,
            ipHash: $ipHash,
            referer: $referer,
        );
    }
}
