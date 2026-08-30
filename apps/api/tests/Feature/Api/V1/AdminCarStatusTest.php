<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Brand;
use App\Models\Car;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminCarStatusTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): string
    {
        $user = User::factory()->create();

        return $user->createToken('admin')->plainTextToken;
    }

    public function test_draft_can_transition_to_available_with_main_photo(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();
        Photo::factory()->mainPhoto()->for($car)->create();

        $response = $this->withToken($this->actingAsAdmin())
            ->patchJson("/api/v1/admin/cars/{$car->id}/status", [
                'status' => 'available',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status.value', 'available');

        $car->refresh();
        $this->assertSame('available', $car->status->value);
        $this->assertNotNull($car->published_at);
    }

    public function test_published_at_is_set_only_on_first_publication(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();
        Photo::factory()->mainPhoto()->for($car)->create();

        // First publish
        $this->withToken($this->actingAsAdmin())
            ->patchJson("/api/v1/admin/cars/{$car->id}/status", ['status' => 'available']);

        $car->refresh();
        $firstPublishedAt = $car->published_at;
        $this->assertNotNull($firstPublishedAt);

        // Reserve, then re-publish
        $this->withToken($this->actingAsAdmin())
            ->patchJson("/api/v1/admin/cars/{$car->id}/status", ['status' => 'reserved']);
        $this->withToken($this->actingAsAdmin())
            ->patchJson("/api/v1/admin/cars/{$car->id}/status", ['status' => 'available']);

        $car->refresh();
        // published_at must not be overwritten
        $this->assertEquals($firstPublishedAt->toIso8601String(), $car->published_at->toIso8601String());
    }

    public function test_transition_to_available_without_main_photo_returns_409_car_not_publishable(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();
        // No main photo

        $response = $this->withToken($this->actingAsAdmin())
            ->patchJson("/api/v1/admin/cars/{$car->id}/status", [
                'status' => 'available',
            ]);

        $response->assertStatus(409)
            ->assertJson([
                'error' => [
                    'code' => 'CAR_NOT_PUBLISHABLE',
                ],
            ]);
    }

    public function test_sold_at_is_set_when_transitioning_to_sold(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->available()->for($brand)->create();

        $this->withToken($this->actingAsAdmin())
            ->patchJson("/api/v1/admin/cars/{$car->id}/status", ['status' => 'sold'])
            ->assertOk();

        $car->refresh();
        $this->assertNotNull($car->sold_at);
        $this->assertSame('sold', $car->status->value);
    }

    public function test_invalid_transition_returns_409_invalid_status_transition(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        // draft → sold is forbidden
        $response = $this->withToken($this->actingAsAdmin())
            ->patchJson("/api/v1/admin/cars/{$car->id}/status", [
                'status' => 'sold',
            ]);

        $response->assertStatus(409)
            ->assertJson([
                'error' => [
                    'code' => 'INVALID_STATUS_TRANSITION',
                ],
            ]);
    }

    public function test_draft_is_never_reachable_from_any_published_status(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->available()->for($brand)->create();

        $response = $this->withToken($this->actingAsAdmin())
            ->patchJson("/api/v1/admin/cars/{$car->id}/status", [
                'status' => 'draft',
            ]);

        $response->assertStatus(409)
            ->assertJson([
                'error' => [
                    'code' => 'INVALID_STATUS_TRANSITION',
                ],
            ]);
    }

    public function test_reserved_can_go_back_to_available(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->reserved()->for($brand)->create([
            'published_at' => now()->subDays(5),
        ]);
        Photo::factory()->mainPhoto()->for($car)->create();

        $response = $this->withToken($this->actingAsAdmin())
            ->patchJson("/api/v1/admin/cars/{$car->id}/status", [
                'status' => 'available',
            ]);

        $response->assertOk()->assertJsonPath('data.status.value', 'available');
    }
}
