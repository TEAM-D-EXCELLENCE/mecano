<?php

declare(strict_types=1);

namespace App\Actions\Admin\Media;

use App\Enums\EnhancementStatus;
use App\Enums\EnhancementType;
use App\Enums\MediaKind;
use App\Enums\MediaProvider;
use App\Exceptions\MediaNotEnhanceableException;
use App\Exceptions\QuotaExceededException;
use App\Http\Requests\Admin\Media\RequestEnhancementRequest;
use App\Models\IntegrationQuota;
use App\Models\Media;
use App\Models\MediaEnhancement;
use App\Support\Contracts\ImageEnhancer;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class RequestMediaEnhancement
{
    public function __construct(
        private ImageEnhancer $enhancer,
    ) {}

    public function handle(Media $media, RequestEnhancementRequest $request): MediaEnhancement
    {
        if ($media->kind !== MediaKind::Photo) {
            throw new MediaNotEnhanceableException;
        }

        $type = EnhancementType::from((string) $request->validated('type'));
        $provider = match ($type) {
            EnhancementType::BackgroundRemoval => MediaProvider::RemoveBg,
            EnhancementType::AutoImprove, EnhancementType::SmartCrop => MediaProvider::Cloudinary,
        };

        if ($type === EnhancementType::BackgroundRemoval) {
            return $this->handleBackgroundRemoval($media, $type, $provider);
        }

        return $this->handleCloudinaryEnhancement($media, $type, $provider);
    }

    private function handleBackgroundRemoval(Media $media, EnhancementType $type, MediaProvider $provider): MediaEnhancement
    {
        $period = now()->format('Y-m');

        /** @var MediaEnhancement $enhancement */
        $enhancement = DB::transaction(function () use ($media, $type, $provider, $period) {
            // Le contrôle et la consommation vivent dans la même transaction, sur
            // une ligne verrouillée : sans cela deux requêtes simultanées passent
            // toutes deux le contrôle et le quota est franchi.
            IntegrationQuota::consumeOrFail('removebg', $period, 1);

            return MediaEnhancement::query()->create([
                'media_id' => $media->id,
                'type' => $type,
                'provider' => $provider,
                'status' => EnhancementStatus::Processing,
                'cost_units' => 1,
            ]);
        });

        try {
            $result = $this->enhancer->removeBackground($media);

            $enhancement->update([
                'status' => EnhancementStatus::Ready,
                'result_key' => $result->resultKey,
                'result_url' => $result->resultUrl,
                'params' => $result->params,
            ]);
        } catch (Throwable $e) {
            IntegrationQuota::refund('removebg', $period, 1);

            $enhancement->update([
                'status' => EnhancementStatus::Failed,
                'error' => $e->getMessage(),
            ]);
        }

        return $enhancement->refresh();
    }

    private function handleCloudinaryEnhancement(Media $media, EnhancementType $type, MediaProvider $provider): MediaEnhancement
    {
        /** @var MediaEnhancement $enhancement */
        $enhancement = MediaEnhancement::query()->create([
            'media_id' => $media->id,
            'type' => $type,
            'provider' => $provider,
            'status' => EnhancementStatus::Processing,
            'cost_units' => 0,
        ]);

        try {
            $result = match ($type) {
                EnhancementType::AutoImprove => $this->enhancer->autoImprove($media),
                EnhancementType::SmartCrop => $this->enhancer->smartCrop($media),
                default => throw new MediaNotEnhanceableException('Type d\'amélioration non supporté.'),
            };

            $enhancement->update([
                'status' => EnhancementStatus::Ready,
                'result_key' => $result->resultKey,
                'result_url' => $result->resultUrl,
                'params' => $result->params,
            ]);
        } catch (Throwable $e) {
            $enhancement->update([
                'status' => EnhancementStatus::Failed,
                'error' => $e->getMessage(),
            ]);
        }

        return $enhancement->refresh();
    }
}
