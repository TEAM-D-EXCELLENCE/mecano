<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Dashboard\GetDashboardMetrics;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class DashboardController extends Controller
{
    /**
     * Get aggregated business metrics for the admin backoffice dashboard.
     */
    public function __invoke(GetDashboardMetrics $getDashboardMetrics): JsonResponse
    {
        $data = $getDashboardMetrics->handle();

        return response()->json([
            'data' => $data,
        ]);
    }
}
