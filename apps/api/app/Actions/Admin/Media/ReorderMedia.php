<?php

declare(strict_types=1);

namespace App\Actions\Admin\Media;

use App\Models\Car;
use App\Models\Media;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class ReorderMedia
{
    /**
     * Reorder media items for a given car by their provided ID sequence.
     *
     * @param  list<int>  $mediaIds
     * @return Collection<int, Media>
     */
    public function handle(int $carId, array $mediaIds): Collection
    {
        /** @var Car $car */
        $car = Car::query()->findOrFail($carId);

        DB::transaction(function () use ($car, $mediaIds): void {
            foreach ($mediaIds as $index => $mediaId) {
                Media::query()
                    ->where('car_id', $car->id)
                    ->where('id', $mediaId)
                    ->update(['position' => $index + 1]);
            }
        });

        return Media::query()
            ->where('car_id', $car->id)
            ->orderBy('position')
            ->get();
    }
}
