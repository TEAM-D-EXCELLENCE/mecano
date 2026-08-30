<?php

declare(strict_types=1);

namespace App\Actions\Admin\Media;

use App\Enums\EnhancementStatus;
use App\Models\Media;
use App\Models\MediaEnhancement;
use App\Support\Contracts\FrontendRevalidator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final readonly class ApproveEnhancement
{
    public function __construct(
        private FrontendRevalidator $revalidator,
    ) {}

    /**
     * Approve an enhancement and set media.published_url.
     *
     * This is the ONLY point where published_url changes (pipeline invariant #2).
     */
    public function handle(MediaEnhancement $enhancement): MediaEnhancement
    {
        if ($enhancement->status !== EnhancementStatus::Ready) {
            throw new UnprocessableEntityHttpException(
                'Seul un dérivé prêt (ready) peut être approuvé.'
            );
        }

        if ($enhancement->result_url === null) {
            throw new ConflictHttpException('L\'URL du dérivé est manquante, impossible d\'approuver.');
        }

        DB::transaction(function () use ($enhancement): void {
            $enhancement->update([
                'status' => EnhancementStatus::Approved,
                'approved_at' => now(),
            ]);

            /** @var Media $media */
            $media = $enhancement->media;
            $media->update([
                'published_url' => $enhancement->result_url,
            ]);
        });

        $car = $enhancement->media?->car;
        if ($car !== null) {
            $this->revalidator->revalidate(["car:{$car->slug}"]);
        }

        return $enhancement->refresh();
    }
}
