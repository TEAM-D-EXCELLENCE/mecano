<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Post
 */
final class PostListResource extends JsonResource
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
            'service' => $this->whenLoaded('service', fn () => [
                'slug' => $this->service->slug,
                'title' => $this->service->title,
            ]),
            'author' => $this->whenLoaded('author', fn () => [
                'name' => $this->author->name,
            ]),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
