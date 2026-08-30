<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_posts_returns_only_published_posts(): void
    {
        $author = User::factory()->create();
        $service = Service::factory()->create();

        Post::factory()->published()->create([
            'title' => 'Article publié',
            'author_id' => $author->id,
            'service_id' => $service->id,
        ]);

        Post::factory()->draft()->create([
            'title' => 'Article brouillon',
            'author_id' => $author->id,
            'service_id' => $service->id,
        ]);

        $response = $this->getJson('/api/v1/posts');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Article publié')
            ->assertJsonPath('data.0.service.slug', $service->slug)
            ->assertJsonPath('data.0.author.name', $author->name);
    }

    public function test_public_posts_can_filter_by_service_slug(): void
    {
        $serviceA = Service::factory()->create(['slug' => 'mecanique']);
        $serviceB = Service::factory()->create(['slug' => 'carrosserie']);

        Post::factory()->published()->create(['service_id' => $serviceA->id, 'title' => 'Post A']);
        Post::factory()->published()->create(['service_id' => $serviceB->id, 'title' => 'Post B']);

        $response = $this->getJson('/api/v1/posts?service=mecanique');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Post A');
    }

    public function test_public_post_detail_returns_published_post_or_404(): void
    {
        $published = Post::factory()->published()->create(['slug' => 'guide-vidange']);
        $draft = Post::factory()->draft()->create(['slug' => 'guide-freinage']);

        $responsePublished = $this->getJson('/api/v1/posts/guide-vidange');
        $responsePublished->assertOk()
            ->assertJsonPath('data.slug', 'guide-vidange')
            ->assertJsonPath('data.body', $published->body);

        $responseDraft = $this->getJson('/api/v1/posts/guide-freinage');
        $responseDraft->assertNotFound();

        $responseNotFound = $this->getJson('/api/v1/posts/inexistant');
        $responseNotFound->assertNotFound();
    }

    public function test_admin_posts_endpoints_require_auth(): void
    {
        $this->getJson('/api/v1/admin/posts')->assertStatus(401);
        $this->postJson('/api/v1/admin/posts', [])->assertStatus(401);
        $this->getJson('/api/v1/admin/posts/1')->assertStatus(401);
        $this->patchJson('/api/v1/admin/posts/1', [])->assertStatus(401);
    }

    public function test_admin_can_list_all_posts_with_filters(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        Post::factory()->published()->create(['title' => 'Publié']);
        Post::factory()->draft()->create(['title' => 'Brouillon']);

        $responseAll = $this->withToken($token)->getJson('/api/v1/admin/posts');
        $responseAll->assertOk()->assertJsonCount(2, 'data');

        $responseDraft = $this->withToken($token)->getJson('/api/v1/admin/posts?status=draft');
        $responseDraft->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Brouillon');
    }

    public function test_admin_can_create_post(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;
        $service = Service::factory()->create();

        $response = $this->withToken($token)->postJson('/api/v1/admin/posts', [
            'title' => 'Comment entretenir sa boîte automatique ?',
            'excerpt' => 'Conseils pratiques et périodicité.',
            'body' => 'Contenu complet et conseils détaillés sans balises html.',
            'service_id' => $service->id,
            'status' => 'draft',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.slug', 'comment-entretenir-sa-boite-automatique')
            ->assertJsonPath('data.status.value', 'draft')
            ->assertJsonPath('data.author.id', $user->id)
            ->assertJsonPath('data.service.id', $service->id);

        $this->assertDatabaseHas('posts', [
            'slug' => 'comment-entretenir-sa-boite-automatique',
            'author_id' => $user->id,
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function test_admin_creating_published_post_sets_published_at(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/admin/posts', [
            'title' => 'Article directement en ligne',
            'body' => 'Corps de texte...',
            'status' => 'published',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status.value', 'published');

        $post = Post::query()->where('title', 'Article directement en ligne')->firstOrFail();
        $this->assertNotNull($post->published_at);
    }

    public function test_admin_can_publish_draft_post(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;

        $post = Post::factory()->draft()->create(['published_at' => null]);

        $response = $this->withToken($token)->patchJson("/api/v1/admin/posts/{$post->id}", [
            'status' => 'published',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status.value', 'published');

        $post->refresh();
        $this->assertEquals(PostStatus::Published, $post->status);
        $this->assertNotNull($post->published_at);
    }
}
