<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Video
 */
final class VideoResource extends JsonResource
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
            'role' => $this->role->toArray(),
            'url' => $this->published_url ?? $this->url,
            'duration_s' => $this->duration_s,
            'position' => $this->position,
            'alt' => $this->alt,
        ];
    }
}
