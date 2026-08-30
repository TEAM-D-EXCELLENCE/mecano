<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CarStatus;
use App\Enums\FuelType;
use App\Enums\MediaRole;
use App\Enums\TransmissionType;
use App\Enums\VehicleCondition;
use Database\Factories\CarFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Car extends Model
{
    /** @use HasFactory<CarFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'brand_id',
        'model',
        'year',
        'mileage_km',
        'price_xaf',
        'fuel',
        'transmission',
        'color',
        'condition',
        'description',
        'status',
        'is_featured',
        'published_at',
        'sold_at',
        'views_count',
        'whatsapp_clicks_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'brand_id' => 'integer',
            'year' => 'integer',
            'mileage_km' => 'integer',
            'price_xaf' => 'integer',
            'fuel' => FuelType::class,
            'transmission' => TransmissionType::class,
            'condition' => VehicleCondition::class,
            'status' => CarStatus::class,
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'sold_at' => 'datetime',
            'views_count' => 'integer',
            'whatsapp_clicks_count' => 'integer',
        ];
    }

    /**
     * Get the brand of this car.
     *
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get all media for this car.
     *
     * @return HasMany<Media, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class)->orderBy('position');
    }

    /**
     * Get the main photo for this car.
     *
     * @return HasOne<Photo, $this>
     */
    public function mainPhoto(): HasOne
    {
        return $this->hasOne(Photo::class)->where('role', MediaRole::Main->value);
    }

    /**
     * Get all photos for this car.
     *
     * @return HasMany<Photo, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('position');
    }

    /**
     * Get all videos for this car.
     *
     * @return HasMany<Video, $this>
     */
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    /**
     * Get the interior video for this car.
     *
     * @return HasOne<Video, $this>
     */
    public function interiorVideo(): HasOne
    {
        return $this->hasOne(Video::class)->where('role', MediaRole::VideoInterior->value);
    }

    /**
     * Get the exterior video for this car.
     *
     * @return HasOne<Video, $this>
     */
    public function exteriorVideo(): HasOne
    {
        return $this->hasOne(Video::class)->where('role', MediaRole::VideoExterior->value);
    }

    /**
     * Get the events for this car.
     *
     * @return HasMany<CarEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(CarEvent::class);
    }

    /**
     * Check if the car is publishable (must have at least one main photo, CDC §3.1).
     */
    public function isPublishable(): bool
    {
        return $this->mainPhoto()->exists();
    }

    /**
     * Scope a query to only include publicly available cars.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', CarStatus::Available);
    }

    /**
     * Scope a query to only include featured cars.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
