<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

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
            'car_id' => $this->car_id,
            'kind' => $this->kind->toArray(),
            'role' => $this->role->toArray(),
            'provider' => $this->provider->toArray(),
            'storage_key' => $this->storage_key,
            'url' => $this->url,
            'published_url' => $this->published_url,
            'mime' => $this->mime,
            'bytes' => $this->bytes,
            'width' => $this->width,
            'height' => $this->height,
            'duration_s' => $this->duration_s,
            'position' => $this->position,
            'alt' => $this->alt,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
