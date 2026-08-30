<?php

declare(strict_types=1);

namespace App\Enums;

enum ImageTransformPreset: string
{
    case Thumb = 'thumb';
    case Card = 'card';
    case Detail = 'detail';
    case Og = 'og';

    public function defaultTransformation(): string
    {
        return match ($this) {
            self::Thumb => 'w_200,h_150,c_fill,f_auto,q_auto',
            self::Card => 'w_640,h_480,c_fill,g_auto,f_auto,q_auto',
            self::Detail => 'w_1280,h_960,c_limit,f_auto,q_auto',
            self::Og => 'w_1200,h_630,c_fill,g_auto,f_auto,q_auto',
        };
    }
}
