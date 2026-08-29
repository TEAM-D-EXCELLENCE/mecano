# Stratégie de tests

Décision D19 : **tests obligatoires sur l'API, libres sur le front.**

## Pourquoi ce déséquilibre est volontaire

Le risque n'est pas réparti uniformément.

Côté API vivent les règles qui, si elles cassent, causent des dégâts invisibles : un brouillon servi au public, un dérivé non approuvé affiché, un quota consommé deux fois, une transition de statut interdite acceptée. **Ces bugs ne se voient pas** — le site fonctionne, il est juste faux.

Côté front, un bug se voit : le bouton ne marche pas, la mise en page casse, l'image ne s'affiche pas. Le mécanicien le signale dans la journée. Une couverture de tests y coûterait cher pour un bénéfice bien plus faible.

C'est pour cela que l'effort de test est concentré là où le mode d'échec est silencieux.

---

## `apps/api` — obligatoire

Pest. **Une PR dont les tests échouent ne peut pas être fusionnée**, à partir de M1.

### Le minimum par endpoint

Chaque endpoint a au moins deux tests :

1. **Cas nominal** : entrée valide → statut attendu, forme de réponse attendue.
2. **Accès refusé** : sans authentification, ou sur une ressource interdite → `401` ou `403`.

Un endpoint d'écriture a en plus un test de validation (`422`, avec les champs en erreur).

```php
it('renvoie le catalogue des véhicules disponibles', function () {
    Car::factory()->count(3)->available()->create();
    Car::factory()->draft()->create();
    Car::factory()->sold()->create();

    getJson('/api/v1/cars')
        ->assertOk()
        ->assertJsonCount(3, 'data')          // ni le brouillon, ni le vendu
        ->assertJsonStructure(['data' => [['id','slug','brand','price_xaf','photos']]]);
});

it('refuse la création sans authentification', function () {
    postJson('/api/v1/admin/cars', [])->assertUnauthorized();
});
```

### Les invariants qui doivent avoir leur test

Ce sont les règles dont la violation est silencieuse. Chacune a un test nommé explicitement :

| Invariant | Ce que ça évite |
|---|---|
| Aucun endpoint public ne renvoie un `draft` | Une annonce non finie visible du public |
| Aucun endpoint public ne renvoie un dérivé non approuvé | Une photo ratée publiée sans validation |
| Une annonce vendue reste accessible, sans `whatsapp_url` | Un client contacte le garage pour un véhicule parti |
| Une annonce sans photo principale ne peut pas passer en `available` | Une fiche sans image dans le catalogue |
| Une seule photo principale par annonce | Affichage indéterminé |
| Maximum 2 vidéos par annonce | Dépassement de la règle du CDC §3.1 |
| Une transition de statut interdite est refusée | Retour d'une annonce en `draft` après publication |
| Un quota épuisé refuse en `409` **avant** tout appel réseau | Crédit perdu, coût inutile |
| Une double soumission d'amélioration ne consomme qu'un crédit | Quota gaspillé |
| Un média non confirmé n'est jamais renvoyé | Fichier fantôme dans une galerie |
| Une écriture publique invalide les bons tags | **Page périmée — le bug le plus difficile à repérer** |
| Aucune réponse ne contient `password` ni de secret | Fuite |

Un test de non-régression sur un bug corrigé est **obligatoire** : c'est la seule garantie qu'il ne revienne pas.

### Règles

- **Aucun appel réseau.** Cloudinary, R2 et remove.bg passent par leurs implémentations factices. Un test qui appelle le réseau est refusé : il est lent et instable.
- Base de test dédiée, `RefreshDatabase`.
- `Model::preventLazyLoading()` actif : **une requête N+1 fait échouer le test**.
- Une factory par modèle, avec des états nommés (`available()`, `sold()`, `draft()`, `withPhotos(8)`).
- Nom de test descriptif, en français, décrivant le comportement — pas la méthode.

### Ce qu'on ne teste pas

Les accesseurs triviaux, la configuration du framework, les migrations, les getters. Un test qui ne peut échouer que si Laravel est cassé n'apporte rien.

