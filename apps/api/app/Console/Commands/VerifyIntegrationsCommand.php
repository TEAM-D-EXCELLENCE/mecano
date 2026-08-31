<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Car;
use App\Models\Post;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

final class VerifyIntegrationsCommand extends Command
{
    protected $signature = 'verify:integrations';

    protected $description = 'Verify live connectivity and health for Supabase (PostgreSQL) and Cloudinary';

    public function handle(): int
    {
        $this->info('==========================================');
        $this->info('   VÉRIFICATION SUPABASE & CLOUDINARY     ');
        $this->info("==========================================\n");

        $allOk = true;

        // ---------------------------------------------------------------------
        // 1. VÉRIFICATION SUPABASE (POSTGRESQL)
        // ---------------------------------------------------------------------
        $this->comment('1. Test de la connexion Supabase PostgreSQL...');
        try {
            $start = microtime(true);
            $pgVersion = DB::selectOne('SELECT version() as ver')->ver;
            $duration = round((microtime(true) - $start) * 1000, 1);

            $this->info("   [OK] Connexion réussie en {$duration} ms !");
            $this->line('   -> Version serveur : '.substr((string) $pgVersion, 0, 50).'...');

            // Comptage des données
            $usersCount = User::query()->count();
            $brandsCount = Brand::query()->count();
            $carsCount = Car::query()->count();
            $servicesCount = Service::query()->count();
            $postsCount = Post::query()->count();
            $settingsCount = Setting::query()->count();

            $this->info('   [OK] Données présentes en base :');
            $this->line("      - Utilisateurs : {$usersCount}");
            $this->line("      - Marques : {$brandsCount}");
            $this->line("      - Véhicules : {$carsCount}");
            $this->line("      - Forfaits atelier : {$servicesCount}");
            $this->line("      - Articles de blog : {$postsCount}");
            $this->line("      - Réglages du garage : {$settingsCount}");
        } catch (\Throwable $e) {
            $allOk = false;
            $this->error('   [FAIL] Erreur Supabase : '.$e->getMessage());
        }

        $this->newLine();

        // ---------------------------------------------------------------------
        // 2. VÉRIFICATION CLOUDINARY
        // ---------------------------------------------------------------------
        $this->comment("2. Test de l'API Cloudinary...");
        // `config()` et non `env()` : dès que la configuration est mise en cache
        // — ce que fait l'entrypoint Docker au démarrage — `env()` renvoie null
        // et la commande annoncerait des identifiants manquants à tort.
        $cloudName = (string) config('media.cloudinary.cloud_name');
        $apiKey = (string) config('media.cloudinary.api_key');
        $apiSecret = (string) config('media.cloudinary.api_secret');

        if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
            $allOk = false;
            $this->error("   [FAIL] Identifiants Cloudinary manquants dans l'environnement !");
        } else {
            try {
                $start = microtime(true);

                // Ping Cloudinary via API REST Ping
                $response = Http::withBasicAuth($apiKey, $apiSecret)
                    ->get("https://api.cloudinary.com/v1_1/{$cloudName}/ping");

                $duration = round((microtime(true) - $start) * 1000, 1);

                if ($response->successful()) {
                    $this->info("   [OK] Authentification Cloudinary validée en {$duration} ms !");
                    $this->line("   -> Cloud Name : {$cloudName}");
                    $this->line('   -> API Status : '.$response->json('status'));

                    // Test signature génération pour upload direct frontend
                    $timestamp = time();
                    $folder = (string) config('media.photos.upload_folder');
                    $toSign = "folder={$folder}&timestamp={$timestamp}".$apiSecret;
                    $signature = sha1($toSign);

                    $this->info("   [OK] Générateur de signature d'upload direct opérationnel !");
                    $this->line("   -> Signature de test : {$signature}");
                } else {
                    $allOk = false;
                    $this->error("   [FAIL] Erreur Cloudinary HTTP {$response->status()} : ".$response->body());
                }
            } catch (\Throwable $e) {
                $allOk = false;
                $this->error('   [FAIL] Erreur Cloudinary : '.$e->getMessage());
            }
        }

        $this->newLine();
        $this->info('==========================================');
        if ($allOk) {
            $this->info('  TOUS LES SERVICES DISTANTS SONT 100% OPÉRATIONNELS ! ');
        } else {
            $this->error('  AU MOINS UNE INTÉGRATION A RENCONTRÉ UN PROBLÈME. ');
        }
        $this->info('==========================================');

        return $allOk ? Command::SUCCESS : Command::FAILURE;
    }
}
