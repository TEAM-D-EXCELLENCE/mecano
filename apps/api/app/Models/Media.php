<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MediaKind;
use App\Enums\MediaProvider;
use App\Enums\MediaRole;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'car_id',
        'kind',
        'role',
        'provider',
        'storage_key',
        'url',
        'published_url',
        'mime',
        'bytes',
        'width',
        'height',
        'duration_s',
        'position',
        'alt',
        'confirmed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'car_id' => 'integer',
            'kind' => MediaKind::class,
            'role' => MediaRole::class,
            'provider' => MediaProvider::class,
            'bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration_s' => 'integer',
            'position' => 'integer',
            'confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get the car that owns this media.
     *
     * @return BelongsTo<Car, $this>
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get the enhancements for this media.
     *
     * @return HasMany<MediaEnhancement, $this>
     */
    public function enhancements(): HasMany
    {
        return $this->hasMany(MediaEnhancement::class);
    }
}
