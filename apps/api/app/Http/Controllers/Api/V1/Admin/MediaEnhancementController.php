<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Media\ApproveEnhancement;
use App\Actions\Admin\Media\RequestMediaEnhancement;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Media\RequestEnhancementRequest;
use App\Http\Resources\Admin\MediaEnhancementResource;
use App\Models\IntegrationQuota;
use App\Models\Media;
use App\Models\MediaEnhancement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class MediaEnhancementController extends Controller
{
    /**
     * List all enhancements for a given media.
     */
    public function index(int $mediaId): AnonymousResourceCollection
    {
        /** @var Media $media */
        $media = Media::query()->findOrFail($mediaId);

        $enhancements = $media->enhancements()
            ->orderByDesc('created_at')
            ->get();

        return MediaEnhancementResource::collection($enhancements);
    }

    /**
     * Request an enhancement for a media (auto_improve, smart_crop, background_removal).
     *
     * For background_removal: checks quota before consuming (transactional).
     * Returns 409 if quota is exhausted.
     */
    public function store(
        RequestEnhancementRequest $request,
        int $mediaId,
        RequestMediaEnhancement $action,
    ): JsonResponse {
        /** @var Media $media */
        $media = Media::query()->findOrFail($mediaId);

        $enhancement = $action->handle($media, $request);

        return (new MediaEnhancementResource($enhancement))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Approve an enhancement and promote its result_url to media.published_url.
     *
     * This is the ONLY moment where published_url changes (invariant #2 from pipeline doc).
     */
    public function approve(int $id, ApproveEnhancement $action): MediaEnhancementResource
    {
        /** @var MediaEnhancement $enhancement */
        $enhancement = MediaEnhancement::query()->with('media')->findOrFail($id);

        $approved = $action->handle($enhancement);

        return new MediaEnhancementResource($approved);
    }

    /**
     * Show current quota status for remove.bg.
     */
    public function quotas(): JsonResponse
    {
        $period = now()->format('Y-m');

        $quota = IntegrationQuota::query()
            ->where('provider', 'removebg')
            ->where('period', $period)
            ->first();

        // Aucune ligne le 1er du mois : le compteur doit tout de même annoncer
        // le plafond, sinon le backoffice afficherait « 0 / 0 » et désactiverait
        // le bouton alors que les crédits sont intacts.
        $limit = $quota?->limit ?? (int) config('services.removebg.monthly_limit', 50);
        $used = $quota?->used ?? 0;

        return response()->json([
            'data' => [
                'provider' => 'removebg',
                'period' => $period,
                'used' => $used,
                'limit' => $limit,
                'available' => max(0, $limit - $used),
            ],
        ]);
    }
}
