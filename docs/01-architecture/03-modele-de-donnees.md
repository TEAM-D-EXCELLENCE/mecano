# 03 — Modèle de données

PostgreSQL managé chez Supabase ([ADR 0010](adr/0010-postgresql-supabase.md)), encodage UTF-8.

> Les tests s'exécutent sur SQLite en mémoire : **aucun test ne touche le moteur de production**. Les différences de moteur — au premier rang desquelles la colonne générée `exclusive_role` ci-dessous — ne sont donc couvertes par rien.
Ce document et les migrations doivent rester synchronisés : **toute PR touchant une migration met ce fichier à jour**.

## Vue d'ensemble

```mermaid
erDiagram
    users ||--o{ posts : "rédige"
    brands ||--o{ cars : "référence"
    cars ||--o{ media : "possède"
    cars ||--o{ car_events : "génère"
    media ||--o{ media_enhancements : "a des dérivés"
    media |o--o| posts : "couverture"
    services ||--o{ posts : "rattachement optionnel"

    users { bigint id PK }
    brands { bigint id PK; string slug UK; string name }
    cars { bigint id PK; string slug UK; bigint brand_id FK; string model; enum status }
    media { bigint id PK; bigint car_id FK; enum kind; enum role; enum provider; string storage_key }
    media_enhancements { bigint id PK; bigint media_id FK; enum type; enum status; timestamp approved_at }
    services { bigint id PK; string slug UK; bool is_active }
    posts { bigint id PK; string slug UK; enum status }
    car_events { bigint id PK; bigint car_id FK; enum type }
    integration_quotas { bigint id PK; string provider; string period }
    settings { string key PK }
```

---

## `users`

Le mécanicien. **Un seul enregistrement en V1** — mais on garde une table plutôt qu'un identifiant en variable d'environnement, pour que le multi-utilisateur (R06) n'exige pas de refonte.

| Colonne | Type | Contrainte |
|---|---|---|
| `id` | bigint unsigned | PK |
| `name` | varchar(120) | |
| `email` | varchar(190) | unique |
| `password` | varchar(255) | haché bcrypt, **jamais** exposé par l'API |
| `email_verified_at`, `remember_token` | | standard Laravel |
| `created_at`, `updated_at` | timestamp | |

Table `personal_access_tokens` : standard Sanctum. Les jetons portent une expiration (voir [06 — Sécurité](06-securite.md)).

---

## `brands` — référentiel fermé des marques

Décision D11. Le mécanicien choisit dans une liste, il ne tape pas de texte libre : c'est ce qui garantit que le filtre par marque du CDC §3.7 fonctionne réellement. Sans référentiel, « Toyota », « toyota » et « TOYOTA » coexistent et cassent le filtre.

| Colonne | Type | Contrainte | Note |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `slug` | varchar(60) | unique | `toyota`, `mercedes-benz`. Sert l'URL `/voitures?marque=toyota` |
| `name` | varchar(80) | | Affiché : `Mercedes-Benz` |
| `logo_url` | varchar(255) | nullable | |
| `position` | smallint unsigned | défaut 0 | Ordre d'affichage dans le filtre |
| `is_active` | boolean | défaut true | Masque une marque du filtre sans casser les annonces existantes |

Alimentée par `BrandSeeder` avec les marques pertinentes pour le marché local. Le mécanicien peut en ajouter depuis le backoffice.

---

## `cars` — les annonces

Table centrale. CDC §3.1.

| Colonne | Type | Contrainte | Note |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `slug` | varchar(180) | unique, index | `toyota-corolla-2018-42`. **Immuable après création** : le SEO en dépend |
| `brand_id` | bigint unsigned | FK → `brands`, restreint | Pas de suppression en cascade : on ne perd pas une annonce en désactivant une marque |
| `model` | varchar(120) | | Texte libre, D11 |
| `year` | smallint unsigned | | 1950 → année courante + 1 |
| `mileage_km` | int unsigned | | Kilométrage. Unité dans le nom, jamais d'ambiguïté |
| `price_xaf` | bigint unsigned | | **FCFA entier, sans sous-unité** (D12). `bigint` car 4 500 000 dépasse vite un `int` sur les gammes hautes |
| `fuel` | enum | | voir énumérations |
| `transmission` | enum | | |
| `color` | varchar(40) | | |
| `condition` | enum | | |
| `description` | text | nullable | |
| `status` | enum | index, défaut `draft` | `draft`, `available`, `reserved`, `sold` |
| `is_featured` | boolean | défaut false | Mise en avant sur l'accueil |
| `published_at` | timestamp | nullable, index | Renseigné au premier passage en `available` |
| `sold_at` | timestamp | nullable | Renseigné au passage en `sold`. Alimente « vendu il y a 2 mois » |
| `views_count` | int unsigned | défaut 0 | Compteur dénormalisé, incrémenté par lot depuis `car_events` |
| `whatsapp_clicks_count` | int unsigned | défaut 0 | Idem. C'est **l'indicateur de succès du projet** |
| `created_at`, `updated_at` | timestamp | | |
| `deleted_at` | timestamp | nullable | Suppression logique — on n'efface jamais un historique de vente |