---

## `apps/web` et `apps/admin` — libre, avec un socle

Aucune obligation de couverture. Trois choses sont néanmoins testées, parce qu'elles cassent silencieusement :

### 1. La logique pure — Vitest

`lib/format.ts` et `lib/seo.ts`. Ce sont les seuls endroits du front à contenir de la logique, et une erreur y est invisible en revue.

```ts
describe('formatPriceXaf', () => {
  it('formate avec des séparateurs de milliers', () => {
    expect(formatPriceXaf(4500000)).toBe('4 500 000 FCFA')
  })
})
```

### 2. Les types — `tsc --noEmit`

Bloque la PR. Comme les types viennent du contrat, une incompatibilité avec l'API est détectée à la compilation plutôt qu'en production. **C'est le vrai filet de sécurité du front**, et il ne coûte rien à écrire.

### 3. Le budget de performance — Lighthouse CI

Sur les aperçus Vercel, à partir de M1. Un dépassement des plafonds de [07 — Performance et SEO](../01-architecture/07-performance-seo-pwa.md#budget-de-performance) fait échouer la PR.

### Tests de bout en bout

**Pas en V1.** Playwright sur les parcours critiques serait utile, mais l'effort est mieux investi ailleurs à ce stade. À reconsidérer si des régressions répétées apparaissent sur le même parcours.

---

## Conformité au contrat — dès M1

C'est ce qui rend « `openapi.yaml` source de vérité » vrai et pas décoratif.

```php
it('respecte le schéma du contrat', function () {
    Car::factory()->available()->withPhotos(3)->create();

    $response = getJson('/api/v1/cars');

    expect($response->json())->toMatchOpenApiSchema('/cars', 'get', 200);
});
```

Un `assertMatchesOpenApiSchema` maison, adossé à `league/openapi-psr7-validator`, appliqué aux endpoints principaux.

**C'est le seul des garde-fous que le responsable architecture accepte d'abandonner** s'il coûte trop cher à mettre en place. Mais alors la décision D05 perd sa garantie : le contrat pourrait dériver du code sans que rien ne le signale.

---

## Ce qui bloque une PR

| Contrôle | Depuis | Portée |
|---|---|---|
| Pint | M0 | `apps/api` |
| Larastan niveau 6 | M0 | `apps/api` |
| ESLint, Prettier | M0 | apps Next |
| `tsc --noEmit` | M0 | apps Next |
| Format des commits | M0 | tout |
| Pest | **M1** | `apps/api` |
| Conformité au contrat | **M1** | `apps/api` |
| Vitest | **M1** | `lib/` des apps Next |
| Lighthouse CI | **M1** | `apps/web` |

Rien de tout cela n'est actif en M0 hors qualité de code et format de commits : il n'y a rien à tester avant qu'il y ait du code.

---

## Vérification manuelle par jalon

Certaines choses ne se testent pas automatiquement pour un coût raisonnable. Elles sont vérifiées à la main, à chaque fin de jalon, et la liste est cochée dans la PR de clôture :

| Jalon | À vérifier à la main |
|---|---|
| M0 | Connexion et déconnexion. Cookie httpOnly effectif (invisible dans la console). `noindex` présent sur toutes les réponses admin |
| M1 | **Je publie une annonce, la page publique est à jour en moins de 10 s.** `curl` sur la fiche montre le prix dans l'HTML. Le bouton WhatsApp ouvre la bonne conversation, avec le bon message. Upload de 8 photos depuis un téléphone |
| M2 | Un article s'affiche correctement. `sitemap.xml` complet. JSON-LD validé par l'outil de test de Google |
| M3 | Upload d'une vidéo de 150 Mo sur connexion mobile. Avant/après visible. Quota décrémenté, bouton désactivé à l'épuisement |
| M4 | Installation sur Android et iOS. Catalogue consultable hors connexion. Aucune réponse admin dans le cache du service worker |

La ligne de M1 en gras est la plus importante du document : c'est le test de la chaîne complète, celle qui traverse les trois applications et le webhook.
