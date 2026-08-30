<?php

declare(strict_types=1);

namespace App\Enums;

enum VehicleCondition: string
{
    case Neuf = 'neuf';
    case Excellent = 'excellent';
    case Bon = 'bon';
    case Moyen = 'moyen';

    public function label(): string
    {
        return match ($this) {
            self::Neuf => 'Neuf',
            self::Excellent => 'Excellent état',
            self::Bon => 'Bon état',
            self::Moyen => 'État moyen',
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
