<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Contracts\FrontendRevalidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SettingController extends Controller
{
    /**
     * Get all settings for admin backoffice.
     */
    public function show(): JsonResponse
    {
        $settings = Setting::query()->pluck('value', 'key')->all();

        return response()->json([
            'data' => $settings,
        ]);
    }

    /**
     * Update settings in bulk.
     */
    public function update(Request $request, FrontendRevalidator $revalidator): JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable'],
        ]);

        /** @var array<string, mixed> $items */
        $items = $validated['settings'];

        foreach ($items as $key => $value) {
            Setting::set((string) $key, $value);
        }

        $revalidator->revalidate(['settings', 'home']);

        $allSettings = Setting::query()->pluck('value', 'key')->all();

        return response()->json([
            'data' => $allSettings,
        ]);
    }
}
