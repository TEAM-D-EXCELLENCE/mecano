<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MediaKind;
use Illuminate\Database\Eloquent\Builder;

final class Video extends Media
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        self::addGlobalScope('video', static function (Builder $builder): void {
            $builder->where('kind', MediaKind::Video->value);
        });

        self::creating(static function (self $model): void {
            $model->kind = MediaKind::Video;
        });
    }
}
