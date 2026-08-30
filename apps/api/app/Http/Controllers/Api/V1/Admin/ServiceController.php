<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Services\CreateService;
use App\Actions\Admin\Services\UpdateService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Services\CreateServiceRequest;
use App\Http\Requests\Admin\Services\UpdateServiceRequest;
use App\Http\Resources\Admin\ServiceResource as AdminServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ServiceController extends Controller
{
    /**
     * List all services (active and inactive) with posts count for backoffice.
     */
    public function index(): AnonymousResourceCollection
    {
        $services = Service::query()
            ->withCount('posts')
            ->orderBy('position')
            ->get();

        return AdminServiceResource::collection($services);
    }

    /**
     * Create a new service.
     */
    public function store(CreateServiceRequest $request, CreateService $createService): JsonResponse
    {
        $service = $createService->handle($request);

        return (new AdminServiceResource($service))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update an existing service.
     * Note: Deactivation (is_active = false) is preferred over deletion (CDC §3.3).
     */
    public function update(UpdateServiceRequest $request, int $id, UpdateService $updateService): AdminServiceResource
    {
        /** @var Service $service */
        $service = Service::query()->findOrFail($id);

        $updatedService = $updateService->handle($service, $request);

        return new AdminServiceResource($updatedService);
    }
}
