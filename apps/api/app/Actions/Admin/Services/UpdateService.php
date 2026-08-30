<?php

declare(strict_types=1);

namespace App\Actions\Admin\Services;

use App\Http\Requests\Admin\Services\UpdateServiceRequest;
use App\Models\Service;

final readonly class UpdateService
{
    /**
     * Update service attributes.
     * Note: Deactivation (is_active = false) is always preferred over deletion (CDC §3.3).
     */
    public function handle(Service $service, UpdateServiceRequest $request): Service
    {
        if ($request->has('title')) {
            $service->title = (string) $request->validated('title');
        }

        if ($request->has('excerpt')) {
            $service->excerpt = $request->validated('excerpt');
        }

        if ($request->has('description')) {
            $service->description = $request->validated('description');
        }

        if ($request->has('icon')) {
            $service->icon = $request->validated('icon');
        }

        if ($request->has('price_from_xaf')) {
            $service->price_from_xaf = $request->validated('price_from_xaf');
        }

        if ($request->has('is_active')) {
            $service->is_active = (bool) $request->validated('is_active');
        }

        if ($request->has('position')) {
            $service->position = (int) $request->validated('position');
        }

        $service->save();

        return $service;
    }
}
