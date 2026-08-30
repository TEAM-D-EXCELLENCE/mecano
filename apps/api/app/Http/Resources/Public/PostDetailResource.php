<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Post
 */
final class PostDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'service' => $this->whenLoaded('service', fn () => $this->service ? [
                'slug' => $this->service->slug,
                'title' => $this->service->title,
            ] : null),
            'author' => $this->whenLoaded('author', fn () => [
                'name' => $this->author->name,
            ]),
            'cover_media' => $this->whenLoaded('coverMedia', fn () => $this->coverMedia ? [
                'url' => $this->coverMedia->published_url ?? $this->coverMedia->url,
                'width' => $this->coverMedia->width,
                'height' => $this->coverMedia->height,
                'alt' => $this->coverMedia->alt,
            ] : null),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
