<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\MediaRole;
use App\Jobs\Media\GenerateDerivatives;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Photo;
use App\Models\User;
use App\Models\Video;
use App\Services\Media\FakeImageStorage;
use App\Services\Media\FakeVideoStorage;
use App\Support\Contracts\ImageStorage;
use App\Support\Contracts\VideoStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class AdminMediaConfirmTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): string
    {
        $user = User::factory()->create();

        return $user->createToken('admin')->plainTextToken;
    }

    public function test_confirm_media_requires_authentication(): void
    {
        $this->postJson('/api/v1/admin/cars/1/media', [])
            ->assertStatus(401);
    }

    public function test_admin_can_confirm_photo_upload(): void
    {
        Queue::fake();

        /** @var FakeImageStorage $imageStorage */
        $imageStorage = app(ImageStorage::class);
        $storageKey = 'mecano/cars/1/photo_abc.jpg';
        $imageStorage->fakeObject($storageKey, 204800, 'image/jpeg', 1920, 1080);

        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $response = $this->withToken($this->actingAsAdmin())
            ->postJson("/api/v1/admin/cars/{$car->id}/media", [
                'storage_key' => $storageKey,
                'role' => 'main',
                'width' => 1920,
                'height' => 1080,
                'bytes' => 204800,
                'mime' => 'image/jpeg',
                'alt' => 'Vue avant trois-quarts',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'kind', 'role', 'storage_key', 'url', 'mime', 'bytes',
                    'width', 'height', 'confirmed_at',
                ],
            ])
            ->assertJsonPath('data.role.value', 'main')
            ->assertJsonPath('data.kind.value', 'photo');

        $this->assertDatabaseHas('media', [
            'car_id' => $car->id,
            'storage_key' => $storageKey,
            'role' => 'main',
            'kind' => 'photo',
            'provider' => 'cloudinary',
        ]);

        Queue::assertPushed(GenerateDerivatives::class);
    }

    public function test_confirming_new_main_photo_demotes_existing_main_photo_to_gallery(): void
    {
        /** @var FakeImageStorage $imageStorage */
        $imageStorage = app(ImageStorage::class);

        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $firstPhotoKey = 'mecano/cars/1/photo_1.jpg';
        $imageStorage->fakeObject($firstPhotoKey);

        $firstPhoto = Photo::factory()->mainPhoto()->for($car)->create([
            'storage_key' => $firstPhotoKey,
        ]);

        $newPhotoKey = 'mecano/cars/1/photo_2.jpg';
        $imageStorage->fakeObject($newPhotoKey);

        $response = $this->withToken($this->actingAsAdmin())
            ->postJson("/api/v1/admin/cars/{$car->id}/media", [
                'storage_key' => $newPhotoKey,
                'role' => 'main',
            ]);

        $response->assertStatus(201);

        // First photo is demoted to gallery
        $firstPhoto->refresh();
        $this->assertSame(MediaRole::Gallery, $firstPhoto->role);

        // New photo is main
        $this->assertDatabaseHas('media', [
            'car_id' => $car->id,
            'storage_key' => $newPhotoKey,
            'role' => 'main',
        ]);
    }

    public function test_admin_can_confirm_video_upload(): void
    {
        /** @var FakeVideoStorage $videoStorage */
        $videoStorage = app(VideoStorage::class);
        $storageKey = 'cars/1/videos/interior.mp4';
        $videoStorage->fakeObject($storageKey, 10485760, 'video/mp4');

        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $response = $this->withToken($this->actingAsAdmin())
            ->postJson("/api/v1/admin/cars/{$car->id}/media", [
                'storage_key' => $storageKey,
                'role' => 'video_interior',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.kind.value', 'video')
            ->assertJsonPath('data.role.value', 'video_interior');

        $this->assertDatabaseHas('media', [
            'car_id' => $car->id,
            'storage_key' => $storageKey,
            'role' => 'video_interior',
            'kind' => 'video',
            // Les vidéos sont passées sur Cloudinary comme les photos (ADR 0012).
            'provider' => 'cloudinary',
        ]);
    }

    public function test_confirming_video_with_same_role_replaces_old_video(): void
    {
        /** @var FakeVideoStorage $videoStorage */
        $videoStorage = app(VideoStorage::class);

        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $oldVideoKey = 'cars/1/videos/old_interior.mp4';
        $videoStorage->fakeObject($oldVideoKey);
        $oldVideo = Video::factory()->interiorVideo()->for($car)->create([
            'storage_key' => $oldVideoKey,
        ]);

        $newVideoKey = 'cars/1/videos/new_interior.mp4';
        $videoStorage->fakeObject($newVideoKey);

        $response = $this->withToken($this->actingAsAdmin())
            ->postJson("/api/v1/admin/cars/{$car->id}/media", [
                'storage_key' => $newVideoKey,
                'role' => 'video_interior',
            ]);

        $response->assertStatus(201);

        // Old video is deleted from DB and storage
        $this->assertDatabaseMissing('media', ['id' => $oldVideo->id]);
        $this->assertTrue($videoStorage->hasDeleted($oldVideoKey));

        // New video is present
        $this->assertDatabaseHas('media', [
            'car_id' => $car->id,
            'storage_key' => $newVideoKey,
            'role' => 'video_interior',
        ]);
    }

    public function test_confirm_rejects_non_existent_storage_key(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $response = $this->withToken($this->actingAsAdmin())
            ->postJson("/api/v1/admin/cars/{$car->id}/media", [
                'storage_key' => 'mecano/cars/unknown_photo.jpg',
                'role' => 'gallery',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => [
                    'code' => 'MEDIA_NOT_FOUND_IN_STORAGE',
                ],
            ]);
    }
}
