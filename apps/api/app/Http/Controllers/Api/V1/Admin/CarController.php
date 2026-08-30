<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Cars\CreateCar;
use App\Actions\Admin\Cars\DeleteCar;
use App\Actions\Admin\Cars\UpdateCar;
use App\Data\CreateCarData;
use App\Data\UpdateCarData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Cars\CreateCarRequest;
use App\Http\Requests\Admin\Cars\UpdateCarRequest;
use App\Http\Resources\Admin\CarResource as AdminCarResource;
use App\Models\Car;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class CarController extends Controller
{
    /**
     * Display a listing of cars for the admin backoffice.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Car::query()
            ->with(['brand', 'mainPhoto']);

        // Filter by status if provided
        $status = $request->query('status') ?? $request->query('statut');
        if (is_string($status) && $status !== '') {
            $query->where('status', $status);
        }

        // Search query (keyword in model, description, or brand name)
        $search = $request->query('recherche') ?? $request->query('q');
        if (is_string($search) && trim($search) !== '') {
            $searchTerm = '%'.trim($search).'%';
            $query->where(static function (Builder $b) use ($searchTerm): void {
                $b->where('model', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhereHas('brand', static function (Builder $brandQuery) use ($searchTerm): void {
                        $brandQuery->where('name', 'like', $searchTerm);
                    });
            });
        }

        $perPage = min(max(1, $request->integer('per_page', 20)), 50);
        $cars = $query->orderByDesc('created_at')->paginate($perPage);

        return AdminCarResource::collection($cars);
    }

    /**
     * Store a newly created car listing in the backoffice.
     */
    public function store(CreateCarRequest $request, CreateCar $createCar): JsonResponse
    {
        $car = $createCar->handle(CreateCarData::fromRequest($request));

        return (new AdminCarResource($car))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified car listing for admin edition.
     */
    public function show(int $id): AdminCarResource
    {
        /** @var Car $car */
        $car = Car::query()
            ->with(['brand', 'mainPhoto', 'photos', 'videos'])
            ->findOrFail($id);

        return new AdminCarResource($car);
    }

    /**
     * Update the specified car listing attributes.
     */
    public function update(UpdateCarRequest $request, int $id, UpdateCar $updateCar): AdminCarResource
    {
        /** @var Car $car */
        $car = Car::query()->findOrFail($id);

        $updatedCar = $updateCar->handle($car, UpdateCarData::fromRequest($request));

        return new AdminCarResource($updatedCar);
    }

    /**
     * Archive (soft-delete) the specified car listing.
     */
    public function destroy(int $id, DeleteCar $deleteCar): Response
    {
        /** @var Car $car */
        $car = Car::query()->findOrFail($id);

        $deleteCar->handle($car);

        return response()->noContent();
    }
}
