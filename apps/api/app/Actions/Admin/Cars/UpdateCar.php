<?php

declare(strict_types=1);

namespace App\Actions\Admin\Cars;

use App\Data\UpdateCarData;
use App\Models\Car;

final readonly class UpdateCar
{
    /**
     * Update car listing attributes (slug remains immutable).
     */
    public function handle(Car $car, UpdateCarData $data): Car
    {
        if ($data->brandId !== null) {
            $car->brand_id = $data->brandId;
        }

        if ($data->model !== null) {
            $car->model = $data->model;
        }

        if ($data->year !== null) {
            $car->year = $data->year;
        }

        if ($data->mileageKm !== null) {
            $car->mileage_km = $data->mileageKm;
        }

        if ($data->priceXaf !== null) {
            $car->price_xaf = $data->priceXaf;
        }

        if ($data->fuel !== null) {
            $car->fuel = $data->fuel;
        }

        if ($data->transmission !== null) {
            $car->transmission = $data->transmission;
        }

        if ($data->color !== null) {
            $car->color = $data->color;
        }

        if ($data->condition !== null) {
            $car->condition = $data->condition;
        }

        if ($data->description !== null) {
            $car->description = $data->description;
        }

        if ($data->isFeatured !== null) {
            $car->is_featured = $data->isFeatured;
        }

        $car->save();

        return $car->load(['brand', 'mainPhoto', 'photos', 'videos']);
    }
}
