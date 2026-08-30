<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Actions\Cars\RecordCarEvent;
use App\Data\RecordCarEventData;
use App\Enums\CarStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\RecordCarEventRequest;
use App\Models\Car;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CarEventController extends Controller
{
    /**
     * Handle incoming car analytics event (view, whatsapp_click).
     */
    public function __invoke(RecordCarEventRequest $request, string $slug, RecordCarEvent $recordCarEvent): Response
    {
        /** @var Car|null $car */
        $car = Car::query()
            ->where('slug', $slug)
            ->whereIn('status', [
                CarStatus::Available->value,
                CarStatus::Reserved->value,
                CarStatus::Sold->value,
            ])
            ->first();

        if (! $car) {
            throw new NotFoundHttpException('Ce véhicule est introuvable ou n\'est plus disponible.');
        }

        $recordCarEvent->handle($car, RecordCarEventData::fromRequest($request));

        return response()->noContent();
    }
}
