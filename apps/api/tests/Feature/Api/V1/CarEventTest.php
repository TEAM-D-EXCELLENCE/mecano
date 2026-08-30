<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Brand;
use App\Models\Car;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CarEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_view_event_stores_salted_ip_hash_and_increments_views_count(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->available()->for($brand)->create([
            'slug' => 'toyota-corolla-2020-1',
            'views_count' => 10,
        ]);

        $response = $this->postJson('/api/v1/cars/toyota-corolla-2020-1/events', [
            'type' => 'view',
            'referer' => 'https://www.google.com/search?q=toyota+corolla+occasion',
        ], [
            'REMOTE_ADDR' => '192.168.1.50',
        ]);

        $response->assertNoContent();

        // Check car event record
        $this->assertDatabaseCount('car_events', 1);
        $this->assertDatabaseHas('car_events', [
            'car_id' => $car->id,
            'type' => 'view',
            'referer' => 'www.google.com', // Clean domain only
        ]);

        // Verify IP was hashed with salt and NOT stored in clear text
        $event = $car->events()->first();
        $this->assertNotNull($event);
        $this->assertNotSame('192.168.1.50', $event->ip_hash);
        $this->assertSame(64, strlen((string) $event->ip_hash));

        // Verify denormalized counter
        $car->refresh();
        $this->assertSame(11, $car->views_count);
    }

    public function test_record_whatsapp_click_event_increments_whatsapp_clicks_count(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->available()->for($brand)->create([
            'slug' => 'mercedes-benz-c200-2019-2',
            'whatsapp_clicks_count' => 5,
        ]);

        $response = $this->postJson('/api/v1/cars/mercedes-benz-c200-2019-2/events', [
            'type' => 'whatsapp_click',
        ]);

        $response->assertNoContent();

        $car->refresh();
        $this->assertSame(6, $car->whatsapp_clicks_count);
    }

    public function test_record_event_fails_for_invalid_type(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->available()->for($brand)->create([
            'slug' => 'toyota-yaris-2020-3',
        ]);

        $response = $this->postJson('/api/v1/cars/toyota-yaris-2020-3/events', [
            'type' => 'malicious_event_type',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => [
                    'code' => 'VALIDATION_FAILED',
                ],
            ]);
    }

    public function test_record_event_returns_404_for_draft_or_non_existent_car(): void
    {
        $brand = Brand::factory()->create();
        Car::factory()->draft()->for($brand)->create([
            'slug' => 'secret-draft-car',
        ]);

        // Draft car
        $responseDraft = $this->postJson('/api/v1/cars/secret-draft-car/events', [
            'type' => 'view',
        ]);

        $responseDraft->assertStatus(404)
            ->assertJson([
                'error' => [
                    'code' => 'NOT_FOUND',
                ],
            ]);

        // Non-existent car
        $responseNonExistent = $this->postJson('/api/v1/cars/unknown-car-slug/events', [
            'type' => 'view',
        ]);

        $responseNonExistent->assertStatus(404)
            ->assertJson([
                'error' => [
                    'code' => 'NOT_FOUND',
                ],
            ]);
    }
}
