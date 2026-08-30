<?php

declare(strict_types=1);

namespace App\Actions\Admin\Cars;

use App\Models\Car;

final readonly class DeleteCar
{
    /**
     * Archive (soft-delete) a car listing.
     */
    public function handle(Car $car): void
    {
        $car->delete();
    }
}
