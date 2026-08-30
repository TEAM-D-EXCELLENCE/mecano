<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaProvider: string
{
    case Cloudinary = 'cloudinary';
    case R2 = 'r2';
    case RemoveBg = 'removebg';

    public function label(): string
    {
        return match ($this) {
            self::Cloudinary => 'Cloudinary',
            self::R2 => 'Cloudflare R2',
            self::RemoveBg => 'remove.bg',
        };
    }

    /**
     * @return array{value: string, label: string}
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
        ];
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
