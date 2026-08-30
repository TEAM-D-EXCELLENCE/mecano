<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Posts\CreatePost;
use App\Actions\Admin\Posts\UpdatePost;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Posts\CreatePostRequest;
use App\Http\Requests\Admin\Posts\UpdatePostRequest;
use App\Http\Resources\Admin\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PostController extends Controller
{
    /**
     * List all posts with optional status and service filtering.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Post::query()->with(['service', 'author']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->integer('service_id'));
        }

        $perPage = min(max(1, $request->integer('per_page', 15)), 50);
        $posts = $query->latest('id')->paginate($perPage);

        return PostResource::collection($posts);
    }

    /**
     * Store a newly created post.
     */
    public function store(CreatePostRequest $request, CreatePost $createPost): JsonResponse
    {
        /** @var User $author */
        $author = $request->user();

        $post = $createPost->handle($request, $author);

        return (new PostResource($post))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified post for editing.
     */
    public function show(int $id): PostResource
    {
        /** @var Post $post */
        $post = Post::query()->with(['service', 'author'])->findOrFail($id);

        return new PostResource($post);
    }

    /**
     * Update the specified post.
     */
    public function update(UpdatePostRequest $request, int $id, UpdatePost $updatePost): PostResource
    {
        /** @var Post $post */
        $post = Post::query()->findOrFail($id);

        $updatedPost = $updatePost->handle($post, $request);

        return new PostResource($updatedPost);
    }
}
