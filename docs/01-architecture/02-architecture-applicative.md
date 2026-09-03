# 02 — Architecture applicative

Comment chaque application est organisée en interne, et quelle règle de dépendance elle respecte.

---

## `apps/api` — Laravel 13

### Règle fondatrice

**L'API ne rend jamais de HTML.** Pas de Blade, pas de `resources/views`, pas de route `web.php` autre que le strict nécessaire (santé, éventuellement Telescope en local). Le dossier `resources/views` est supprimé.

### Découpage en couches

```
   HTTP          Controllers, Requests, Resources, Middleware
     ↓           « traduire du HTTP en intention métier, et l'inverse »
   Métier        Actions, Services de domaine, Policies, Enums
     ↓           « les règles du garage, sans savoir qu'il existe du HTTP »
   Données       Models Eloquent, Query builders, Migrations
     ↓
   Externe       Contrats + implémentations (Cloudinary, remove.bg, Revalidation)
```

**Règle de dépendance : une couche ne connaît que celle du dessous.** Un `Action` ne reçoit jamais une `Request`, il reçoit un objet de données. Un `Model` n'appelle jamais Cloudinary.

### Arborescence

```
apps/api/
├── app/
│   ├── Actions/                  Une action = un cas d'usage, une classe, une méthode publique
│   │   ├── Cars/                 CreateCar, UpdateCar, ChangeCarStatus, ArchiveCar
│   │   ├── Media/                ConfirmUpload, ReorderMedia, SetMainPhoto, DeleteMedia
│   │   ├── Enhancements/         RequestEnhancement, ApproveEnhancement
│   │   ├── Services/
│   │   └── Posts/
│   ├── Data/                     Objets de transfert immuables (readonly classes)
│   │   ├── CarData.php
│   │   └── MediaUploadData.php
│   ├── Enums/                    CarStatus, FuelType, TransmissionType, VehicleCondition,
│   │                             MediaKind, MediaRole, MediaProvider, EnhancementType,
│   │                             EnhancementStatus, PostStatus, CarEventType
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/Public/    CarController, ServiceController, PostController, EventController
│   │   │   ├── Api/V1/Auth/      LoginController, LogoutController, MeController
│   │   │   └── Api/V1/Admin/     CarController, MediaController, EnhancementController,
│   │   │                         ServiceController, PostController, BrandController,
│   │   │                         QuotaController, DashboardController
│   │   ├── Requests/             Une classe par écriture. Toute la validation vit ici
│   │   ├── Resources/            Une classe par forme de réponse. Voir la règle ci-dessous
│   │   │   ├── Public/           CarListResource, CarDetailResource, ServiceResource, PostResource
│   │   │   └── Admin/            AdminCarResource, MediaResource, EnhancementResource
│   │   └── Middleware/
│   ├── Jobs/                     GenerateDerivatives, RunEnhancement, RevalidateFrontend,
│   │                             PurgeOrphanUploads
│   ├── Models/                   Car, Brand, Media, Photo, Video, MediaEnhancement,
│   │                             Service, Post, CarEvent, IntegrationQuota, Setting, User
│   ├── Policies/                 CarPolicy, MediaPolicy, PostPolicy, ServicePolicy
│   ├── Queries/                  CarCatalogQuery — filtres et tri du catalogue public
│   └── Support/
│       ├── Contracts/            ImageStorage, VideoStorage, BackgroundRemover, FrontendRevalidator
│       └── Integrations/         CloudinaryImageStorage, CloudinaryVideoStorage,
│                                 RemoveBgBackgroundRemover, NextRevalidator, FakeAdapters
├── database/
│   ├── migrations/
│   ├── factories/                Obligatoires : le front s'appuie dessus pour les données d'exemple
│   └── seeders/                  BrandSeeder (référentiel), DemoSeeder (jeu de démonstration)
├── routes/
│   ├── api.php                   Uniquement des routes API, groupées par version
│   └── console.php
└── tests/
    ├── Feature/Api/V1/           Un fichier par endpoint
    └── Unit/
```

### Deux règles de code qui comptent plus que les autres

**1. Modèles métier distincts sur une table unique.** `media` est une seule table, mais on manipule `Photo` et `Video`, deux modèles Eloquent avec un `kind` fixé automatiquement :

