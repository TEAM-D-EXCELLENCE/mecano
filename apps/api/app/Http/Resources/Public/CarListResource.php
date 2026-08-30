<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Car
 */
final class CarListResource extends JsonResource
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
            'slug' => $this->slug,
            'brand' => BrandResource::make($this->whenLoaded('brand')),
            'model' => $this->model,
            'year' => $this->year,
            'mileage_km' => $this->mileage_km,
            'price_xaf' => $this->price_xaf,
            'fuel' => $this->fuel->toArray(),
            'transmission' => $this->transmission->toArray(),
            'condition' => $this->condition->toArray(),
            'status' => $this->status->toArray(),
            'is_featured' => $this->is_featured,
            'main_photo' => MediaResource::make($this->whenLoaded('mainPhoto')),
            'published_at' => $this->published_at?->toIso8601String(),
            'sold_at' => $this->sold_at?->toIso8601String(),
        ];
    }
}
