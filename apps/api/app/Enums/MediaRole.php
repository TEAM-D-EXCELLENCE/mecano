<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaRole: string
{
    case Main = 'main';
    case Gallery = 'gallery';
    case VideoInterior = 'video_interior';
    case VideoExterior = 'video_exterior';

    public function label(): string
    {
        return match ($this) {
            self::Main => 'Photo principale',
            self::Gallery => 'Galerie',
            self::VideoInterior => 'Vidéo intérieur',
            self::VideoExterior => 'Vidéo extérieur',
        };
    }

    /**
     * Exclusive roles are constrained to a single occurrence per car in the database.
     */
    public function isExclusive(): bool
    {
        return match ($this) {
            self::Main, self::VideoInterior, self::VideoExterior => true,
            self::Gallery => false,
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
