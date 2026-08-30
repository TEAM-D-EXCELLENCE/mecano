<?php

declare(strict_types=1);

namespace App\Queries;

use App\Data\CarCatalogFilterData;
use App\Enums\CarStatus;
use App\Models\Car;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final readonly class CarCatalogQuery
{
    /**
     * Execute the public car catalog query with filters, sorting, and pagination.
     *
     * @return LengthAwarePaginator<Car>
     */
    public function paginate(CarCatalogFilterData $filters): LengthAwarePaginator
    {
        $query = Car::query()
            ->with(['brand', 'mainPhoto']);

        // Invariant: Draft cars are NEVER included in public queries
        $allowedStatuses = [
            CarStatus::Available->value,
            CarStatus::Reserved->value,
        ];

        if ($filters->inclureVendus) {
            $allowedStatuses[] = CarStatus::Sold->value;
        }

        $query->whereIn('status', $allowedStatuses);

        // Filter: Marque (slug)
        if ($filters->marque !== null) {
            $query->whereHas('brand', static function (Builder $b) use ($filters): void {
                $b->where('slug', $filters->marque);
            });
        }

        // Filter: Prix min / max (FCFA)
        if ($filters->prixMin !== null) {
            $query->where('price_xaf', '>=', $filters->prixMin);
        }

        if ($filters->prixMax !== null) {
            $query->where('price_xaf', '<=', $filters->prixMax);
        }

        // Filter: Année min / max
        if ($filters->anneeMin !== null) {
            $query->where('year', '>=', $filters->anneeMin);
        }

        if ($filters->anneeMax !== null) {
            $query->where('year', '<=', $filters->anneeMax);
        }

        // Filter: Carburant
        if ($filters->carburant !== null) {
            $query->where('fuel', $filters->carburant->value);
        }

        // Filter: Transmission
        if ($filters->transmission !== null) {
            $query->where('transmission', $filters->transmission->value);
        }

        // Sorting
        match ($filters->tri) {
            'prix_asc' => $query->orderBy('price_xaf')->orderByDesc('id'),
            'prix_desc' => $query->orderByDesc('price_xaf')->orderByDesc('id'),
            'km_asc' => $query->orderBy('mileage_km')->orderByDesc('id'),
            default => $query->orderByDesc('published_at')->orderByDesc('id'),
        };

        return $query->paginate(
            perPage: $filters->perPage,
            page: $filters->page
        );
    }
}
