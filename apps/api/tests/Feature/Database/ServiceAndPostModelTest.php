<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\Photo;
use App\Models\Post;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ServiceAndPostModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_factory_and_active_scope(): void
    {
        Service::factory()->active()->create(['position' => 1]);
        Service::factory()->active()->create(['position' => 2]);
        Service::factory()->inactive()->create(['position' => 3]);

        $activeServices = Service::query()->active()->get();

        $this->assertCount(2, $activeServices);
        $this->assertSame(1, $activeServices->first()->position);
    }

    public function test_post_factory_relations_and_published_scope(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $photo = Photo::factory()->create();

        $publishedPost = Post::factory()->published()->create([
            'author_id' => $user->id,
            'service_id' => $service->id,
            'cover_media_id' => $photo->id,
        ]);

        $draftPost = Post::factory()->draft()->create([
            'author_id' => $user->id,
            'service_id' => $service->id,
        ]);

        // Verify relationships
        $this->assertSame($user->id, $publishedPost->author->id);
        $this->assertSame($service->id, $publishedPost->service->id);
        $this->assertSame($photo->id, $publishedPost->coverMedia->id);
        $this->assertTrue($service->posts->contains($publishedPost));
        $this->assertTrue($service->posts->contains($draftPost));

        // Verify published scope
        $publishedPosts = Post::query()->published()->get();
        $this->assertCount(1, $publishedPosts);
        $this->assertSame($publishedPost->id, $publishedPosts->first()->id);
    }

    public function test_database_seeder_populates_services_and_posts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, Service::query()->count());
        $this->assertGreaterThan(0, Post::query()->count());
        $this->assertDatabaseHas('services', ['slug' => 'diagnostic-electronique']);
        $this->assertDatabaseHas('posts', ['slug' => '5-signes-plaquettes-frein-usees']);
    }
}
