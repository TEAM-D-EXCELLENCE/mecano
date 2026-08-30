<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Enums\CarEventType;
use App\Models\Car;
use App\Models\CarEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AggregateCarEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_aggregate_car_events_updates_views_and_whatsapp_clicks_counters(): void
    {
        $carA = Car::factory()->available()->create([
            'views_count' => 0,
            'whatsapp_clicks_count' => 0,
        ]);

        $carB = Car::factory()->available()->create([
            'views_count' => 0,
            'whatsapp_clicks_count' => 0,
        ]);

        // Create 3 views and 2 clicks for car A
        CarEvent::factory()->count(3)->create([
            'car_id' => $carA->id,
            'type' => CarEventType::View,
            'created_at' => now()->subDays(2),
        ]);
        CarEvent::factory()->count(2)->create([
            'car_id' => $carA->id,
            'type' => CarEventType::WhatsappClick,
            'created_at' => now()->subDays(1),
        ]);

        // Create 5 views and 0 clicks for car B
        CarEvent::factory()->count(5)->create([
            'car_id' => $carB->id,
            'type' => CarEventType::View,
            'created_at' => now()->subHours(5),
        ]);

        // Run artisan command
        $this->artisan('cars:aggregate-events')
            ->expectsOutputToContain('Aggregation completed successfully')
            ->assertSuccessful();

        $carA->refresh();
        $this->assertEquals(3, $carA->views_count);
        $this->assertEquals(2, $carA->whatsapp_clicks_count);

        $carB->refresh();
        $this->assertEquals(5, $carB->views_count);
        $this->assertEquals(0, $carB->whatsapp_clicks_count);
    }

    public function test_aggregate_car_events_purges_events_older_than_retention_period(): void
    {
        $car = Car::factory()->available()->create();

        // Recent event (3 months old) - should be kept
        $recentEvent = CarEvent::factory()->create([
            'car_id' => $car->id,
            'type' => CarEventType::View,
            'created_at' => now()->subMonths(3),
        ]);

        // Expired event (13 months old) - should be purged
        $expiredEvent = CarEvent::factory()->create([
            'car_id' => $car->id,
            'type' => CarEventType::View,
            'created_at' => now()->subMonths(13),
        ]);

        $this->artisan('cars:aggregate-events --retention-months=12')
            ->assertSuccessful();

        $this->assertDatabaseHas('car_events', ['id' => $recentEvent->id]);
        $this->assertDatabaseMissing('car_events', ['id' => $expiredEvent->id]);
    }
}
