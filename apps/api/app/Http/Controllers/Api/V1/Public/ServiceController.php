<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ServiceController extends Controller
{
    /**
     * List all active services sorted by position.
     */
    public function index(): AnonymousResourceCollection
    {
        $services = Service::query()->active()->get();

        return ServiceResource::collection($services);
    }
}