### Index

```sql
INDEX (status, published_at DESC)          -- le catalogue public, requête la plus fréquente
INDEX (brand_id, status)                   -- filtre par marque
INDEX (price_xaf)                          -- filtre et tri par prix
INDEX (year)                               -- filtre par année
INDEX (status, is_featured, published_at)  -- mise en avant sur l'accueil
UNIQUE (slug)
```

### Règles métier

1. **Le slug est généré une seule fois**, à la création : `{marque}-{modèle}-{année}-{id}`, normalisé. Une correction de faute de frappe sur le modèle ne change pas l'URL. Si le slug devait changer, il faudrait une table de redirections 301 — hors périmètre V1.
2. **Une annonce ne passe en `available` que si elle a au moins une photo** dont le `role` est `main`. Contrainte applicative, vérifiée dans `ChangeCarStatus`, testée.
3. **`sold` reste public** (D14) : la page reste servie avec un badge, le bouton WhatsApp est remplacé par un appel à l'action « chercher un véhicule similaire ». L'annonce est exclue des filtres du catalogue **par défaut**, mais accessible via `?inclure_vendus=1`.
4. `draft` n'est **jamais** servi par un endpoint public. Vérifié par un test dédié pour chaque endpoint public.
5. Transitions de statut autorisées :

```
draft ──▶ available ──▶ reserved ──▶ sold
             ▲             │           │
             └─────────────┘           │
             └─────────────────────────┘   (retour possible : une vente peut échouer)
```
`draft` n'est jamais réatteignable depuis un autre statut : une annonce publiée l'a été.

---

## `media` — photos et vidéos

