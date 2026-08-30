<?php

declare(strict_types=1);

namespace App\Data\Media;

use App\Enums\MediaKind;
use App\Http\Requests\Admin\Media\CreateUploadSignatureRequest;

final readonly class CreateUploadSignatureData
{
    public function __construct(
        public int $carId,
        public MediaKind $kind,
        public string $mime,
        public int $bytes,
    ) {}

    public static function fromRequest(CreateUploadSignatureRequest $request): self
    {
        return new self(
            carId: (int) $request->validated('car_id'),
            kind: MediaKind::from((string) $request->validated('kind')),
            mime: (string) $request->validated('mime'),
            bytes: (int) $request->validated('bytes'),
        );
    }
}
