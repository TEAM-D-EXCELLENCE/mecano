<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

final class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = User::query()->first() ?? User::factory()->create();

        $diagnosticService = Service::query()->where('slug', 'diagnostic-electronique')->first();
        $vidangeService = Service::query()->where('slug', 'revision-vidange')->first();
        $climService = Service::query()->where('slug', 'climatisation-auto')->first();
        $freinageService = Service::query()->where('slug', 'freinage-suspension')->first();

        $posts = [
            [
                'slug' => '5-signes-plaquettes-frein-usees',
                'title' => '5 signes qu\'il faut remplacer vos plaquettes de frein',
                'excerpt' => 'Grincements, pédale molle, distance d\'arrêt allongée : découvrez les alertes critiques pour votre sécurité.',
                'body' => "Le système de freinage est l'organe de sécurité le plus important de votre voiture. Savoir détecter les signes d'usure des plaquettes permet d'éviter d'endommager les disques de frein et de prévenir les accidents.\n\n1. Un bruit de grincement métallique aigu lors du freinage.\n2. La pédale de frein qui devient spongieuse ou s'enfonce trop bas.\n3. Des vibrations inhabituelles dans le volant lors du ralentissement.\n4. Le véhicule qui tire d'un côté au freinage.\n5. L'allumage du voyant de frein sur le tableau de bord.\n\nSi vous observez l'un de ces symptômes, faites contrôler immédiatement vos freins dans notre atelier.",
                'service_id' => $freinageService?->id,
                'author_id' => $author->id,
                'status' => PostStatus::Published,
                'published_at' => now()->subDays(10),
            ],
            [
                'slug' => 'proteger-moteur-climat-tropical',
                'title' => 'Comment bien choisir son huile moteur sous climat tropical ?',
                'excerpt' => 'Chaleur, humidité et embouteillages urbains : adaptez la viscosité et le type d\'huile pour prolonger la vie de votre moteur.',
                'body' => "Les conditions de conduite en Afrique centrale soumettent les moteurs à rude épreuve : températures élevées, poussière et arrêts fréquents dans les embouteillages.\n\nUne huile moteur de qualité synthétique (5W30, 5W40 ou 10W40 selon les préconisations du constructeur) garantit un film lubrifiant stable même à très haute température.\n\nNous vous recommandons de respecter scrupuleusement l'intervalle de vidange tous les 5 000 à 7 500 km dans notre environnement.",
                'service_id' => $vidangeService?->id,
                'author_id' => $author->id,
                'status' => PostStatus::Published,
                'published_at' => now()->subDays(6),
            ],
            [
                'slug' => 'pourquoi-climatisation-ne-refroidit-plus',
                'title' => 'Pourquoi votre climatisation auto ne souffle plus d\'air froid ?',
                'excerpt' => 'Comprendre les causes fréquentes de panne de climatisation et les solutions pour retrouver un confort optimal.',
                'body' => "Rien n'est plus désagréable qu'une climatisation qui souffle de l'air tiède en pleine chaleur.\n\nLes causes principales sont généralement :\n- Une baisse du niveau de gaz réfrigérant due à une micro-fuite sur un joint torique.\n- Un compresseur de climatisation défaillant ou dont l'embrayage électromagnétique patine.\n- Un condenseur obstrué par la poussière et les débris de la route.\n- Un filtre d'habitacle complètement colmaté.\n\nUn simple contrôle avec tirage au vide et détection de fuite permet de remettre votre clim à neuf.",
                'service_id' => $climService?->id,
                'author_id' => $author->id,
                'status' => PostStatus::Published,
                'published_at' => now()->subDays(3),
            ],
            [
                'slug' => 'voyant-moteur-allume-que-faire',
                'title' => 'Voyant moteur allumé : les bons réflexes à adopter',
                'excerpt' => 'Voyant clignotant ou fixe ? Découvrez la marche à suivre pour protéger votre bloc moteur sans paniquer.',
                'body' => "Le voyant moteur (« Check Engine ») signale qu'une anomalie a été enregistrée par le calculateur de gestion moteur.\n\nSi le voyant clignote, arrêtez le véhicule dès que possible : cela indique un raté de combustion grave pouvant détruire le catalyseur.\n\nSi le voyant reste fixe, le problème peut provenir de la sonde lambda, du débitmètre d'air, des bougies ou du circuit d'admission. Un passage au diagnostic valise électronique permet d'extraire le code défaut précis en quelques minutes.",
                'service_id' => $diagnosticService?->id,
                'author_id' => $author->id,
                'status' => PostStatus::Published,
                'published_at' => now()->subDay(),
            ],
            [
                'slug' => 'guide-achat-voiture-occasion-cameroun',
                'title' => 'Guide d\'achat : 10 points clés pour réussir son achat de véhicule d\'occasion',
                'excerpt' => 'Brouillon d\'article en cours de rédaction par notre équipe technique.',
                'body' => "Article en cours de rédaction sur les étapes de vérification mécanique avant achat d'un véhicule d'occasion.",
                'service_id' => null,
                'author_id' => $author->id,
                'status' => PostStatus::Draft,
                'published_at' => null,
            ],
        ];

        foreach ($posts as $post) {
            Post::query()->updateOrCreate(
                ['slug' => $post['slug']],
                $post
            );
        }
    }
}
