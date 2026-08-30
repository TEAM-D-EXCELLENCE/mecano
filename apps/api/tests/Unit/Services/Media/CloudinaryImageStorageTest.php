<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Media;

use App\Data\Media\UploadConstraints;
use App\Enums\ImageTransformPreset;
use App\Services\Media\CloudinaryImageStorage;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class CloudinaryImageStorageTest extends TestCase
{
    private CloudinaryImageStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = new CloudinaryImageStorage(
            cloudName: 'test-cloud',
            apiKey: '123456789',
            apiSecret: 'secret_abc_123',
            secure: true,
        );
    }

    public function test_signed_upload_params_generates_valid_signature_and_fields(): void
    {
        $constraints = new UploadConstraints(
            maxSizeBytes: 10 * 1024 * 1024,
            allowedMimes: ['image/jpeg', 'image/png'],
            ttlSeconds: 600,
        );

        $signed = $this->storage->signedUploadParams('mecano/cars', $constraints);

        $this->assertSame('https://api.cloudinary.com/v1_1/test-cloud/image/upload', $signed->uploadUrl);
        $this->assertStringStartsWith('mecano/cars/', $signed->storageKey);

        $fields = $signed->fields;
        $this->assertSame('123456789', $fields['api_key']);
        $this->assertSame('mecano/cars', $fields['folder']);
        $this->assertSame($signed->storageKey, $fields['public_id']);
        $this->assertNotEmpty($fields['signature']);
        $this->assertIsInt($fields['timestamp']);
    }

    public function test_derivative_url_generates_correct_cdn_urls(): void
    {
        $key = 'mecano/cars/photo_123';

        // Thumb preset
        $thumbUrl = $this->storage->derivativeUrl($key, ImageTransformPreset::Thumb);
        $this->assertStringContainsString('https://res.cloudinary.com/test-cloud/image/upload/', $thumbUrl);
        $this->assertStringContainsString('w_200,h_150', $thumbUrl);
        $this->assertStringEndsWith('mecano/cars/photo_123', $thumbUrl);

        // Card preset
        $cardUrl = $this->storage->derivativeUrl($key, ImageTransformPreset::Card);
        $this->assertStringContainsString('w_640,h_480', $cardUrl);

        // Detail preset
        $detailUrl = $this->storage->derivativeUrl($key, ImageTransformPreset::Detail);
        $this->assertStringContainsString('w_1280,h_960', $detailUrl);

        // Raw original (empty preset)
        $rawUrl = $this->storage->derivativeUrl($key, '');
        $this->assertSame('https://res.cloudinary.com/test-cloud/image/upload/mecano/cars/photo_123', $rawUrl);
    }

    public function test_exists_returns_object_meta_when_found(): void
    {
        Http::fake([
            'https://api.cloudinary.com/v1_1/test-cloud/resources/image/upload/*' => Http::response([
                'bytes' => 204800,
                'format' => 'webp',
                'width' => 1920,
                'height' => 1080,
                'secure_url' => 'https://res.cloudinary.com/test-cloud/image/upload/photo.webp',
            ], 200),
        ]);

        $meta = $this->storage->exists('mecano/cars/photo_123');

        $this->assertNotNull($meta);
        $this->assertSame('mecano/cars/photo_123', $meta->key);
        $this->assertSame(204800, $meta->sizeBytes);
        $this->assertSame('image/webp', $meta->mimeType);
        $this->assertSame(1920, $meta->width);
        $this->assertSame(1080, $meta->height);
    }

    public function test_exists_returns_null_when_not_found(): void
    {
        Http::fake([
            'https://api.cloudinary.com/v1_1/test-cloud/resources/image/upload/*' => Http::response([
                'error' => ['message' => 'Resource not found'],
            ], 404),
        ]);

        $meta = $this->storage->exists('mecano/cars/unknown_photo');

        $this->assertNull($meta);
    }

    public function test_delete_sends_destroy_request(): void
    {
        Http::fake([
            'https://api.cloudinary.com/v1_1/test-cloud/image/destroy' => Http::response([
                'result' => 'ok',
            ], 200),
        ]);

        $this->storage->delete('mecano/cars/photo_123');

        Http::assertSent(static function ($request): bool {
            return $request->url() === 'https://api.cloudinary.com/v1_1/test-cloud/image/destroy'
                && $request['public_id'] === 'mecano/cars/photo_123';
        });
    }
}
