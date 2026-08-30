<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

final class SettingController extends Controller
{
    /**
     * Get public garage settings.
     */
    public function __invoke(): JsonResponse
    {
        $settings = Setting::query()->pluck('value', 'key')->all();

        return response()->json([
            'data' => $settings,
        ]);
    }
}
