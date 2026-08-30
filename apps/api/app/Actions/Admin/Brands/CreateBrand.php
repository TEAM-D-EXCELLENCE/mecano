<?php

declare(strict_types=1);

namespace App\Actions\Admin\Brands;

use App\Data\CreateBrandData;
use App\Models\Brand;

final readonly class CreateBrand
{
    /**
     * Create a new brand in the database.
     */
    public function handle(CreateBrandData $data): Brand
    {
        return Brand::query()->create([
            'name' => $data->name,
            'slug' => $data->slug,
            'logo_url' => $data->logo_url,
            'position' => $data->position,
            'is_active' => $data->is_active,
        ]);
    }
}
