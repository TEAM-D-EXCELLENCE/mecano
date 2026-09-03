<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Media;

use App\Enums\MediaProvider;
use App\Models\Media;
use App\Services\Media\CloudinaryImageEnhancer;
use App\Services\Media\RemoveBgImageEnhancer;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

final class RemoveBgImageEnhancerTest extends TestCase
{
    private RemoveBgImageEnhancer $enhancer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->enhancer = new RemoveBgImageEnhancer(
            inner: new CloudinaryImageEnhancer(cloudName: 'test-cloud'),
            apiKey: 'removebg-key',
            cloudName: 'test-cloud',
            cloudApiKey: '123456789',
            cloudApiSecret: 'secret_abc_123',
            folder: 'mecano/enhanced',
        );
    }

    private function media(): Media
    {
        return new Media([
            'storage_key' => 'mecano/cars/photo-1',
            'url' => 'https://res.cloudinary.com/test-cloud/image/upload/mecano/cars/photo-1',
        ]);
    }

    public function test_background_removal_uploads_the_cutout_and_returns_its_url(): void
    {
        Http::fake([
            'api.remove.bg/*' => Http::response('PNG-BINARY', 200),
            'api.cloudinary.com/*' => Http::response([
                'public_id' => 'mecano/enhanced/7-bg-removed-1700000000',
                'secure_url' => 'https://res.cloudinary.com/test-cloud/image/upload/mecano/enhanced/7-bg-removed.png',
            ], 200),
        ]);

        $result = $this->enhancer->removeBackground($this->media());

        $this->assertSame(MediaProvider::RemoveBg, $result->provider);
        $this->assertSame(
            'https://res.cloudinary.com/test-cloud/image/upload/mecano/enhanced/7-bg-removed.png',
            $result->resultUrl,
        );
        $this->assertSame('mecano/enhanced/7-bg-removed-1700000000', $result->resultKey);
        $this->assertSame(1, $result->costUnits);

        Http::assertSent(static fn ($request) => str_contains($request->url(), 'api.remove.bg')
            && $request->hasHeader('X-Api-Key', 'removebg-key'));
    }

    public function test_a_provider_failure_raises_so_the_credit_is_refunded(): void
    {
        Http::fake(['api.remove.bg/*' => Http::response('quota exceeded', 402)]);

        $this->expectException(RuntimeException::class);

        $this->enhancer->removeBackground($this->media());
    }

    public function test_an_unusable_cloudinary_response_raises_instead_of_publishing_nothing(): void
    {
        Http::fake([
            'api.remove.bg/*' => Http::response('PNG-BINARY', 200),
            'api.cloudinary.com/*' => Http::response(['error' => ['message' => 'nope']], 200),
        ]);

        $this->expectException(RuntimeException::class);

        $this->enhancer->removeBackground($this->media());
    }

    public function test_without_a_key_it_refuses_rather_than_consuming_a_credit(): void
    {
        $enhancer = new RemoveBgImageEnhancer(
            inner: new CloudinaryImageEnhancer(cloudName: 'test-cloud'),
            apiKey: '',
            cloudName: 'test-cloud',
            cloudApiKey: '123456789',
            cloudApiSecret: 'secret_abc_123',
        );

        Http::fake();

        $this->expectException(RuntimeException::class);

        $enhancer->removeBackground($this->media());
    }

    public function test_auto_improve_stays_a_cloudinary_url_transformation(): void
    {
        Http::fake();

        $result = $this->enhancer->autoImprove($this->media());

        $this->assertSame(MediaProvider::Cloudinary, $result->provider);
        Http::assertNothingSent();
    }
}
