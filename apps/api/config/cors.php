<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS)
|--------------------------------------------------------------------------
|
| La liste des origines est une frontière de sécurité, pas un réglage : elle
| vit dans le code, pas dans l'environnement. La modifier passe donc par une
| relecture, ce qui est précisément l'effet recherché — une variable
| d'environnement laisserait n'importe qui autoriser n'importe quelle origine
| sans que personne ne le voie.
|
| Les origines de développement ne sont ajoutées qu'en dehors de la production.
|
*/

$productionOrigins = [
    // Backoffice
    'https://admin-nine-smoky-13.vercel.app',
    'https://admin.garage.excellenceteam.site',
    // Vitrine publique
    'https://web-mu-three-85.vercel.app',
    'https://webgarage.excellenceteam.site',
];

$localOrigins = [
    'http://localhost:3000',
    'http://localhost:3001',
    'http://127.0.0.1:3000',
    'http://127.0.0.1:3001',
];

$isProduction = env('APP_ENV', 'production') === 'production';

return [

    'paths' => ['api/*', 'up'],

    'allowed_methods' => ['GET', 'POST', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => $isProduction
        ? $productionOrigins
        : array_merge($productionOrigins, $localOrigins),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization', 'Accept', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 86400,

    /*
    | L'API est sans état : elle n'accepte qu'un en-tête `Authorization`, jamais
    | un cookie. C'est le BFF du backoffice qui détient le cookie de session, sur
    | son propre domaine. Autoriser les identifiants ici élargirait la surface
    | d'attaque sans rien apporter (docs/01-architecture/06-securite.md).
    */
    'supports_credentials' => false,

];
