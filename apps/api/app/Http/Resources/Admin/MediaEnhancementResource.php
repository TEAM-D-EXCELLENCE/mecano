<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\MediaEnhancement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MediaEnhancement
 */
final class MediaEnhancementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'media_id' => $this->media_id,
            'type' => $this->type->toArray(),
            'provider' => $this->provider->toArray(),
            'status' => $this->status->toArray(),
            'params' => $this->params,
            'result_key' => $this->result_key,
            'result_url' => $this->result_url,
            'error' => $this->error,
            'cost_units' => $this->cost_units,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
