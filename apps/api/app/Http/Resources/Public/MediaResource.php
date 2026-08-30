<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Media
 */
final class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kind' => $this->kind->toArray(),
            'role' => $this->role->toArray(),
            'url' => $this->published_url ?? $this->url,
            'width' => $this->width,
            'height' => $this->height,
            'position' => $this->position,
            'alt' => $this->alt,
        ];
    }
}
