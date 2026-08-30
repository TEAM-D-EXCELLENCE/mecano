<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CarEventType;
use App\Enums\CarStatus;
use App\Enums\FuelType;
use App\Enums\MediaKind;
use App\Enums\MediaProvider;
use App\Enums\MediaRole;
use App\Enums\TransmissionType;
use App\Enums\VehicleCondition;
use App\Models\Brand;
use App\Models\Car;
use App\Models\CarEvent;
use App\Models\Media;
use Illuminate\Database\Seeder;

final class CarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = Brand::query()->pluck('id', 'slug');

        $carsData = [
            [
                'slug' => 'toyota-corolla-2020-1',
                'brand_slug' => 'toyota',
                'model' => 'Corolla 1.8 Dual VVT-i',
                'year' => 2020,
                'mileage_km' => 52000,
                'price_xaf' => 8500000,
                'fuel' => FuelType::Essence,
                'transmission' => TransmissionType::Automatique,
                'color' => 'Gris métallisé',
                'condition' => VehicleCondition::Excellent,
                'description' => "Véhicule première main en excellent état mécanique et carrosserie. Entretien exclusif chez le concessionnaire officiel. Climatisation d'origine très performante, écran tactile multimédia avec caméra de recul, jantes alliage 16 pouces.",
                'status' => CarStatus::Available,
                'is_featured' => true,
                'published_at' => now()->subDays(12),
                'sold_at' => null,
                'views_count' => 142,
                'whatsapp_clicks_count' => 18,
                'photos_count' => 4,
                'has_interior_video' => true,
                'has_exterior_video' => false,
            ],
            [
                'slug' => 'mercedes-benz-c200-2019-2',
                'brand_slug' => 'mercedes-benz',
                'model' => 'Classe C 200 Avantgarde',
                'year' => 2019,
                'mileage_km' => 68000,
                'price_xaf' => 14500000,
                'fuel' => FuelType::Essence,
                'transmission' => TransmissionType::Automatique,
                'color' => 'Noir Obsidienne',
                'condition' => VehicleCondition::Excellent,
                'description' => 'Berline premium luxueuse. Intérieur cuir beige en parfait état, toit ouvrant panoramique, phares LED High Performance, aide au stationnement active avec radars avant/arrière.',
                'status' => CarStatus::Available,
                'is_featured' => true,
                'published_at' => now()->subDays(5),
                'sold_at' => null,
                'views_count' => 280,
                'whatsapp_clicks_count' => 34,
                'photos_count' => 5,
                'has_interior_video' => true,
                'has_exterior_video' => true,
            ],
            [
                'slug' => 'toyota-prado-tx-2018-3',
                'brand_slug' => 'toyota',
                'model' => 'Land Cruiser Prado TX 3.0 D4D',
                'year' => 2018,
                'mileage_km' => 95000,
                'price_xaf' => 22000000,
                'fuel' => FuelType::Diesel,
                'transmission' => TransmissionType::Automatique,
                'color' => 'Blanc Nacré',
                'condition' => VehicleCondition::Bon,
                'description' => "4x4 robuste et fiable, 7 places assises. Parfait pour les longs trajets et pistes difficiles. Suspension renforcée, climatisation tri-zone, carnet d'entretien complet à jour.",
                'status' => CarStatus::Available,
                'is_featured' => true,
                'published_at' => now()->subDays(8),
                'sold_at' => null,
                'views_count' => 310,
                'whatsapp_clicks_count' => 42,
                'photos_count' => 5,
                'has_interior_video' => true,
                'has_exterior_video' => false,
            ],
            [
                'slug' => 'peugeot-3008-2021-4',
                'brand_slug' => 'peugeot',
                'model' => '3008 GT-Line 1.6 PureTech',
                'year' => 2021,
                'mileage_km' => 38000,
                'price_xaf' => 13800000,
                'fuel' => FuelType::Essence,
                'transmission' => TransmissionType::Automatique,
                'color' => 'Bleu Célèbes',
                'condition' => VehicleCondition::Excellent,
                'description' => 'SUV moderne au style affirmé. i-Cockpit digital 3D, Apple CarPlay / Android Auto sans fil, jantes 18 pouces biton, sellerie mixte cuir/Alcantara.',
                'status' => CarStatus::Available,
                'is_featured' => false,
                'published_at' => now()->subDays(15),
                'sold_at' => null,
                'views_count' => 195,
                'whatsapp_clicks_count' => 21,
                'photos_count' => 4,
                'has_interior_video' => false,
                'has_exterior_video' => false,
            ],
            [
                'slug' => 'hyundai-tucson-2022-5',
                'brand_slug' => 'hyundai',
                'model' => 'Tucson Executive 2.0 MPi',
                'year' => 2022,
                'mileage_km' => 24000,
                'price_xaf' => 16500000,
                'fuel' => FuelType::Essence,
                'transmission' => TransmissionType::Automatique,
                'color' => 'Gris Amazon',
                'condition' => VehicleCondition::Neuf,
                'description' => "État proche du neuf sous garantie résiduelle. Éclairage paramétrique avant masqué, combiné d'instruments 10.25 pouces, chargeur induction, régulateur adaptatif.",
                'status' => CarStatus::Available,
                'is_featured' => true,
                'published_at' => now()->subDays(2),
                'sold_at' => null,
                'views_count' => 98,
                'whatsapp_clicks_count' => 12,
                'photos_count' => 4,
                'has_interior_video' => true,
                'has_exterior_video' => false,
            ],
            [
                'slug' => 'renault-duster-2017-6',
                'brand_slug' => 'renault',
                'model' => 'Duster 1.5 dCi 4x2',
                'year' => 2017,
                'mileage_km' => 110000,
                'price_xaf' => 5200000,
                'fuel' => FuelType::Diesel,
                'transmission' => TransmissionType::Manuelle,
                'color' => 'Brun Vison',
                'condition' => VehicleCondition::Bon,
                'description' => 'SUV économique et spacieux, très faible consommation de carburant. Entièrement révisé par nos mécaniciens : vidange effectuée, filtres neufs, freins avant neufs.',
                'status' => CarStatus::Available,
                'is_featured' => false,
                'published_at' => now()->subDays(20),
                'sold_at' => null,
                'views_count' => 165,
                'whatsapp_clicks_count' => 25,
                'photos_count' => 3,
                'has_interior_video' => false,
                'has_exterior_video' => false,
            ],
            [
                'slug' => 'toyota-hilux-double-cabine-2020-7',
                'brand_slug' => 'toyota',
                'model' => 'Hilux Double Cabine 2.4 D-4D 4x4',
                'year' => 2020,
                'mileage_km' => 78000,
                'price_xaf' => 18500000,
                'fuel' => FuelType::Diesel,
                'transmission' => TransmissionType::Manuelle,
                'color' => 'Blanc Pur',
                'condition' => VehicleCondition::Bon,
                'description' => 'Pick-up utilitaire tout-terrain robuste. Benne protégée avec couvre-benne rigide, attelage remorque, blocage de différentiel arrière, climatisation.',
                'status' => CarStatus::Available,
                'is_featured' => false,
                'published_at' => now()->subDays(18),
                'sold_at' => null,
                'views_count' => 240,
                'whatsapp_clicks_count' => 38,
                'photos_count' => 4,
                'has_interior_video' => false,
                'has_exterior_video' => false,
            ],
            [
                'slug' => 'kia-sportage-2019-8',
                'brand_slug' => 'kia',
                'model' => 'Sportage 2.0 CRDi AWD',
                'year' => 2019,
                'mileage_km' => 72000,
                'price_xaf' => 10200000,
                'fuel' => FuelType::Diesel,
                'transmission' => TransmissionType::Automatique,
                'color' => 'Rouge Passion',
                'condition' => VehicleCondition::Bon,
                'description' => 'SUV 4 roues motrices polyvalent et confortable. Intérieur cuir, volant chauffant, toit vitré, système audio premium JBL, historique limpide.',
                'status' => CarStatus::Available,
                'is_featured' => false,
                'published_at' => now()->subDays(25),
                'sold_at' => null,
                'views_count' => 180,
                'whatsapp_clicks_count' => 16,
                'photos_count' => 3,
                'has_interior_video' => false,
                'has_exterior_video' => false,
            ],
            // -----------------------------------------------------------------
            // Edge Cases (CDC & Specs requirements)
            // -----------------------------------------------------------------
            // Edge Case 1: Reserved car
            [
                'slug' => 'mercedes-benz-gle-350-2021-9',
                'brand_slug' => 'mercedes-benz',
                'model' => 'GLE 350d 4MATIC AMG Line',
                'year' => 2021,
                'mileage_km' => 34000,
                'price_xaf' => 32000000,
                'fuel' => FuelType::Diesel,
                'transmission' => TransmissionType::Automatique,
                'color' => 'Argent Iridium',
                'condition' => VehicleCondition::Excellent,
                'description' => "Véhicule d'exception actuellement sous réservation client.",
                'status' => CarStatus::Reserved,
                'is_featured' => false,
                'published_at' => now()->subDays(10),
                'sold_at' => null,
                'views_count' => 450,
                'whatsapp_clicks_count' => 52,
                'photos_count' => 4,
                'has_interior_video' => true,
                'has_exterior_video' => true,
            ],
            // Edge Case 2: Sold car (remains public with badge, D14)
            [
                'slug' => 'toyota-rav4-2018-10',
                'brand_slug' => 'toyota',
                'model' => 'RAV4 2.0 VVT-i AWD',
                'year' => 2018,
                'mileage_km' => 88000,
                'price_xaf' => 11500000,
                'fuel' => FuelType::Essence,
                'transmission' => TransmissionType::Automatique,
                'color' => 'Gris Minéral',
                'condition' => VehicleCondition::Bon,
                'description' => 'Véhicule vendu récemment par le garage. Resté en ligne pour démonstration de notre historique de vente.',
                'status' => CarStatus::Sold,
                'is_featured' => false,
                'published_at' => now()->subDays(45),
                'sold_at' => now()->subDays(12),
                'views_count' => 320,
                'whatsapp_clicks_count' => 48,
                'photos_count' => 4,
                'has_interior_video' => false,
                'has_exterior_video' => false,
            ],
            // Edge Case 3: Another Sold car
            [
                'slug' => 'peugeot-208-2016-11',
                'brand_slug' => 'peugeot',
                'model' => '208 1.2 PureTech Allure',
                'year' => 2016,
                'mileage_km' => 125000,
                'price_xaf' => 3900000,
                'fuel' => FuelType::Essence,
                'transmission' => TransmissionType::Manuelle,
                'color' => 'Blanc Banquise',
                'condition' => VehicleCondition::Moyen,
                'description' => 'Citadine économique vendue le mois dernier.',
                'status' => CarStatus::Sold,
                'is_featured' => false,
                'published_at' => now()->subDays(60),
                'sold_at' => now()->subDays(28),
                'views_count' => 210,
                'whatsapp_clicks_count' => 30,
                'photos_count' => 3,
                'has_interior_video' => false,
                'has_exterior_video' => false,
            ],
            // Edge Case 4: No description (null description)
            [
                'slug' => 'nissan-qashqai-2018-12',
                'brand_slug' => 'nissan',
                'model' => 'Qashqai 2.0 Acenta',
                'year' => 2018,
                'mileage_km' => 80000,
                'price_xaf' => 8900000,
                'fuel' => FuelType::Essence,
                'transmission' => TransmissionType::Automatique,
                'color' => 'Bleu Magnétique',
                'condition' => VehicleCondition::Bon,
                'description' => null, // Null description edge case
                'status' => CarStatus::Available,
                'is_featured' => false,
                'published_at' => now()->subDays(7),
                'sold_at' => null,
                'views_count' => 110,
                'whatsapp_clicks_count' => 9,
                'photos_count' => 3,
                'has_interior_video' => false,
                'has_exterior_video' => false,
            ],
            // Edge Case 5: Very long description + 6 photos + 2 videos
            [
                'slug' => 'volkswagen-tiguan-r-line-2020-13',
                'brand_slug' => 'volkswagen',
                'model' => 'Tiguan 2.0 TDI 4Motion R-Line',
                'year' => 2020,
                'mileage_km' => 48000,
                'price_xaf' => 17200000,
                'fuel' => FuelType::Diesel,
                'transmission' => TransmissionType::Automatique,
                'color' => 'Blanc Pur R-Line',
                'condition' => VehicleCondition::Excellent,
                'description' => "Ce véhicule a bénéficié d'une inspection complète de 110 points dans notre atelier spécialisé.\n\nLe moteur 2.0 TDI développe une puissance linéaire tout en maintenant une consommation maîtrisée de 6.2L/100km sur autoroute. La boîte DSG à 7 rapports a été vidangée selon les préconisations constructeur à 45 000 km avec filtre d'origine.\n\nÉquipements notables : Pack R-Line extérieur et intérieur, toit ouvrant panoramique électrique, affichage tête haute (Head-up display), système de son Dynaudio 400W, jantes alliage 19 pouces Suzuka avec 4 pneus neufs Michelin.",
                'status' => CarStatus::Available,
                'is_featured' => true,
                'published_at' => now()->subDays(4),
                'sold_at' => null,
                'views_count' => 390,
                'whatsapp_clicks_count' => 65,
                'photos_count' => 6,
                'has_interior_video' => true,
                'has_exterior_video' => true,
            ],
            // Edge Case 6: Draft car (never served on public endpoints)
            [
                'slug' => 'toyota-yaris-2015-14',
                'brand_slug' => 'toyota',
                'model' => 'Yaris 1.3 VVT-i',
                'year' => 2015,
                'mileage_km' => 140000,
                'price_xaf' => 4200000,
                'fuel' => FuelType::Essence,
                'transmission' => TransmissionType::Manuelle,
                'color' => 'Gris Argent',
                'condition' => VehicleCondition::Moyen,
                'description' => "Brouillon en attente de validation et d'ajout de photos supplémentaires.",
                'status' => CarStatus::Draft,
                'is_featured' => false,
                'published_at' => null,
                'sold_at' => null,
                'views_count' => 0,
                'whatsapp_clicks_count' => 0,
                'photos_count' => 2,
                'has_interior_video' => false,
                'has_exterior_video' => false,
            ],
            // Edge Case 7: Car with deactivated brand (Chevrolet) - D11
            [
                'slug' => 'chevrolet-cruze-2016-15',
                'brand_slug' => 'chevrolet',
                'model' => 'Cruze LT 1.6',
                'year' => 2016,
                'mileage_km' => 95000,
                'price_xaf' => 4500000,
                'fuel' => FuelType::Essence,
                'transmission' => TransmissionType::Automatique,
                'color' => 'Bordeaux',
                'condition' => VehicleCondition::Bon,
                'description' => 'Berline confortable avec climatisation et grand coffre.',
                'status' => CarStatus::Available,
                'is_featured' => false,
                'published_at' => now()->subDays(22),
                'sold_at' => null,
                'views_count' => 85,
                'whatsapp_clicks_count' => 6,
                'photos_count' => 3,
                'has_interior_video' => false,
                'has_exterior_video' => false,
            ],
        ];

        foreach ($carsData as $data) {
            $brandId = $brands[$data['brand_slug']] ?? null;

            if (! $brandId) {
                continue;
            }

            /** @var Car $car */
            $car = Car::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'brand_id' => $brandId,
                    'model' => $data['model'],
                    'year' => $data['year'],
                    'mileage_km' => $data['mileage_km'],
                    'price_xaf' => $data['price_xaf'],
                    'fuel' => $data['fuel'],
                    'transmission' => $data['transmission'],
                    'color' => $data['color'],
                    'condition' => $data['condition'],
                    'description' => $data['description'],
                    'status' => $data['status'],
                    'is_featured' => $data['is_featured'],
                    'published_at' => $data['published_at'],
                    'sold_at' => $data['sold_at'],
                    'views_count' => $data['views_count'],
                    'whatsapp_clicks_count' => $data['whatsapp_clicks_count'],
                ]
            );

            // Clean previous media & events on re-seeding
            $car->media()->delete();
            $car->events()->delete();

            // Seed Main Photo (position 0)
            Media::query()->create([
                'car_id' => $car->id,
                'kind' => MediaKind::Photo,
                'role' => MediaRole::Main,
                'provider' => MediaProvider::Cloudinary,
                'storage_key' => "cars/{$car->slug}/photo-main",
                'url' => "https://res.cloudinary.com/garage/image/upload/v1724000000/cars/{$car->slug}/photo-main.jpg",
                'published_url' => "https://res.cloudinary.com/garage/image/upload/v1724000000/cars/{$car->slug}/photo-main.jpg",
                'mime' => 'image/jpeg',
                'bytes' => 450000,
                'width' => 1920,
                'height' => 1080,
                'position' => 0,
                'alt' => "{$car->model} — Vue principale",
                'confirmed_at' => now(),
            ]);

            // Seed Gallery Photos
            for ($i = 1; $i < $data['photos_count']; $i++) {
                Media::query()->create([
                    'car_id' => $car->id,
                    'kind' => MediaKind::Photo,
                    'role' => MediaRole::Gallery,
                    'provider' => MediaProvider::Cloudinary,
                    'storage_key' => "cars/{$car->slug}/photo-gallery-{$i}",
                    'url' => "https://res.cloudinary.com/garage/image/upload/v1724000000/cars/{$car->slug}/photo-gallery-{$i}.jpg",
                    'published_url' => "https://res.cloudinary.com/garage/image/upload/v1724000000/cars/{$car->slug}/photo-gallery-{$i}.jpg",
                    'mime' => 'image/jpeg',
                    'bytes' => 380000 + ($i * 15000),
                    'width' => 1920,
                    'height' => 1080,
                    'position' => $i,
                    'alt' => "{$car->model} — Photo {$i}",
                    'confirmed_at' => now(),
                ]);
            }

            // Seed Interior Video
            if ($data['has_interior_video']) {
                Media::query()->create([
                    'car_id' => $car->id,
                    'kind' => MediaKind::Video,
                    'role' => MediaRole::VideoInterior,
                    'provider' => MediaProvider::R2,
                    'storage_key' => "videos/{$car->slug}/video-interior.mp4",
                    'url' => "https://media.garage.com/videos/{$car->slug}/video-interior.mp4",
                    'published_url' => "https://media.garage.com/videos/{$car->slug}/video-interior.mp4",
                    'mime' => 'video/mp4',
                    'bytes' => 28000000,
                    'duration_s' => 45,
                    'position' => 10,
                    'alt' => "{$car->model} — Tour intérieur 360",
                    'confirmed_at' => now(),
                ]);
            }

            // Seed Exterior Video
            if ($data['has_exterior_video']) {
                Media::query()->create([
                    'car_id' => $car->id,
                    'kind' => MediaKind::Video,
                    'role' => MediaRole::VideoExterior,
                    'provider' => MediaProvider::R2,
                    'storage_key' => "videos/{$car->slug}/video-exterior.mp4",
                    'url' => "https://media.garage.com/videos/{$car->slug}/video-exterior.mp4",
                    'published_url' => "https://media.garage.com/videos/{$car->slug}/video-exterior.mp4",
                    'mime' => 'video/mp4',
                    'bytes' => 35000000,
                    'duration_s' => 60,
                    'position' => 11,
                    'alt' => "{$car->model} — Tour extérieur",
                    'confirmed_at' => now(),
                ]);
            }

            // Seed some sample events
            for ($v = 0; $v < min(5, (int) ($data['views_count'] / 20)); $v++) {
                CarEvent::query()->create([
                    'car_id' => $car->id,
                    'type' => CarEventType::View,
                    'ip_hash' => hash('sha256', "192.168.1.{$v}_salt"),
                    'referer' => $v % 2 === 0 ? 'google.com' : 'facebook.com',
                    'created_at' => now()->subDays($v),
                ]);
            }

            if ($data['whatsapp_clicks_count'] > 0) {
                CarEvent::query()->create([
                    'car_id' => $car->id,
                    'type' => CarEventType::WhatsappClick,
                    'ip_hash' => hash('sha256', '192.168.1.99_salt'),
                    'referer' => 'google.com',
                    'created_at' => now()->subHours(6),
                ]);
            }
        }
    }
}
