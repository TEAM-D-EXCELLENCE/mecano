<?php

declare(strict_types=1);

namespace Tests\Feature\Integration;

use App\Enums\CarStatus;
use App\Models\Car;
use App\Models\IntegrationQuota;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Invariants que l'API laissait franchir.
 */
final class MediaInvariantsRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function token(): string
    {
        return User::factory()->create()->createToken('admin')->plainTextToken;
    }

    public function test_last_photo_of_a_published_car_cannot_be_deleted(): void
    {
        $car = Car::factory()->create(['status' => CarStatus::Available]);
        $photo = Media::factory()->mainPhoto()->create(['car_id' => $car->id]);

        $response = $this->withToken($this->token())->deleteJson("/api/v1/admin/media/{$photo->id}");

        $response->assertStatus(409)
            ->assertJsonPath('error.code', 'LAST_PHOTO_OF_PUBLISHED_CAR');

        $this->assertDatabaseHas('media', ['id' => $photo->id]);
        $this->assertTrue($car->fresh()->isPublishable());
    }

    public function test_last_photo_of_a_draft_can_be_deleted(): void
    {
        $car = Car::factory()->create(['status' => CarStatus::Draft]);
        $photo = Media::factory()->mainPhoto()->create(['car_id' => $car->id]);

        $this->withToken($this->token())
            ->deleteJson("/api/v1/admin/media/{$photo->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('media', ['id' => $photo->id]);
    }

    public function test_a_published_car_keeps_a_main_photo_when_another_remains(): void
    {
        $car = Car::factory()->create(['status' => CarStatus::Available]);
        $main = Media::factory()->mainPhoto()->create(['car_id' => $car->id]);
        Media::factory()->galleryPhoto()->create(['car_id' => $car->id]);

        $this->withToken($this->token())
            ->deleteJson("/api/v1/admin/media/{$main->id}")
            ->assertNoContent();

        $this->assertTrue($car->fresh()->isPublishable());
    }

    public function test_quota_refusal_carries_the_documented_error_envelope(): void
    {
        $period = now()->format('Y-m');
        IntegrationQuota::query()->create([
            'provider' => 'removebg',
            'period' => $period,
            'used' => 50,
            'limit' => 50,
            'updated_at' => now(),
        ]);

        $media = Media::factory()->galleryPhoto()->create();

        $response = $this->withToken($this->token())
            ->postJson("/api/v1/admin/media/{$media->id}/enhance", ['type' => 'background_removal']);

        // Le front compare sur `code`, jamais sur `message` : le code doit être
        // celui du contrat, et le statut 409 doit survivre à `APP_DEBUG=false`.
        $response->assertStatus(409)
            ->assertJsonPath('error.code', 'QUOTA_EXCEEDED')
            ->assertJsonPath('error.details.provider', 'removebg')
            ->assertJsonPath('error.details.used', 50)
            ->assertJsonPath('error.details.limit', 50)
            ->assertJsonStructure(['error' => ['code', 'message', 'details' => ['resets_at']]]);

        $this->assertSame(50, IntegrationQuota::query()
            ->where('provider', 'removebg')->where('period', $period)->value('used'));
    }

    public function test_enhancing_a_video_is_refused_with_a_stable_code(): void
    {
        $media = Media::factory()->interiorVideo()->create();

        $this->withToken($this->token())
            ->postJson("/api/v1/admin/media/{$media->id}/enhance", ['type' => 'auto_improve'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'MEDIA_NOT_ENHANCEABLE');
    }
}
