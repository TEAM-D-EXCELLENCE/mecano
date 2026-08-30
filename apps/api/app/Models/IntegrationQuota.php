<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class IntegrationQuota extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'provider',
        'period',
        'used',
        'limit',
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'used' => 'integer',
            'limit' => 'integer',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Check if quota is available for the given provider and period.
     */
    public static function hasAvailable(string $provider, ?string $period = null, int $units = 1): bool
    {
        $period = $period ?? now()->format('Y-m');
        $quota = self::query()->where('provider', $provider)->where('period', $period)->first();

        if ($quota === null) {
            $defaultLimit = (int) config("services.{$provider}.monthly_limit", 50);

            return $units <= $defaultLimit;
        }

        return ($quota->used + $units) <= $quota->limit;
    }

    /**
     * Increment usage within transaction.
     */
    public static function consume(string $provider, ?string $period = null, int $units = 1): self
    {
        $period = $period ?? now()->format('Y-m');
        $defaultLimit = (int) config("services.{$provider}.monthly_limit", 50);

        /** @var self $quota */
        $quota = self::query()->firstOrCreate(
            ['provider' => $provider, 'period' => $period],
            ['used' => 0, 'limit' => $defaultLimit, 'updated_at' => now()]
        );

        $quota->increment('used', $units, ['updated_at' => now()]);

        return $quota->refresh();
    }

    /**
     * Refund usage if operation fails.
     */
    public static function refund(string $provider, ?string $period = null, int $units = 1): void
    {
        $period = $period ?? now()->format('Y-m');
        $quota = self::query()->where('provider', $provider)->where('period', $period)->first();

        if ($quota !== null && $quota->used >= $units) {
            $quota->decrement('used', $units, ['updated_at' => now()]);
        }
    }
}
