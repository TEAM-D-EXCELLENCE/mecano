<?php

declare(strict_types=1);

namespace App\Data\Media;

final readonly class SignedUpload
{
    /**
     * @param  array<string, mixed>  $fields
     */
    public function __construct(
        public string $uploadUrl,
        public array $fields,
        public string $storageKey,
        public string $expiresAt,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'upload_url' => $this->uploadUrl,
            'fields' => $this->fields,
            'storage_key' => $this->storageKey,
            'expires_at' => $this->expiresAt,
        ];
    }
}
