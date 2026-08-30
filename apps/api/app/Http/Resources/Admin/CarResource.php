<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Car
 */
final class CarResource extends JsonResource
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
            'brand_id' => $this->brand_id,
            'brand' => BrandResource::make($this->whenLoaded('brand')),
            'model' => $this->model,
            'year' => $this->year,
            'mileage_km' => $this->mileage_km,
            'price_xaf' => $this->price_xaf,
            'fuel' => $this->fuel->toArray(),
            'transmission' => $this->transmission->toArray(),
            'color' => $this->color,
            'condition' => $this->condition->toArray(),
            'description' => $this->description,
            'status' => $this->status->toArray(),
            'is_featured' => $this->is_featured,
            'is_publishable' => $this->isPublishable(),
            'published_at' => $this->published_at?->toIso8601String(),
            'sold_at' => $this->sold_at?->toIso8601String(),
            'views_count' => $this->views_count,
            'whatsapp_clicks_count' => $this->whatsapp_clicks_count,
            'main_photo' => MediaResource::make($this->whenLoaded('mainPhoto')),
            'photos' => MediaResource::collection($this->whenLoaded('photos')),
            'videos' => MediaResource::collection($this->whenLoaded('videos')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
