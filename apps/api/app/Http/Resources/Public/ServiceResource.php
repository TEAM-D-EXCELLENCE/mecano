<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Service
 */
final class ServiceResource extends JsonResource
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
            'description' => $this->description,
            'icon' => $this->icon,
            'price_from_xaf' => $this->price_from_xaf,
            'position' => $this->position,
        ];
    }
}
