<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Cars\ChangeCarStatus;
use App\Enums\CarStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Cars\ChangeCarStatusRequest;
use App\Http\Resources\Admin\CarResource as AdminCarResource;
use App\Models\Car;

final class CarStatusController extends Controller
{
    /**
     * Transition the car to the specified status.
     */
    public function __invoke(ChangeCarStatusRequest $request, int $id, ChangeCarStatus $changeCarStatus): AdminCarResource
    {
        /** @var Car $car */
        $car = Car::query()->findOrFail($id);

        $newStatus = CarStatus::from((string) $request->validated('status'));

        $updatedCar = $changeCarStatus->handle($car, $newStatus);

        return new AdminCarResource($updatedCar);
    }
}
