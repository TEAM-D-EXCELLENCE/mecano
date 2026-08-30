<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Data\CarCatalogFilterData;
use App\Enums\CarStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\CarDetailResource;
use App\Http\Resources\Public\CarListResource;
use App\Models\Car;
use App\Queries\CarCatalogQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CarController extends Controller
{
    /**
     * Display a listing of public cars with filters, sorting, and pagination.
     */
    public function index(Request $request, CarCatalogQuery $catalogQuery): AnonymousResourceCollection
    {
        $filters = CarCatalogFilterData::fromRequest($request);
        $paginator = $catalogQuery->paginate($filters);

        return CarListResource::collection($paginator);
    }

    /**
     * Display the specified car detail for public display.
     */
    public function show(string $slug): CarDetailResource
    {
        $car = Car::query()
            ->where('slug', $slug)
            ->whereIn('status', [
                CarStatus::Available->value,
                CarStatus::Reserved->value,
                CarStatus::Sold->value,
            ])
            ->with(['brand', 'mainPhoto', 'photos', 'videos'])
            ->first();

        if (! $car) {
            throw new NotFoundHttpException('Ce véhicule est introuvable ou n\'est plus disponible.');
        }

        return new CarDetailResource($car);
    }
}
