<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Media\ConfirmMediaUpload;
use App\Actions\Admin\Media\DeleteMedia;
use App\Actions\Admin\Media\ReorderMedia;
use App\Actions\Admin\Media\UpdateMedia;
use App\Data\Media\ConfirmMediaUploadData;
use App\Enums\MediaRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Media\ConfirmMediaUploadRequest;
use App\Http\Requests\Admin\Media\ReorderMediaRequest;
use App\Http\Requests\Admin\Media\UpdateMediaRequest;
use App\Http\Resources\Admin\MediaResource as AdminMediaResource;
use App\Models\Car;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class MediaController extends Controller
{
    /**
     * List all media for the specified car.
     */
    public function index(int $id): AnonymousResourceCollection
    {
        /** @var Car $car */
        $car = Car::query()->findOrFail($id);

        $media = $car->media()->orderBy('position')->get();

        return AdminMediaResource::collection($media);
    }

    /**
     * Confirm a media upload and persist the media in the database.
     */
    public function store(
        ConfirmMediaUploadRequest $request,
        int $id,
        ConfirmMediaUpload $confirmMediaUpload,
    ): JsonResponse {
        $media = $confirmMediaUpload->handle(
            $id,
            ConfirmMediaUploadData::fromRequest($request)
        );

        return (new AdminMediaResource($media))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update an existing media item (e.g. promote to main photo, update alt).
     */
    public function update(
        UpdateMediaRequest $request,
        int $id,
        UpdateMedia $updateMedia,
    ): AdminMediaResource {
        /** @var Media $media */
        $media = Media::query()->findOrFail($id);

        $role = $request->has('role') ? MediaRole::from((string) $request->validated('role')) : null;
        $alt = $request->has('alt') ? (string) $request->validated('alt') : null;

        $updated = $updateMedia->handle($media, $role, $alt);

        return new AdminMediaResource($updated);
    }

    /**
     * Delete a media item from the database and external storage.
     */
    public function destroy(int $id, DeleteMedia $deleteMedia): Response
    {
        /** @var Media $media */
        $media = Media::query()->findOrFail($id);

        $deleteMedia->handle($media);

        return response()->noContent();
    }

    /**
     * Reorder media items for the specified car.
     */
    public function reorder(
        ReorderMediaRequest $request,
        int $id,
        ReorderMedia $reorderMedia,
    ): AnonymousResourceCollection {
        /** @var list<int> $mediaIds */
        $mediaIds = $request->validated('media_ids');

        $reordered = $reorderMedia->handle($id, $mediaIds);

        return AdminMediaResource::collection($reordered);
    }
}
