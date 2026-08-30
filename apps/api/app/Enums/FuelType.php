<?php

declare(strict_types=1);

namespace App\Enums;

enum FuelType: string
{
    case Essence = 'essence';
    case Diesel = 'diesel';
    case Hybride = 'hybride';
    case Electrique = 'electrique';
    case Gpl = 'gpl';

    public function label(): string
    {
        return match ($this) {
            self::Essence => 'Essence',
            self::Diesel => 'Diesel',
            self::Hybride => 'Hybride',
            self::Electrique => 'Électrique',
            self::Gpl => 'GPL',
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
