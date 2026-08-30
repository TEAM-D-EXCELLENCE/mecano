<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\MediaRole;
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
use Tests\TestCase;

final class AdminMediaManagementTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): string
    {
        $user = User::factory()->create();

        return $user->createToken('admin')->plainTextToken;
    }

    public function test_media_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/cars/1/media')->assertStatus(401);
        $this->postJson('/api/v1/admin/cars/1/media/reorder', [])->assertStatus(401);
        $this->patchJson('/api/v1/admin/media/1', [])->assertStatus(401);
        $this->deleteJson('/api/v1/admin/media/1')->assertStatus(401);
    }

    public function test_admin_can_list_media_ordered_by_position(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $photo2 = Photo::factory()->galleryPhoto()->for($car)->create(['position' => 2]);
        $photo1 = Photo::factory()->mainPhoto()->for($car)->create(['position' => 1]);
        $photo3 = Photo::factory()->galleryPhoto()->for($car)->create(['position' => 3]);

        $response = $this->withToken($this->actingAsAdmin())
            ->getJson("/api/v1/admin/cars/{$car->id}/media");

        $response->assertOk()->assertJsonCount(3, 'data');
        $this->assertSame($photo1->id, $response->json('data.0.id'));
        $this->assertSame($photo2->id, $response->json('data.1.id'));
        $this->assertSame($photo3->id, $response->json('data.2.id'));
    }

    public function test_admin_can_reorder_media_sequence(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $p1 = Photo::factory()->mainPhoto()->for($car)->create(['position' => 1]);
        $p2 = Photo::factory()->galleryPhoto()->for($car)->create(['position' => 2]);
        $p3 = Photo::factory()->galleryPhoto()->for($car)->create(['position' => 3]);

        $response = $this->withToken($this->actingAsAdmin())
            ->postJson("/api/v1/admin/cars/{$car->id}/media/reorder", [
                'media_ids' => [$p3->id, $p1->id, $p2->id],
            ]);

        $response->assertOk();
        $this->assertSame($p3->id, $response->json('data.0.id'));
        $this->assertSame($p1->id, $response->json('data.1.id'));
        $this->assertSame($p2->id, $response->json('data.2.id'));

        $this->assertSame(1, $p3->fresh()->position);
        $this->assertSame(2, $p1->fresh()->position);
        $this->assertSame(3, $p2->fresh()->position);
    }

    public function test_admin_can_update_media_and_promote_to_main_photo(): void
    {
        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $mainPhoto = Photo::factory()->mainPhoto()->for($car)->create();
        $galleryPhoto = Photo::factory()->galleryPhoto()->for($car)->create();

        $response = $this->withToken($this->actingAsAdmin())
            ->patchJson("/api/v1/admin/media/{$galleryPhoto->id}", [
                'role' => 'main',
                'alt' => 'Nouveau titre alt',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.role.value', 'main')
            ->assertJsonPath('data.alt', 'Nouveau titre alt');

        // Former main photo was demoted to gallery
        $mainPhoto->refresh();
        $this->assertSame(MediaRole::Gallery, $mainPhoto->role);

        // Target photo is now main
        $galleryPhoto->refresh();
        $this->assertSame(MediaRole::Main, $galleryPhoto->role);
    }

    public function test_admin_can_delete_media_and_deletes_from_storage(): void
    {
        /** @var FakeImageStorage $imageStorage */
        $imageStorage = app(ImageStorage::class);

        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $key = 'mecano/cars/1/photo_to_delete.jpg';
        $imageStorage->fakeObject($key);
        $photo = Photo::factory()->galleryPhoto()->for($car)->create(['storage_key' => $key]);

        $response = $this->withToken($this->actingAsAdmin())
            ->deleteJson("/api/v1/admin/media/{$photo->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('media', ['id' => $photo->id]);
        $this->assertTrue($imageStorage->hasDeleted($key));
    }

    public function test_deleting_main_photo_promotes_next_gallery_photo(): void
    {
        /** @var FakeImageStorage $imageStorage */
        $imageStorage = app(ImageStorage::class);

        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $mainKey = 'mecano/cars/1/main.jpg';
        $imageStorage->fakeObject($mainKey);
        $mainPhoto = Photo::factory()->mainPhoto()->for($car)->create([
            'storage_key' => $mainKey,
            'position' => 1,
        ]);

        $galleryKey = 'mecano/cars/1/gallery1.jpg';
        $imageStorage->fakeObject($galleryKey);
        $galleryPhoto = Photo::factory()->galleryPhoto()->for($car)->create([
            'storage_key' => $galleryKey,
            'position' => 2,
        ]);

        $this->withToken($this->actingAsAdmin())
            ->deleteJson("/api/v1/admin/media/{$mainPhoto->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('media', ['id' => $mainPhoto->id]);

        // The gallery photo is now promoted to main photo
        $galleryPhoto->refresh();
        $this->assertSame(MediaRole::Main, $galleryPhoto->role);
    }

    public function test_deleting_video_deletes_from_video_storage(): void
    {
        /** @var FakeVideoStorage $videoStorage */
        $videoStorage = app(VideoStorage::class);

        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $videoKey = 'cars/1/videos/interior.mp4';
        $videoStorage->fakeObject($videoKey);
        $video = Video::factory()->interiorVideo()->for($car)->create(['storage_key' => $videoKey]);

        $this->withToken($this->actingAsAdmin())
            ->deleteJson("/api/v1/admin/media/{$video->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('media', ['id' => $video->id]);
        $this->assertTrue($videoStorage->hasDeleted($videoKey));
    }
}
