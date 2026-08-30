<?php

declare(strict_types=1);

namespace App\Data;

use App\Http\Requests\Admin\Brands\CreateBrandRequest;
use Illuminate\Support\Str;

final readonly class CreateBrandData
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $logo_url = null,
        public int $position = 0,
        public bool $is_active = true,
    ) {}

    public static function fromRequest(CreateBrandRequest $request): self
    {
        $name = (string) $request->validated('name');
        $rawSlug = $request->validated('slug');
        $slug = $rawSlug !== null && (string) $rawSlug !== '' ? (string) $rawSlug : Str::slug($name);

        return new self(
            name: $name,
            slug: $slug,
            logo_url: $request->validated('logo_url'),
            position: (int) ($request->validated('position') ?? 0),
            is_active: (bool) ($request->validated('is_active') ?? true),
        );
    }
}
