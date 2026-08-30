<?php

declare(strict_types=1);

namespace App\Actions\Admin\Cars;

use App\Models\Car;
use App\Support\Contracts\FrontendRevalidator;

final readonly class DeleteCar
{
    public function __construct(
        private FrontendRevalidator $revalidator,
    ) {}

    /**
     * Archive (soft-delete) a car listing.
     */
    public function handle(Car $car): void
    {
        $slug = $car->slug;
        $car->delete();

        $this->revalidator->revalidate(["car:{$slug}", 'cars', 'home']);
    }
}
