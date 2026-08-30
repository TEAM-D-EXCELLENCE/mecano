<?php

declare(strict_types=1);

namespace App\Jobs\Cars;

use App\Enums\CarEventType;
use App\Models\Car;
use App\Models\CarEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class AggregateCarEvents implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $retentionMonths = 12,
    ) {}

    /**
     * Execute the job:
     * 1. Aggregate views and WhatsApp clicks into cars denormalized counters.
     * 2. Purge event logs older than the retention threshold (12 months).
     *
     * @return array{cars_updated: int, events_purged: int}
     */
    public function handle(): array
    {
        // 1. Update views_count and whatsapp_clicks_count for all cars
        $viewCounts = CarEvent::query()
            ->where('type', CarEventType::View)
            ->groupBy('car_id')
            ->selectRaw('car_id, COUNT(*) as count')
            ->pluck('count', 'car_id');

        $clickCounts = CarEvent::query()
            ->where('type', CarEventType::WhatsappClick)
            ->groupBy('car_id')
            ->selectRaw('car_id, COUNT(*) as count')
            ->pluck('count', 'car_id');

        $carsUpdated = 0;

        /** @var Car $car */
        foreach (Car::query()->cursor() as $car) {
            $views = (int) ($viewCounts->get($car->id) ?? 0);
            $clicks = (int) ($clickCounts->get($car->id) ?? 0);

            if ($car->views_count !== $views || $car->whatsapp_clicks_count !== $clicks) {
                $car->update([
                    'views_count' => $views,
                    'whatsapp_clicks_count' => $clicks,
                ]);
                $carsUpdated++;
            }
        }

        // 2. Purge event logs older than retention period (default 12 months)
        $threshold = now()->subMonths($this->retentionMonths);
        $eventsPurged = CarEvent::query()
            ->where('created_at', '<', $threshold)
            ->delete();

        if ($carsUpdated > 0 || $eventsPurged > 0) {
            Log::info("AggregateCarEvents completed: {$carsUpdated} cars counters synchronized, {$eventsPurged} events purged (>{$this->retentionMonths}m).");
        }

        return [
            'cars_updated' => $carsUpdated,
            'events_purged' => $eventsPurged,
        ];
    }
}