Table unique, modèles métier distincts (D10). Voir [02 — Architecture applicative](02-architecture-applicative.md#deux-règles-de-code-qui-comptent-plus-que-les-autres).

| Colonne | Type | Contrainte | Note |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `car_id` | bigint unsigned | FK → `cars`, cascade | |
| `kind` | enum | index | `photo`, `video` |
| `role` | enum | | `main`, `gallery`, `video_interior`, `video_exterior` |
| `provider` | enum | | `cloudinary` (photos), `r2` (vidéos) |
| `storage_key` | varchar(255) | | `public_id` Cloudinary, ou clé d'objet R2 |
| `url` | varchar(500) | | URL de diffusion de l'original |
| `published_url` | varchar(500) | nullable | La version réellement servie au public : l'original, ou un dérivé approuvé |
| `mime` | varchar(60) | | |
| `bytes` | int unsigned | | |
| `width`, `height` | smallint unsigned | nullable | Renseignés pour les photos. **Obligatoires côté front** pour réserver la place et éviter le décalage de mise en page |
| `duration_s` | smallint unsigned | nullable | Vidéos |
| `position` | smallint unsigned | défaut 0 | Ordre dans la galerie |
| `alt` | varchar(200) | nullable | Texte alternatif. Généré par défaut : « Toyota Corolla 2018 — photo 3 » |
| `confirmed_at` | timestamp | nullable | Renseigné à la confirmation d'upload. `null` = fichier orphelin, purgé après 24 h |
| `created_at`, `updated_at` | timestamp | | |

### Index et contraintes

```sql
INDEX (car_id, kind, position)
INDEX (confirmed_at)               -- purge des orphelins
UNIQUE (car_id, role) WHERE role IN ('main','video_interior','video_exterior')
```

L'implémentation actuelle repose sur une colonne générée, héritée du choix MySQL initial :

```sql
exclusive_role VARCHAR(30) GENERATED ALWAYS AS
  (CASE WHEN role IN ('main','video_interior','video_exterior') THEN role ELSE NULL END) STORED,
UNIQUE KEY uq_car_exclusive_role (car_id, exclusive_role)
```

La base garantit ainsi : **une seule photo principale, une seule vidéo intérieur, une seule vidéo extérieur par annonce**. Ce n'est pas seulement une règle applicative — c'est exactement la contrainte du CDC §3.1, et elle vit là où elle ne peut pas être contournée.

### Règles métier

1. Maximum **2 vidéos** par annonce (CDC §3.1), garanti par l'unicité des rôles vidéo.
2. Désigner une nouvelle photo principale rétrograde l'ancienne en `gallery`, dans une transaction.
3. `published_url` est ce que le public reçoit. Tant qu'aucun dérivé n'est approuvé, il vaut `url`.
4. Un média non confirmé n'est jamais renvoyé par l'API. Voir [04 — Pipeline médias](04-pipeline-medias.md).

---

## `media_enhancements` — les dérivés et leur validation

C'est cette table qui satisfait « visualiser la version originale et la version améliorée **avant publication** » (CDC §3.2).

| Colonne | Type | Contrainte | Note |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `media_id` | bigint unsigned | FK → `media`, cascade | |
| `type` | enum | | `auto_improve`, `smart_crop`, `background_removal` |
| `provider` | enum | | `cloudinary`, `removebg` |
| `status` | enum | index | `pending`, `processing`, `ready`, `failed`, `approved` |
| `params` | json | nullable | Paramètres de la transformation, pour rejouer à l'identique |
| `result_key` | varchar(255) | nullable | Clé de stockage du dérivé |
| `result_url` | varchar(500) | nullable | URL du dérivé, pour l'aperçu avant/après |
| `error` | text | nullable | Message d'échec, affiché au mécanicien |
| `cost_units` | smallint unsigned | défaut 0 | Unités de quota consommées. Alimente `integration_quotas` |
| `approved_at` | timestamp | nullable | **Approuvé = devient `media.published_url`** |
| `created_at`, `updated_at` | timestamp | | |

```sql
INDEX (media_id, status)
INDEX (status, created_at)   -- reprise des jobs en échec
```

**Invariant central : un dérivé non approuvé n'est jamais servi au public.** L'original reste intact, quoi qu'il arrive. C'est ce qui rend l'amélioration sans risque : le pire cas est un dérivé raté qu'on n'approuve pas.

---

## `integration_quotas` — les compteurs d'API externes

Décision D13. Le plan gratuit remove.bg est plafonné (~50 appels/mois), et le CDC §4 impose de rester à coût minimal : il faut donc compter, et le montrer au mécanicien.

| Colonne | Type | Contrainte | Note |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `provider` | varchar(40) | | `removebg` |
| `period` | char(7) | | `2026-09`. Mois calendaire |
| `used` | smallint unsigned | défaut 0 | |
| `limit` | smallint unsigned | | Recopié depuis la configuration, pour garder l'historique si le plan change |
| `updated_at` | timestamp | | |

```sql
UNIQUE (provider, period)
```

L'incrément se fait dans la **même transaction** que la création de l'amélioration, et un appel qui dépasserait la limite est refusé en `409 Conflict` avant tout appel réseau. Le backoffice affiche « 12 / 50 ce mois-ci ».

---

## `services`

CDC §3.3.

| Colonne | Type | Contrainte | Note |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `slug` | varchar(120) | unique | |
| `title` | varchar(150) | | |
| `excerpt` | varchar(300) | nullable | Texte de la carte |
| `description` | text | nullable | |
| `icon` | varchar(60) | nullable | Nom d'icône Lucide, choisi dans une liste fermée côté backoffice |
| `price_from_xaf` | bigint unsigned | nullable | « À partir de ». `null` = « sur devis » |
| `is_active` | boolean | index, défaut true | **Désactivation, jamais suppression** (CDC §3.3) : un article rattaché ne doit pas devenir orphelin |
| `position` | smallint unsigned | défaut 0 | |
| `created_at`, `updated_at` | timestamp | | |

---

## `posts` — les articles de blog

CDC §3.4. Éditeur volontairement minimal (D16, écart E7).

| Colonne | Type | Contrainte | Note |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `slug` | varchar(180) | unique | Immuable |
| `title` | varchar(200) | | |
| `excerpt` | varchar(300) | nullable | |
| `body` | text | | **Texte brut.** Les sauts de ligne font les paragraphes. Aucun HTML n'est ni stocké ni rendu |
| `cover_media_id` | bigint unsigned | FK → `media`, nullable, mise à null | Image de couverture |
| `service_id` | bigint unsigned | FK → `services`, nullable, mise à null | **Rattachement à un service, exigé par le CDC §3.4** |
| `author_id` | bigint unsigned | FK → `users`, restreint | |
| `status` | enum | index | `draft`, `published` |
| `published_at` | timestamp | nullable, index | |
| `created_at`, `updated_at` | timestamp | | |

```sql
INDEX (status, published_at DESC)
INDEX (service_id, status)
```

`body` en texte brut est un choix de sécurité autant que de simplicité : rien à assainir, aucune injection HTML possible depuis le backoffice.

---

## `car_events` — mesure d'audience

Alimente le tableau de bord de M4. **À poser dès M1** : sans historique, le tableau de bord de M4 démarre vide et n'a aucune valeur.

| Colonne | Type | Contrainte | Note |
|---|---|---|---|
| `id` | bigint unsigned | PK | |
| `car_id` | bigint unsigned | FK → `cars`, cascade | |
| `type` | enum | index | `view`, `whatsapp_click` |
| `ip_hash` | char(64) | nullable | SHA-256 de l'IP **+ un sel côté serveur**. Permet de dédoublonner sans conserver de donnée personnelle |
| `referer` | varchar(255) | nullable | Domaine seul, jamais l'URL complète |
| `created_at` | timestamp | index | |

```sql
INDEX (car_id, type, created_at)
INDEX (created_at)               -- purge : rétention 12 mois
```

Une commande planifiée agrège les compteurs vers `cars.views_count` / `whatsapp_clicks_count` et purge au-delà de 12 mois.

**Sur la vie privée :** on ne stocke aucune IP en clair, aucun identifiant de navigateur, aucun cookie de suivi. Le comptage est agrégé et anonyme, ce qui évite d'avoir à demander un consentement.

---

## `settings`

Configuration éditable par le mécanicien, sans redéploiement.

| Colonne | Type | Note |
|---|---|---|
| `key` | varchar(80) | PK. `whatsapp_number`, `garage_name`, `logo_url`, `hero_title`, `address`, `opening_hours` |
| `value` | json | |
| `updated_at` | timestamp | |

Le numéro WhatsApp est ici et non dans un fichier de configuration : c'est une donnée métier, le mécanicien doit pouvoir en changer seul.

---

## Reporté en V2

`social_publications` — traçabilité des publications Facebook (CDC §3.5) : `car_id`, `platform`, `external_id`, `status`, `payload`, `error`, `published_at`. **Ne pas créer en V1** : une table vide est de la dette, pas de l'anticipation.

---

## Énumérations

Chaque valeur ci-dessous apparaît **à l'identique** dans `app/Enums/` et dans `openapi.yaml`. La CI de conformité au schéma vérifie qu'elles ne divergent pas.

| Enum | Valeurs |
|---|---|
| `CarStatus` | `draft`, `available`, `reserved`, `sold` |
| `FuelType` | `essence`, `diesel`, `hybride`, `electrique`, `gpl` |
| `TransmissionType` | `manuelle`, `automatique` |
| `VehicleCondition` | `neuf`, `excellent`, `bon`, `moyen` |
| `MediaKind` | `photo`, `video` |
| `MediaRole` | `main`, `gallery`, `video_interior`, `video_exterior` |
| `MediaProvider` | `cloudinary`, `r2` |
| `EnhancementType` | `auto_improve`, `smart_crop`, `background_removal` |
| `EnhancementStatus` | `pending`, `processing`, `ready`, `failed`, `approved` |
| `PostStatus` | `draft`, `published` |
| `CarEventType` | `view`, `whatsapp_click` |

Les valeurs sont en français car elles sont affichées au mécanicien et apparaissent dans les URL publiques (`?carburant=diesel`). Les noms de colonnes et de tables restent en anglais, comme le reste du code.

---

## Règles de migration

1. **Une migration fusionnée sur `main` ne se modifie plus jamais.** On en ajoute une nouvelle. Sans exception.
2. Une migration doit avoir un `down()` fonctionnel, ou déclarer explicitement pourquoi elle est irréversible.
3. Toute nouvelle colonne est soit nullable, soit dotée d'une valeur par défaut : une migration ne doit jamais échouer sur des données existantes.
4. Les clés étrangères sont explicites, avec un comportement de suppression **choisi et justifié** : `cascade` pour les médias d'une annonce, `restrict` pour la marque d'une annonce, `set null` pour le service d'un article.
5. Une PR ajoutant une table ou une colonne met à jour **ce document** et la `factory` correspondante — le front en dépend pour ses données d'exemple.
