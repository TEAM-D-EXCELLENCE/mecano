<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\EnhancementStatus;
use App\Enums\EnhancementType;
use App\Enums\MediaProvider;
use App\Models\IntegrationQuota;
use App\Models\Media;
use App\Models\MediaEnhancement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class MediaEnhancementAndQuotaMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_enhancements_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('media_enhancements'));

        $this->assertTrue(Schema::hasColumns('media_enhancements', [
            'id',
            'media_id',
            'type',
            'provider',
            'status',
            'params',
            'result_key',
            'result_url',
            'error',
            'cost_units',
            'approved_at',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_integration_quotas_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('integration_quotas'));

        $this->assertTrue(Schema::hasColumns('integration_quotas', [
            'id',
            'provider',
            'period',
            'used',
            'limit',
            'updated_at',
        ]));
    }

    public function test_media_enhancement_belongs_to_media(): void
    {
        $media = Media::factory()->galleryPhoto()->create();
        $enhancement = MediaEnhancement::factory()->create([
            'media_id' => $media->id,
            'type' => EnhancementType::BackgroundRemoval,
            'provider' => MediaProvider::RemoveBg,
            'status' => EnhancementStatus::Ready,
        ]);

        $this->assertEquals($media->id, $enhancement->media->id);
        $this->assertCount(1, $media->fresh()->enhancements);
    }

    public function test_integration_quota_helpers_work_transactionally(): void
    {
        $period = now()->format('Y-m');
        $this->assertTrue(IntegrationQuota::hasAvailable('removebg', $period, 1));

        $quota = IntegrationQuota::consume('removebg', $period, 2);
        $this->assertEquals(2, $quota->used);

        IntegrationQuota::refund('removebg', $period, 1);
        $quota->refresh();
        $this->assertEquals(1, $quota->used);
    }
}
