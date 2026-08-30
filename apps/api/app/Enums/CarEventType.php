<?php

declare(strict_types=1);

namespace App\Enums;

enum CarEventType: string
{
    case View = 'view';
    case WhatsappClick = 'whatsapp_click';

    public function label(): string
    {
        return match ($this) {
            self::View => 'Vue fiche',
            self::WhatsappClick => 'Clic WhatsApp',
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
