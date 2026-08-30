<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\Car;
use App\Support\WhatsAppUrlBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Car
 */
final class CarDetailResource extends JsonResource
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
            'color' => $this->color,
            'condition' => $this->condition->toArray(),
            'description' => $this->description,
            'status' => $this->status->toArray(),
            'is_featured' => $this->is_featured,
            'main_photo' => MediaResource::make($this->whenLoaded('mainPhoto')),
            'photos' => MediaResource::collection($this->whenLoaded('photos')),
            'videos' => VideoResource::collection($this->whenLoaded('videos')),
            'published_at' => $this->published_at?->toIso8601String(),
            'sold_at' => $this->sold_at?->toIso8601String(),
            'whatsapp_url' => WhatsAppUrlBuilder::build($this->resource),
        ];
    }
}
