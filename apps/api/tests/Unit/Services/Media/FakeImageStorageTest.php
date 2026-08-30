<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Media;

use App\Data\Media\UploadConstraints;
use App\Enums\ImageTransformPreset;
use App\Services\Media\FakeImageStorage;
use Tests\TestCase;

final class FakeImageStorageTest extends TestCase
{
    private FakeImageStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = new FakeImageStorage('https://fake-cdn.example.com');
    }

    public function test_signed_upload_generates_fake_parameters(): void
    {
        $constraints = new UploadConstraints(
            maxSizeBytes: 5 * 1024 * 1024,
            allowedMimes: ['image/jpeg'],
            ttlSeconds: 300,
        );

        $signed = $this->storage->signedUploadParams('mecano/cars', $constraints);

        $this->assertSame('https://fake-cdn.example.com/upload', $signed->uploadUrl);
        $this->assertStringStartsWith('mecano/cars/', $signed->storageKey);
        $this->assertArrayHasKey('signature', $signed->fields);
    }

    public function test_derivative_url_produces_consistent_url(): void
    {
        $url = $this->storage->derivativeUrl('mecano/cars/test.jpg', ImageTransformPreset::Card);

        $this->assertSame('https://fake-cdn.example.com/transformed/card/mecano/cars/test.jpg', $url);
    }

    public function test_fake_object_lifecycle_and_deletion(): void
    {
        $key = 'mecano/cars/test.jpg';

        $this->assertNull($this->storage->exists($key));

        $this->storage->fakeObject($key, 50000, 'image/png', 800, 600);

        $meta = $this->storage->exists($key);
        $this->assertNotNull($meta);
        $this->assertSame(50000, $meta->sizeBytes);
        $this->assertSame('image/png', $meta->mimeType);
        $this->assertSame(800, $meta->width);
        $this->assertSame(600, $meta->height);

        $this->storage->delete($key);

        $this->assertNull($this->storage->exists($key));
        $this->assertTrue($this->storage->hasDeleted($key));
    }
}
