<?php

declare(strict_types=1);

namespace App\Actions\Admin\Posts;

use App\Enums\PostStatus;
use App\Http\Requests\Admin\Posts\CreatePostRequest;
use App\Models\Post;
use App\Models\User;
use App\Support\Contracts\FrontendRevalidator;
use Illuminate\Support\Str;

final readonly class CreatePost
{
    public function __construct(
        private FrontendRevalidator $revalidator,
    ) {}

    public function handle(CreatePostRequest $request, User $author): Post
    {
        $title = (string) $request->validated('title');
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (Post::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $rawStatus = $request->validated('status');
        $status = $rawStatus ? PostStatus::from((string) $rawStatus) : PostStatus::Draft;
        $publishedAt = $status === PostStatus::Published ? now() : null;

        /** @var Post $post */
        $post = Post::query()->create([
            'slug' => $slug,
            'title' => $title,
            'excerpt' => $request->validated('excerpt'),
            'body' => (string) $request->validated('body'),
            'service_id' => $request->validated('service_id'),
            'author_id' => $author->id,
            'cover_media_id' => $request->validated('cover_media_id'),
            'status' => $status,
            'published_at' => $publishedAt,
        ]);

        $post->load(['service', 'author']);

        $this->revalidator->revalidate(["post:{$post->slug}", 'posts']);

        return $post;
    }
}
