<?php

declare(strict_types=1);

namespace App\Enums;

enum EnhancementStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Ready = 'ready';
    case Failed = 'failed';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Processing => 'Traitement en cours',
            self::Ready => 'Prêt pour validation',
            self::Failed => 'Échec du traitement',
            self::Approved => 'Approuvé et publié',
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
