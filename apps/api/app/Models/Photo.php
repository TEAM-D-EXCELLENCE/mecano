<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MediaKind;
use Illuminate\Database\Eloquent\Builder;

final class Photo extends Media
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        self::addGlobalScope('photo', static function (Builder $builder): void {
            $builder->where('kind', MediaKind::Photo->value);
        });

        self::creating(static function (self $model): void {
            $model->kind = MediaKind::Photo;
        });
    }
}
