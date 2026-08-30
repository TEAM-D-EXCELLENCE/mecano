<?php

declare(strict_types=1);

namespace App\Actions\Admin\Media;

use App\Enums\MediaRole;
use App\Models\Media;
use Illuminate\Support\Facades\DB;

final readonly class UpdateMedia
{
    /**
     * Update media attributes (e.g. promote to main photo, set alt text).
     */
    public function handle(Media $media, ?MediaRole $role = null, ?string $alt = null): Media
    {
        DB::transaction(function () use ($media, $role, $alt): void {
            if ($role !== null) {
                // If promoting to Main photo, demote existing main photo of the same car
                if ($role === MediaRole::Main && $media->role !== MediaRole::Main) {
                    Media::query()
                        ->where('car_id', $media->car_id)
                        ->where('role', MediaRole::Main)
                        ->update(['role' => MediaRole::Gallery]);
                }

                $media->role = $role;
            }

            if ($alt !== null) {
                $media->alt = $alt;
            }

            $media->save();
        });

        return $media;
    }
}
