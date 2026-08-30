<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CarEventType;
use Database\Factories\CarEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CarEvent extends Model
{
    /** @use HasFactory<CarEventFactory> */
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'car_id',
        'type',
        'ip_hash',
        'referer',
        'created_at',
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
            'type' => CarEventType::class,
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the car associated with this event.
     *
     * @return BelongsTo<Car, $this>
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
