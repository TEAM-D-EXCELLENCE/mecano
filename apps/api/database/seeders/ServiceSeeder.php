<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

final class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'slug' => 'diagnostic-electronique',
                'title' => 'Diagnostic électronique complet',
                'excerpt' => 'Analyse approfondie de tous les calculateurs et capteurs de votre véhicule avec équipement de pointe multimarque.',
                'description' => "Notre atelier dispose de valises de diagnostic professionnelles compatibles avec toutes les marques asiatiques, européennes et américaines.\n\nNous identifions l'origine exacte des voyants moteur, ABS, airbag, boîtes automatiques et soucis d'injection sans tâtonnement.",
                'icon' => 'gauge',
                'price_from_xaf' => 25000,
                'is_active' => true,
                'position' => 1,
            ],
            [
                'slug' => 'revision-vidange',
                'title' => 'Révision générale & vidange moteur',
                'excerpt' => 'Entretien périodique complet avec huiles synthétiques haute performance et 30 points de contrôle de sécurité.',
                'description' => "La vidange régulière avec des lubrifiants adaptés au climat chaud et humide est indispensable pour préserver la longévité de votre moteur.\n\nCette prestation inclut le remplacement du filtre à huile, le contrôle des niveaux (frein, refroidissement, direction), la vérification des trains roulants et de la batterie.",
                'icon' => 'wrench',
                'price_from_xaf' => 35000,
                'is_active' => true,
                'position' => 2,
            ],
            [
                'slug' => 'climatisation-auto',
                'title' => 'Entretien et recharge de climatisation',
                'excerpt' => 'Recharge en gaz R134a, détection de micro-fuites, nettoyage antibactérien du circuit et remplacement du filtre habitacle.',
                'description' => "Ne subissez plus la chaleur : nous remettons en état votre système d'air conditionné avec test d'étanchéité sous pression d'azote, tirage au vide complet, recharge précise en fluide frigorigène et huile pour compresseur.",
                'icon' => 'zap',
                'price_from_xaf' => 30000,
                'is_active' => true,
                'position' => 3,
            ],
            [
                'slug' => 'freinage-suspension',
                'title' => 'Système de freinage & trains roulants',
                'excerpt' => 'Contrôle et remplacement des plaquettes, disques, mâchoires, amortisseurs et biellettes de suspension.',
                'description' => "Votre sécurité dépend directement de l'état de votre freinage et de votre suspension face aux aléas de la route.\n\nNous utilisons exclusivement des pièces de rechange certifiées pour assurer un freinage réactif et une tenue de route irréprochable.",
                'icon' => 'shield-check',
                'price_from_xaf' => 20000,
                'is_active' => true,
                'position' => 4,
            ],
            [
                'slug' => 'depannage-remorquage',
                'title' => 'Dépannage & remorquage 24/7',
                'excerpt' => 'Assistance rapide et transport sécurisé de votre véhicule en cas de panne ou d\'accident sur tout le Grand Douala.',
                'description' => "En cas d'immobilisation de votre voiture, notre équipe d'intervention rapide vous prend en charge et achemine votre véhicule en toute sécurité vers notre atelier pour réparation.",
                'icon' => 'truck',
                'price_from_xaf' => null, // Sur devis
                'is_active' => true,
                'position' => 5,
            ],
            [
                'slug' => 'electricite-circuits',
                'title' => 'Électricité automobile & circuits',
                'excerpt' => 'Réparation d\'alternateurs, démarreurs, lève-vitres, éclairage LED et faisceaux électriques complexes.',
                'description' => 'De la simple panne de batterie aux coupures intermittentes de faisceau, nos techniciens électriciens résolvent tous vos dysfonctionnements électriques avec minutie.',
                'icon' => 'car',
                'price_from_xaf' => 15000,
                'is_active' => true,
                'position' => 6,
            ],
        ];

        foreach ($services as $service) {
            Service::query()->updateOrCreate(
                ['slug' => $service['slug']],
                $service
            );
        }
    }
}
