<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

final class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'whatsapp_number' => env('DEFAULT_WHATSAPP_NUMBER', '+237699001122'),
            'garage_name' => env('DEFAULT_GARAGE_NAME', "Garage de l'Excellence"),
            'hero_title' => 'Véhicules d\'occasion révisés et garantis par notre chef mécanicien.',
            'hero_subtitle' => 'Chaque véhicule est minutieusement inspecté sur 110 points de contrôle avant mise en vente.',
            'address' => 'Boulevard de la Liberté, Douala, Cameroun',
            'opening_hours' => [
                'lundi_vendredi' => '08:00 - 18:00',
                'samedi' => '08:30 - 14:00',
                'dimanche' => 'Fermé',
            ],
            'logo_url' => null,
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
