<?php

declare(strict_types=1);

namespace App\Enums;

enum EnhancementType: string
{
    case AutoImprove = 'auto_improve';
    case SmartCrop = 'smart_crop';
    case BackgroundRemoval = 'background_removal';

    public function label(): string
    {
        return match ($this) {
            self::AutoImprove => 'Amélioration automatique',
            self::SmartCrop => 'Recadrage intelligent',
            self::BackgroundRemoval => 'Détourage / Suppression d\'arrière-plan',
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
