<?php

declare(strict_types=1);

namespace App\Actions\Cars;

use App\Data\RecordCarEventData;
use App\Enums\CarEventType;
use App\Models\Car;
use App\Models\CarEvent;

final readonly class RecordCarEvent
{
    /**
     * Record a public analytics event and increment denormalized car counters.
     */
    public function handle(Car $car, RecordCarEventData $data): CarEvent
    {
        /** @var CarEvent $event */
        $event = $car->events()->create([
            'type' => $data->type,
            'ip_hash' => $data->ipHash,
            'referer' => $data->referer,
            'created_at' => now(),
        ]);

        // Increment denormalized counter
        match ($data->type) {
            CarEventType::View => $car->increment('views_count'),
            CarEventType::WhatsappClick => $car->increment('whatsapp_clicks_count'),
        };

        return $event;
    }
}
