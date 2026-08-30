<?php

declare(strict_types=1);

namespace App\Data\Media;

use App\Enums\MediaProvider;

final readonly class EnhancedImageResult
{
    /**
     * @param  array<string, mixed>|null  $params
     */
    public function __construct(
        public string $resultKey,
        public string $resultUrl,
        public MediaProvider $provider,
        public int $costUnits = 0,
        public ?array $params = null,
    ) {}
}
