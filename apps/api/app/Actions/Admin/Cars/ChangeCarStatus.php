<?php

declare(strict_types=1);

namespace App\Actions\Admin\Cars;

use App\Enums\CarStatus;
use App\Exceptions\CarNotPublishableException;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Car;
use App\Support\Contracts\FrontendRevalidator;

final readonly class ChangeCarStatus
{
    public function __construct(
        private FrontendRevalidator $revalidator,
    ) {}

    /**
     * Allowed status transition map.
     *
     * Key   → current status
     * Value → list of allowed next statuses
     *
     * Invariant: `draft` is NEVER reachable from any other status.
     *
     * @var array<string, list<CarStatus>>
     */
    private const array TRANSITIONS = [
        CarStatus::Draft->value => [CarStatus::Available],
        CarStatus::Available->value => [CarStatus::Reserved, CarStatus::Sold],
        CarStatus::Reserved->value => [CarStatus::Available, CarStatus::Sold],
        CarStatus::Sold->value => [CarStatus::Available],
    ];

    /**
     * Transition the car to the specified new status.
     *
     * @throws InvalidStatusTransitionException
     * @throws CarNotPublishableException
     */
    public function handle(Car $car, CarStatus $newStatus): Car
    {
        $currentStatus = $car->status;
        $allowed = self::TRANSITIONS[$currentStatus->value] ?? [];

        $isAllowed = count(array_filter(
            $allowed,
            static fn (CarStatus $s) => $s === $newStatus
        )) > 0;

        if (! $isAllowed) {
            throw new InvalidStatusTransitionException($currentStatus->value, $newStatus->value);
        }

        // Invariant: transition to available requires at least one main photo (CDC §3.1)
        if ($newStatus === CarStatus::Available && ! $car->isPublishable()) {
            throw new CarNotPublishableException;
        }

        $car->status = $newStatus;

        // Update timestamps on transitions
        if ($newStatus === CarStatus::Available && $car->published_at === null) {
            $car->published_at = now();
        }

        if ($newStatus === CarStatus::Sold) {
            $car->sold_at = now();
        }

        // If returning to available from sold or reserved, clear sold_at
        if ($newStatus === CarStatus::Available && $currentStatus === CarStatus::Sold) {
            $car->sold_at = null;
        }

        $car->save();

        $this->revalidator->revalidate(["car:{$car->slug}", 'cars', 'home']);

        return $car->load(['brand', 'mainPhoto']);
    }
}
