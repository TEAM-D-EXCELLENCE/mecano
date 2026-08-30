<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Brand;
use App\Models\Car;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminMediaSignatureTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): string
    {
        $user = User::factory()->create();

        return $user->createToken('admin')->plainTextToken;
    }

    public function test_upload_signature_requires_authentication(): void
    {
        $this->postJson('/api/v1/admin/media/upload-signature', [])
            ->assertStatus(401);
    }

    public function test_admin_can_generate_photo_upload_signature(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $response = $this->withToken($this->actingAsAdmin())
            ->postJson('/api/v1/admin/media/upload-signature', [
                'car_id' => $car->id,
                'kind' => 'photo',
                'mime' => 'image/jpeg',
                'bytes' => 5 * 1024 * 1024, // 5 MB
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'upload_url',
                    'fields',
                    'storage_key',
                    'expires_at',
                ],
            ]);

        $this->assertStringContainsString("{$car->id}", $response->json('data.storage_key'));
    }

    public function test_admin_can_generate_video_upload_signature(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $response = $this->withToken($this->actingAsAdmin())
            ->postJson('/api/v1/admin/media/upload-signature', [
                'car_id' => $car->id,
                'kind' => 'video',
                'mime' => 'video/mp4',
                'bytes' => 50 * 1024 * 1024, // 50 MB
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'upload_url',
                    'fields',
                    'storage_key',
                    'expires_at',
                ],
            ]);

        $this->assertSame('PUT', $response->json('data.fields.method'));
    }

    public function test_photo_upload_rejects_unsupported_mime_type(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $response = $this->withToken($this->actingAsAdmin())
            ->postJson('/api/v1/admin/media/upload-signature', [
                'car_id' => $car->id,
                'kind' => 'photo',
                'mime' => 'application/pdf',
                'bytes' => 1024,
            ]);

        $response->assertStatus(422)
            ->assertJson(['error' => ['code' => 'VALIDATION_FAILED']]);
    }

    public function test_photo_upload_rejects_size_exceeding_15mb(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $response = $this->withToken($this->actingAsAdmin())
            ->postJson('/api/v1/admin/media/upload-signature', [
                'car_id' => $car->id,
                'kind' => 'photo',
                'mime' => 'image/jpeg',
                'bytes' => 16 * 1024 * 1024, // 16 MB > 15 MB
            ]);

        $response->assertStatus(422)
            ->assertJson(['error' => ['code' => 'VALIDATION_FAILED']]);
    }

    public function test_video_upload_rejects_size_exceeding_200mb(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $response = $this->withToken($this->actingAsAdmin())
            ->postJson('/api/v1/admin/media/upload-signature', [
                'car_id' => $car->id,
                'kind' => 'video',
                'mime' => 'video/mp4',
                'bytes' => 201 * 1024 * 1024, // 201 MB > 200 MB
            ]);

        $response->assertStatus(422)
            ->assertJson(['error' => ['code' => 'VALIDATION_FAILED']]);
    }

    public function test_video_upload_signature_fails_when_car_already_has_2_videos(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        // Create 2 existing videos for this car
        Video::factory()->interiorVideo()->for($car)->create();
        Video::factory()->exteriorVideo()->for($car)->create();

        $response = $this->withToken($this->actingAsAdmin())
            ->postJson('/api/v1/admin/media/upload-signature', [
                'car_id' => $car->id,
                'kind' => 'video',
                'mime' => 'video/mp4',
                'bytes' => 10 * 1024 * 1024,
            ]);

        $response->assertStatus(409)
            ->assertJson([
                'error' => [
                    'code' => 'VIDEO_LIMIT_EXCEEDED',
                ],
            ]);
    }
}
