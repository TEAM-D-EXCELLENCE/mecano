<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class HealthController extends Controller
{
    /**
     * Handle the incoming health check request.
     */
    public function __invoke(): JsonResponse
    {
        try {
            // Check Database connectivity
            DB::connection()->getPdo();

            // Check Queue tables / storage
            if (config('queue.default') === 'database') {
                DB::table('jobs')->limit(1)->get();
            }

            // Check Cache store
            Cache::store()->has('health_check_ping');

            return response()->json([
                'status' => 'ok',
                'timestamp' => now()->toIso8601String(),
            ], 200);
        } catch (Throwable $e) {
            Log::warning('Health check probe failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'unhealthy',
                'timestamp' => now()->toIso8601String(),
            ], 503);
        }
    }
}
