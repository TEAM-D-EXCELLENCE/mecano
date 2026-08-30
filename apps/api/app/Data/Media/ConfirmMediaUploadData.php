<?php

declare(strict_types=1);

namespace App\Data\Media;

use App\Enums\MediaRole;
use App\Http\Requests\Admin\Media\ConfirmMediaUploadRequest;

final readonly class ConfirmMediaUploadData
{
    public function __construct(
        public string $storageKey,
        public MediaRole $role,
        public ?int $width = null,
        public ?int $height = null,
        public ?int $bytes = null,
        public ?string $mime = null,
        public ?string $alt = null,
    ) {}

    public static function fromRequest(ConfirmMediaUploadRequest $request): self
    {
        return new self(
            storageKey: (string) $request->validated('storage_key'),
            role: MediaRole::from((string) $request->validated('role')),
            width: $request->has('width') && $request->validated('width') !== null ? (int) $request->validated('width') : null,
            height: $request->has('height') && $request->validated('height') !== null ? (int) $request->validated('height') : null,
            bytes: $request->has('bytes') && $request->validated('bytes') !== null ? (int) $request->validated('bytes') : null,
            mime: $request->has('mime') && $request->validated('mime') !== null ? (string) $request->validated('mime') : null,
            alt: $request->has('alt') && $request->validated('alt') !== null ? (string) $request->validated('alt') : null,
        );
    }
}
