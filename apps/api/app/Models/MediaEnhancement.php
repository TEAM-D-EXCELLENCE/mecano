<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EnhancementStatus;
use App\Enums\EnhancementType;
use App\Enums\MediaProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MediaEnhancement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'media_id',
        'type',
        'provider',
        'status',
        'params',
        'result_key',
        'result_url',
        'error',
        'cost_units',
        'approved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => EnhancementType::class,
            'provider' => MediaProvider::class,
            'status' => EnhancementStatus::class,
            'params' => 'array',
            'cost_units' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
