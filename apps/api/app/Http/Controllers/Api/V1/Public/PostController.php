<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PostDetailResource;
use App\Http\Resources\Public\PostListResource;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PostController extends Controller
{
    /**
     * List published posts, optionally filtered by service slug.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Post::query()
            ->published()
            ->with(['service', 'author']);

        // Filter by service slug if provided
        $serviceSlug = $request->query('service');
        if (is_string($serviceSlug) && $serviceSlug !== '') {
            $query->whereHas('service', static function ($q) use ($serviceSlug): void {
                $q->where('slug', $serviceSlug);
            });
        }

        $perPage = min(max(1, $request->integer('per_page', 10)), 50);
        $posts = $query->paginate($perPage);

        return PostListResource::collection($posts);
    }

    /**
     * Display a published post by its immutable slug.
     * Returns 404 for drafts or non-existent slugs.
     */
    public function show(string $slug): PostDetailResource
    {
        $post = Post::query()
            ->published()
            ->with(['service', 'author', 'coverMedia'])
            ->where('slug', $slug)
            ->first();

        if ($post === null) {
            abort(404);
        }

        return new PostDetailResource($post);
    }
}
