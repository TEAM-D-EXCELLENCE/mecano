<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\PostStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class ServicesAndPostsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_table_has_expected_schema(): void
    {
        $this->assertTrue(Schema::hasTable('services'));

        $this->assertTrue(Schema::hasColumns('services', [
            'id',
            'slug',
            'title',
            'excerpt',
            'description',
            'icon',
            'price_from_xaf',
            'is_active',
            'position',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_posts_table_has_expected_schema(): void
    {
        $this->assertTrue(Schema::hasTable('posts'));

        $this->assertTrue(Schema::hasColumns('posts', [
            'id',
            'slug',
            'title',
            'excerpt',
            'body',
            'cover_media_id',
            'service_id',
            'author_id',
            'status',
            'published_at',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_post_status_enum_values(): void
    {
        $this->assertSame(['draft', 'published'], PostStatus::values());
        $this->assertSame('Brouillon', PostStatus::Draft->label());
        $this->assertSame('Publié', PostStatus::Published->label());
    }
}
