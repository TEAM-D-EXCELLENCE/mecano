<?php

declare(strict_types=1);

namespace App\Actions\Admin\Posts;

use App\Enums\PostStatus;
use App\Http\Requests\Admin\Posts\UpdatePostRequest;
use App\Models\Post;

final readonly class UpdatePost
{
    public function handle(Post $post, UpdatePostRequest $request): Post
    {
        if ($request->has('title')) {
            $post->title = (string) $request->validated('title');
        }

        if ($request->has('excerpt')) {
            $post->excerpt = $request->validated('excerpt');
        }

        if ($request->has('body')) {
            $post->body = (string) $request->validated('body');
        }

        if ($request->has('service_id')) {
            $post->service_id = $request->validated('service_id');
        }

        if ($request->has('cover_media_id')) {
            $post->cover_media_id = $request->validated('cover_media_id');
        }

        if ($request->has('status')) {
            $newStatus = PostStatus::from((string) $request->validated('status'));
            $post->status = $newStatus;

            if ($newStatus === PostStatus::Published && $post->published_at === null) {
                $post->published_at = now();
            }
        }

        $post->save();
        $post->load(['service', 'author']);

        return $post;
    }
}
