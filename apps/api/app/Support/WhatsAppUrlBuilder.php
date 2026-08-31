<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Car;
use App\Models\Setting;

final readonly class WhatsAppUrlBuilder
{
    /**
     * Build a formatted WhatsApp Click-to-Chat URL for a specific car.
     */
    public static function build(Car $car): string
    {
        $rawPhone = (string) Setting::get('whatsapp_number', (string) config('media.default_whatsapp_number', '+237699001122'));
        // Clean phone number: keep only digits
        $cleanPhone = preg_replace('/\D+/', '', $rawPhone) ?: '237699001122';

        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        $carUrl = "{$frontendUrl}/voitures/{$car->slug}";
        $brandName = $car->brand?->name ?? '';

        $message = sprintf(
            'Bonjour, je suis intéressé par votre annonce : %s %s (%d) — %s',
            $brandName,
            $car->model,
            $car->year,
            $carUrl
        );

        return sprintf(
            'https://wa.me/%s?text=%s',
            $cleanPhone,
            rawurlencode($message)
        );
    }
}
