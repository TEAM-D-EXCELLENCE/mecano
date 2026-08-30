<?php

declare(strict_types=1);

namespace App\Actions\Admin\Cars;

use App\Data\CreateCarData;
use App\Models\Brand;
use App\Models\Car;
use App\Support\Contracts\FrontendRevalidator;
use Illuminate\Support\Str;

final readonly class CreateCar
{
    public function __construct(
        private FrontendRevalidator $revalidator,
    ) {}

    /**
     * Create a new car listing in the database with an immutable generated slug.
     */
    public function handle(CreateCarData $data): Car
    {
        $brand = Brand::query()->findOrFail($data->brandId);

        // Generate preliminary unique slug: {brand_slug}-{model_slug}-{year}-{unique}
        $brandSlug = Str::slug($brand->name);
        $modelSlug = Str::slug($data->model);
        $uniqueSuffix = Str::lower(Str::random(6));
        $slug = "{$brandSlug}-{$modelSlug}-{$data->year}-{$uniqueSuffix}";

        /** @var Car $car */
        $car = Car::query()->create([
            'slug' => $slug,
            'brand_id' => $data->brandId,
            'model' => $data->model,
            'year' => $data->year,
            'mileage_km' => $data->mileageKm,
            'price_xaf' => $data->priceXaf,
            'fuel' => $data->fuel,
            'transmission' => $data->transmission,
            'color' => $data->color,
            'condition' => $data->condition,
            'description' => $data->description,
            'status' => $data->status,
            'is_featured' => $data->isFeatured,
            'published_at' => null,
            'sold_at' => null,
            'views_count' => 0,
            'whatsapp_clicks_count' => 0,
        ]);

        // Finalize immutable slug with the actual primary key ID
        $finalSlug = "{$brandSlug}-{$modelSlug}-{$data->year}-{$car->id}";
        $car->slug = $finalSlug;
        $car->save();

        $this->revalidator->revalidate(["car:{$finalSlug}", 'cars', 'home']);

        return $car->load(['brand', 'mainPhoto', 'photos', 'videos']);
    }
}
