<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Brand;
use App\Models\Car;
use App\Models\Photo;
use App\Models\Video;
use App\Services\Media\FakeImageStorage;
use App\Services\Media\FakeVideoStorage;
use App\Support\Contracts\ImageStorage;
use App\Support\Contracts\VideoStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PurgeOrphanUploadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_purge_removes_unconfirmed_media_older_than_24_hours(): void
    {
        /** @var FakeImageStorage $imageStorage */
        $imageStorage = app(ImageStorage::class);
        /** @var FakeVideoStorage $videoStorage */
        $videoStorage = app(VideoStorage::class);

        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        // 1. Old orphan photo (> 24h, unconfirmed)
        $oldPhotoKey = 'mecano/cars/1/old_orphan.jpg';
        $imageStorage->fakeObject($oldPhotoKey);
        $oldOrphanPhoto = Photo::factory()->for($car)->create([
            'storage_key' => $oldPhotoKey,
            'confirmed_at' => null,
            'created_at' => now()->subHours(25),
        ]);

        // 2. Old orphan video (> 24h, unconfirmed)
        $oldVideoKey = 'cars/1/videos/old_orphan.mp4';
        $videoStorage->fakeObject($oldVideoKey);
        $oldOrphanVideo = Video::factory()->for($car)->create([
            'storage_key' => $oldVideoKey,
            'confirmed_at' => null,
            'created_at' => now()->subHours(30),
        ]);

        // 3. Recent unconfirmed photo (< 24h)
        $recentPhotoKey = 'mecano/cars/1/recent.jpg';
        $imageStorage->fakeObject($recentPhotoKey);
        $recentPhoto = Photo::factory()->for($car)->create([
            'storage_key' => $recentPhotoKey,
            'confirmed_at' => null,
            'created_at' => now()->subHours(2),
        ]);

        // 4. Old confirmed photo (> 24h)
        $confirmedPhotoKey = 'mecano/cars/1/confirmed.jpg';
        $imageStorage->fakeObject($confirmedPhotoKey);
        $confirmedPhoto = Photo::factory()->for($car)->create([
            'storage_key' => $confirmedPhotoKey,
            'confirmed_at' => now()->subHours(25),
            'created_at' => now()->subHours(26),
        ]);

        $this->artisan('media:purge-orphans')
            ->expectsOutput('Done. 2 orphan media upload(s) purged.')
            ->assertSuccessful();

        // Orphans were deleted
        $this->assertDatabaseMissing('media', ['id' => $oldOrphanPhoto->id]);
        $this->assertDatabaseMissing('media', ['id' => $oldOrphanVideo->id]);
        $this->assertTrue($imageStorage->hasDeleted($oldPhotoKey));
        $this->assertTrue($videoStorage->hasDeleted($oldVideoKey));

        // Non-orphans are kept intact
        $this->assertDatabaseHas('media', ['id' => $recentPhoto->id]);
        $this->assertDatabaseHas('media', ['id' => $confirmedPhoto->id]);
        $this->assertFalse($imageStorage->hasDeleted($recentPhotoKey));
        $this->assertFalse($imageStorage->hasDeleted($confirmedPhotoKey));
    }

    public function test_purge_respects_custom_hours_option(): void
    {
        /** @var FakeImageStorage $imageStorage */
        $imageStorage = app(ImageStorage::class);

        $brand = Brand::factory()->create();
        $car = Car::factory()->draft()->for($brand)->create();

        $photoKey = 'mecano/cars/1/photo_6h.jpg';
        $imageStorage->fakeObject($photoKey);
        $photo = Photo::factory()->for($car)->create([
            'storage_key' => $photoKey,
            'confirmed_at' => null,
            'created_at' => now()->subHours(7),
        ]);

        $this->artisan('media:purge-orphans', ['--hours' => 6])
            ->expectsOutput('Done. 1 orphan media upload(s) purged.')
            ->assertSuccessful();

        $this->assertDatabaseMissing('media', ['id' => $photo->id]);
        $this->assertTrue($imageStorage->hasDeleted($photoKey));
    }
}
