<?php

declare(strict_types=1);

namespace App\Data\Media;

final readonly class ObjectMeta
{
    public function __construct(
        public string $key,
        public int $sizeBytes,
        public string $mimeType,
        public ?int $width = null,
        public ?int $height = null,
        public ?string $url = null,
    ) {}
}