```php
// app/Models/Photo.php
class Photo extends Media
{
    protected static function booted(): void
    {
        static::addGlobalScope('photo', fn ($q) => $q->where('kind', MediaKind::Photo));
        static::creating(fn (self $m) => $m->kind = MediaKind::Photo);
    }
}
```

Bénéfice : `$car->photos` et `$car->videos` sont deux relations distinctes, avec leurs propres règles (deux vidéos maximum, exactement une photo principale) et leurs propres ressources API. Le front voit deux ressources claires, la base reste simple. C'est la décision D10.

**2. Deux familles de ressources, publiques et admin.** Une ressource publique ne doit **jamais** pouvoir laisser fuiter un champ interne. On ne partage pas de classe entre `Public/` et `Admin/`, même au prix d'un peu de duplication : c'est de la duplication voulue, elle protège une frontière de sécurité.

| | `Public/CarDetailResource` | `Admin/AdminCarResource` |
|---|---|---|
| Brouillons visibles | non, jamais | oui |
| `views_count`, `whatsapp_clicks_count` | non | oui |
| Dérivés non approuvés | non | oui |
| `deleted_at`, notes internes | non | oui |
| `whatsapp_url` prêt à l'emploi | oui | non |

### Enums, pas de chaînes libres

Tout état est un `enum` PHP 8 adossé à une chaîne, et **la même valeur littérale apparaît dans `openapi.yaml`**. Si les deux divergent, la CI de conformité au schéma le détecte (voir [tests](../02-conventions/tests.md)).

### Intégrations derrière un contrat

Aucune classe métier ne connaît Cloudinary. Elle connaît `ImageStorage` :

```php
interface ImageStorage {
    public function signedUploadParams(string $folder): array;
    public function derivativeUrl(string $key, ImageTransform $t): string;
    public function delete(string $key): void;
}
```

Trois bénéfices : les tests utilisent une implémentation factice sans appel réseau, un changement de fournisseur ne touche pas le métier, et le mode dégradé (fournisseur indisponible) est explicite. Voir [05 — Intégrations](05-integrations-externes.md).

---

## `apps/web` — vitrine publique

### Règle fondatrice

**Tout ce qui doit être indexé par Google est rendu côté serveur.** Un composant client (`"use client"`) est réservé à l'interactivité : filtres, galerie, lecteur vidéo, menu.

### Arborescence

```
apps/web/
├── app/
│   ├── layout.tsx                   Coquille, polices, thème, données structurées globales
│   ├── page.tsx                     Accueil : mise en avant + derniers véhicules
│   ├── voitures/
│   │   ├── page.tsx                 Catalogue. Filtres portés par l'URL (voir ci-dessous)
│   │   └── [slug]/
│   │       ├── page.tsx             Fiche véhicule — SSR + generateMetadata + JSON-LD
│   │       └── opengraph-image.tsx  Image de partage générée
│   ├── services/page.tsx
│   ├── blog/
│   │   ├── page.tsx
│   │   └── [slug]/page.tsx
│   ├── sitemap.ts                   Généré depuis l'API
│   ├── robots.ts
│   └── api/
│       ├── revalidate/route.ts      Reçoit le webhook signé de Laravel
│       └── track/route.ts           Relaie les événements de vue et de clic WhatsApp
├── components/
│   ├── ui/                          shadcn/ui — composants générés, non modifiés à la main
│   ├── car/                         CarCard, CarGallery, CarSpecs, SoldBadge, WhatsAppButton
│   └── layout/                      Header, Footer, MobileNav
├── lib/
│   ├── api/                         Client HTTP + types générés depuis ../../openapi.yaml
│   ├── format.ts                    formatPriceXaf, formatMileage, formatYear
│   └── seo.ts                       Constructeurs JSON-LD
└── types/api.d.ts                   GÉNÉRÉ — jamais édité à la main
```

### Les filtres vivent dans l'URL

`/voitures?marque=toyota&prix_max=5000000&annee_min=2015` est rendu côté serveur, indexable, partageable, et fonctionne sans JavaScript. Un état de filtre stocké dans un `useState` serait invisible de Google et casserait le bouton retour du navigateur. C'est une règle, pas une préférence.

### Stratégie de cache

| Route | Stratégie | Tag de revalidation |
|---|---|---|
| `/` | ISR, revalidation par webhook | `cars`, `home` |
| `/voitures` | ISR par combinaison de filtres | `cars` |
| `/voitures/[slug]` | ISR | `car:{slug}`, `cars` |
| `/services` | ISR | `services` |
| `/blog`, `/blog/[slug]` | ISR | `posts`, `post:{slug}` |
| `/sitemap.xml` | ISR | `cars`, `posts`, `services` |
| `/api/*` | jamais en cache | — |

Filet de sécurité : chaque route porte aussi un `revalidate` d'une heure, pour qu'un webhook perdu ne laisse pas une page périmée indéfiniment.

---

## `apps/admin` — backoffice

### Règle fondatrice

**Le code React ne voit jamais le jeton d'authentification.** Il appelle ses propres route handlers Next, qui lisent le jeton dans un cookie httpOnly et le retransmettent en `Bearer` à Laravel. C'est le BFF, décision D04.

```
Navigateur ──fetch('/bff/cars')──▶ Route handler Next ──Bearer──▶ mecano-api.duckdns.org
              (cookie httpOnly,                        (jeton lu côté
               inaccessible au JS)                      serveur uniquement)
```

### Arborescence

```
apps/admin/
├── app/
│   ├── login/page.tsx
│   ├── (dashboard)/
│   │   ├── layout.tsx               Garde d'authentification côté serveur
│   │   ├── page.tsx                 Tableau de bord (M4)
│   │   ├── annonces/
│   │   │   ├── page.tsx             Liste, recherche, filtre par statut
│   │   │   ├── nouvelle/page.tsx
│   │   │   └── [id]/
│   │   │       ├── page.tsx         Formulaire d'édition
│   │   │       └── medias/page.tsx  Gestionnaire de médias (le plus gros écran du projet)
│   │   ├── services/
│   │   ├── articles/
│   │   └── reglages/                Numéro WhatsApp, logo, quotas
│   └── bff/[...path]/route.ts       Proxy authentifié vers l'API
├── components/
│   ├── ui/                          shadcn/ui
│   ├── media/                       Uploader, DropZone, EnhancePanel, BeforeAfter, QuotaBadge
│   └── forms/                       CarForm, ServiceForm, PostForm
├── lib/
│   ├── auth.ts                      Lecture/écriture du cookie, garde serveur
│   ├── api/                         Client typé (via le BFF)
│   └── upload.ts                    Upload signé vers Cloudinary
└── types/api.d.ts                   GÉNÉRÉ
```

### Le backoffice n'est jamais indexé

`robots.txt` interdisant tout, en-tête `X-Robots-Tag: noindex, nofollow` sur chaque réponse, aucun lien depuis la vitrine. Voir [06 — Sécurité](06-securite.md).

---

## Le contrat, à la racine

```
openapi.yaml          ← source de vérité, propriété du responsable architecture
```

Chaque app Next génère ses types localement, il n'y a **aucun paquet partagé** (décision D03) :

```bash
npx openapi-typescript ../../openapi.yaml -o types/api.d.ts
```

Cette commande tourne en `postinstall` et en CI. Un `types/api.d.ts` modifié à la main est un motif de refus de PR : il serait écrasé au prochain build.

**Pourquoi accepter cette duplication.** Un paquet partagé imposerait un workspace et un ordre de construction entre les trois apps ; il ferait aussi de chaque changement de contrat un changement à trois déploiements couplés. Avec la génération locale, `web` et `admin` sont réellement indépendants — c'est ce qui est recherché. Le coût réel se limite à une commande dupliquée dans deux `package.json`.

## Ce qu'on ne fait pas, volontairement

| Non retenu | Pourquoi |
|---|---|
| Architecture hexagonale complète côté API | Sur-ingénierie pour ce volume. La couche `Actions` + contrats d'intégration suffit |
| Répertoire par domaine (`app/Domains/Cars/...`) | Onze tables et un seul utilisateur : le découpage horizontal reste plus lisible |
| Client d'état global (Redux, Zustand) côté front | Le serveur est l'état. Les Server Components et les paramètres d'URL couvrent le besoin |
| GraphQL | Un seul consommateur par surface, des besoins connus. REST + OpenAPI est plus simple à contractualiser |
| Micro-services | Deux devs, un garage. Non |
