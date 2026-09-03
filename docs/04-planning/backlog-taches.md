# Backlog des tâches

Une ligne = une tâche = **une branche = une PR**. Si une tâche dépasse 400 lignes de diff, elle se découpe.

Identifiants : `OPS-` infrastructure et architecture · `CTR-` contrat d'API · `BE-` backend · `FE-` frontend · `DOC-` documentation.

Les identifiants sont **stables** : ils sont référencés par la [matrice de traçabilité](../00-contexte/tracabilite-exigences.md) et cités dans les PR.

---

## Préalable

| # | Tâche | Qui | Est. | Dépend de |
|---|---|---|---|---|
| OPS-01 | **Brancher le domaine sur Vercel**, créer les projets `mecano-web` et `mecano-admin` | Archi | 0,5 j | — |
| OPS-02 | Préparer le serveur : PHP 8.4, MySQL 8, Nginx, Composer, Supervisor | BE | 1 j | — |
| OPS-03 | TLS `mecano-api.duckdns.org` + renouvellement automatique | BE | 0,5 j | OPS-02 |
| OPS-04 | Compte Cloudinary, dossiers `dev` / `preview` / `prod` | Archi | 0,5 j | — |
| OPS-06 | Compte remove.bg, **vérifier le plafond réel du plan gratuit** | Archi | 0,25 j | — |
| DEC-01 | **Faire valider les écarts E1 à E7 par le client, par écrit** | Archi | 0,5 j | — |
| DEC-02 | Arbitrer avec le client : démarche Meta lancée maintenant pour la V2 ? | Archi | 0,25 j | DEC-01 |

---

## M0 — Socle

| # | Tâche | Qui | Est. | Dépend de |
|---|---|---|---|---|
| OPS-10 | Squelette du monorepo, `.editorconfig`, `.gitignore`, CODEOWNERS, gabarit de PR | Archi | 0,5 j | — |
| OPS-11 | CI : vérification du format des commits et du titre de PR | Archi | 0,5 j | OPS-10 |
| OPS-12 | CI : Pint + Larastan 6 sur `apps/api`, déclenchée par chemin | Archi | 0,5 j | OPS-10 |
| OPS-13 | CI : ESLint + Prettier + `tsc --noEmit` sur les apps Next | Archi | 0,5 j | OPS-10 |
| **CTR-01** | **Contrat : `/health`, `/auth/login`, `/auth/logout`, `/auth/me`** | Archi | 1 j | OPS-10 |
| BE-01 | Initialiser Laravel 13, MySQL, `.env.example`, structure des dossiers | BE | 1 j | OPS-10 |
| BE-02 | Migration `users` + seeder du compte mécanicien | BE | 0,5 j | BE-01 |
| BE-03 | `GET /health` : base, file d'attente, fournisseurs | BE | 0,5 j | BE-01 |
| BE-04 | Sanctum : `/auth/login` avec limitation de débit double, jeton unique actif | BE | 1,5 j | BE-02, CTR-01 |
| BE-05 | `/auth/logout`, `/auth/me`, gestionnaire d'exceptions au format unique | BE | 1 j | BE-04 |
| BE-06 | CORS sans joker, en-têtes de sécurité, `APP_DEBUG=false` en production | BE | 0,5 j | BE-01 |
| FE-01 | Initialiser `apps/web` : Next, Tailwind, jetons, shadcn, polices | FE | 1 j | OPS-10 |
| FE-02 | `apps/web` : coquille, en-tête, pied de page, page d'accueil vide, déploiement | FE | 1 j | FE-01, OPS-01 |
| FE-03 | Initialiser `apps/admin` + génération des types depuis le contrat | FE | 0,5 j | CTR-01 |
| FE-04 | **`apps/admin` : BFF `bff/[...path]`, cookie httpOnly, garde serveur** | FE | 1,5 j | FE-03 |
| FE-05 | `apps/admin` : page de connexion, déconnexion, `noindex` sur toutes les réponses | FE | 1 j | FE-04 |
| OPS-14 | Premier déploiement de bout en bout + **revue de sécurité M0** | Archi | 0,5 j | BE-05, FE-05 |

---

## M1 — Vendre une voiture (le MVP)

### Contrat

| # | Tâche | Qui | Est. | Dépend de |
|---|---|---|---|---|
| **CTR-02** | **Contrat M1 : marques, catalogue, fiche, événements, CRUD annonces, médias** | Archi | 2 j | M0 |

`CTR-02` débloque **tout** M1 des deux côtés. C'est la tâche la plus rentable du projet : rien ne peut avancer avant, tout avance en parallèle après.

### Backend

| # | Tâche | Qui | Est. | Dépend de |
|---|---|---|---|---|
| BE-07 | Migrations `brands`, `cars` + index du catalogue | BE | 1 j | CTR-02 |
| BE-08 | Migrations `media` (colonne générée + unicité du rôle exclusif), `car_events`, `settings` | BE | 1 j | BE-07 |
| BE-09 | **Factories et seeders réalistes**, cas limites inclus | BE | 1 j | BE-08 |
| BE-10 | Énumérations, modèles `Car`/`Brand`, modèles métier `Photo`/`Video` | BE | 1,5 j | BE-08 |
| BE-11 | `GET /brands` public + `/admin/brands` (liste, création) | BE | 1 j | BE-10 |
| BE-12 | `CarCatalogQuery` : filtres, tri, pagination, exclusion des brouillons | BE | 1,5 j | BE-10 |
| BE-13 | `GET /cars`, `GET /cars/{slug}`, ressources publiques, `whatsapp_url` | BE | 1,5 j | BE-12 |
| BE-14 | `POST /cars/{slug}/events` : IP hachée salée, limitation de débit | BE | 1 j | BE-10 |
| BE-15 | `/admin/cars` : liste, création, lecture, modification, archivage | BE | 2 j | BE-10 |
| BE-16 | `PATCH /admin/cars/{id}/status` : transitions, `is_publishable`, invariant photo principale | BE | 1,5 j | BE-15 |
| BE-17 | Contrats d'intégration + `CloudinaryImageStorage` + implémentations factices | BE | 2 j | BE-10 |
| BE-18 | `POST /admin/media/upload-signature` : signature contraignante, limites | BE | 1,5 j | BE-17 |
| BE-19 | `POST /admin/cars/{id}/media` : confirmation, vérification `HEAD`, rôle exclusif | BE | 1,5 j | BE-18 |
| BE-20 | `PATCH`/`DELETE` média, réordonnancement, photo principale transactionnelle | BE | 1,5 j | BE-19 |
| BE-21 | `GenerateDerivatives` + `PurgeOrphanUploads` planifié | BE | 1 j | BE-19 |
| BE-22 | **`NextRevalidator` + job `RevalidateFrontend` signé HMAC, avec réessais** | BE | 2 j | BE-16 |
| BE-23 | Tests Pest : endpoints publics, invariants de visibilité | BE | 1 j | BE-13 |
| BE-24 | Tests Pest : endpoints admin, transitions, médias | BE | 1 j | BE-20 |
| BE-25 | **Test de conformité au schéma OpenAPI** | BE | 1 j | BE-23 |

### Frontend — vitrine

| # | Tâche | Qui | Est. | Dépend de |
|---|---|---|---|---|
| FE-06 | `lib/api` + types générés + `lib/format.ts` (FCFA, km) + tests Vitest | FE | 1 j | CTR-02 |
| FE-07 | Accueil : mise en avant, derniers véhicules, appel à l'action | FE | 1,5 j | FE-06 |
| FE-08 | **Catalogue : grille, filtres portés par l'URL, pagination, état vide** | FE | 3 j | FE-06 |
| FE-09 | Fiche véhicule : caractéristiques, description, rendu serveur | FE | 1,5 j | FE-06 |
| FE-10 | Galerie photos : composant client, clavier, dimensions réservées | FE | 1,5 j | FE-09 |
| FE-11 | **Bouton WhatsApp** (consomme `whatsapp_url`) + journalisation du clic | FE | 0,5 j | FE-09 |
| FE-12 | Badge Vendu, appel à l'action de remplacement, exclusion des filtres | FE | 0,5 j | FE-09 |
| FE-13 | SEO : `generateMetadata`, JSON-LD `Vehicle`, image OG, `robots.txt` | FE | 2 j | FE-09 |
| FE-14 | Tags ISR + filet `revalidate` + route `api/revalidate` (vérification HMAC) | FE | 1 j | FE-08, FE-09 |
| FE-15 | Route `api/track` relayant les événements | FE | 0,5 j | FE-11 |
| FE-16 | Page 404, page d'erreur, squelettes de chargement | FE | 1 j | FE-08 |

### Frontend — backoffice

| # | Tâche | Qui | Est. | Dépend de |
|---|---|---|---|---|
| FE-17 | Liste des annonces : filtre par statut, recherche, pagination | FE | 1,5 j | CTR-02 |
| FE-18 | Formulaire annonce : `react-hook-form` + `zod`, erreurs `422` par champ | FE | 2,5 j | CTR-02 |
| FE-19 | Changement de statut, bouton Publier désactivé si `is_publishable` est faux | FE | 1 j | FE-18 |
| FE-20 | **Gestionnaire de photos, PR 1/3** : upload signé d'un fichier de bout en bout | FE | 1,5 j | CTR-02 |
| FE-21 | **Gestionnaire de photos, PR 2/3** : multiple, progression, échec par fichier | FE | 1,5 j | FE-20 |
| FE-22 | **Gestionnaire de photos, PR 3/3** : réordonnancement, photo principale, suppression | FE | 1,5 j | FE-21 |

### Clôture

| # | Tâche | Qui | Est. | Dépend de |
|---|---|---|---|---|
| OPS-15 | Lighthouse CI sur les aperçus + plafonds bloquants | Archi | 1 j | FE-13 |
| OPS-16 | **Vérification manuelle M1**, revue de sécurité, déploiement en production | Archi | 1 j | tout M1 |

---

## M2 — Crédibilité

| # | Tâche | Qui | Est. |
|---|---|---|---|
| **CTR-03** | Contrat M2 : services, articles, réglages | Archi | 1 j |
| BE-26 | Migrations `services`, `posts` + factories | BE | 1 j |
| BE-27 | API services : CRUD admin (avec désactivation) + `GET /services` | BE | 1,5 j |
| BE-28 | API articles : CRUD admin + `GET /posts`, `/posts/{slug}`, rattachement à un service | BE | 2 j |
| BE-29 | API réglages : `GET /settings` public, `PATCH /admin/settings` | BE | 0,5 j |
| BE-30 | Tests M2 + revalidation des tags `services` et `posts` | BE | 1 j |
| FE-23 | Page services publique | FE | 1,5 j |
| FE-24 | Liste et détail des articles, rendu du texte brut en paragraphes | FE | 2 j |
| FE-25 | Page contact, pages légales, fil d'Ariane | FE | 1 j |
| FE-26 | `sitemap.xml`, JSON-LD `AutoRepair` (adresse, horaires) et `BlogPosting` | FE | 1,5 j |
| FE-27 | Backoffice : gestion des services | FE | 1,5 j |
| FE-28 | Backoffice : rédaction d'articles, écran de réglages | FE | 2 j |
| DOC-01 | **Guide d'utilisation du backoffice** — livrable CDC §6 | Archi | 1,5 j |
| OPS-17 | Search Console, vérification du JSON-LD, clôture M2 | Archi | 0,5 j |

---

## M3 — Qualité visuelle

| # | Tâche | Qui | Est. |
|---|---|---|---|
| **CTR-04** | Contrat M3 : améliorations, quotas, vidéos | Archi | 1 j |
| BE-31 | Migrations `media_enhancements`, `integration_quotas` | BE | 0,5 j |
| BE-32 | `CloudinaryVideoStorage` : upload signé, confirmation, diffusion | BE | 2 j |
| BE-33 | `POST /admin/media/{id}/enhance` — amélioration auto et recadrage Cloudinary | BE | 2 j |
| BE-34 | **`RemoveBgBackgroundRemover` : comptage transactionnel, remboursement sur échec** | BE | 2,5 j |
| BE-35 | `POST /admin/enhancements/{id}/approve` : bascule de `published_url` | BE | 1 j |
| BE-36 | `GET /admin/quotas` + `GET /admin/media/{id}/enhancements` | BE | 0,5 j |
| BE-37 | Tests des invariants : quota infranchissable, dérivé non approuvé jamais public | BE | 1,5 j |
| FE-29 | Vitrine : lecteur vidéo habillé, vignette, `preload="none"` | FE | 1,5 j |
| FE-30 | Backoffice : upload vidéo avec progression et reprise | FE | 1,5 j |
| FE-31 | **Backoffice : panneau d'amélioration, comparateur avant/après, interrogation d'état** | FE | 3 j |
| FE-32 | Backoffice : compteur de quota visible **avant** le clic, désactivation à l'épuisement | FE | 1 j |
| OPS-18 | Vérification manuelle M3 (upload 150 Mo sur mobile), clôture | Archi | 0,5 j |

---

## M4 — Confort

| # | Tâche | Qui | Est. |
|---|---|---|---|
| **CTR-05** | Contrat M4 : tableau de bord | Archi | 0,5 j |
| BE-38 | `AggregateCarEvents` nocturne + purge à 12 mois | BE | 1 j |
| BE-39 | `GET /admin/dashboard` : vues, clics, annonces les plus vues, délai moyen de vente | BE | 1,5 j |
| FE-33 | PWA : manifeste, icônes, service worker, cache, page hors ligne | FE | 2 j |
| FE-34 | Invite d'installation après deux visites | FE | 0,5 j |
| FE-35 | Backoffice : écran de tableau de bord | FE | 1,5 j |
| OPS-19 | Vérification PWA Android et iOS, **aucune réponse admin en cache**, clôture V1 | Archi | 0,5 j |

---

## Reporté en V2

| # | Tâche | Prérequis |
|---|---|---|
| V2-01 | Application Meta, permission `pages_manage_posts` | DEC-02, délai Meta |
| V2-02 | Table `social_publications` + contrat | V2-01 accordée |
| V2-03 | `POST /admin/cars/{id}/publish/facebook` + traçabilité | V2-02 |
| V2-04 | Backoffice : bouton Publier sur Facebook, historique | V2-03 |
| V2-05 | Habillage vidéo réencodé | Décision R05, budget |
| V2-06 | Suppression de fond illimitée | Décision R04, budget |
| V2-07 | Éditeur riche pour le blog | Besoin confirmé après usage |
| V2-08 | Multi-utilisateur du backoffice | Décision R06 |

---

## Les cinq tâches à ne pas rater

| # | Pourquoi elle est critique |
|---|---|
| **OPS-01** | Bloque tout. Le faire plus tard impose de reconfigurer cookies, CORS et URL canoniques |
| **CTR-02** | Débloque 30 jours de travail parallèle. Rien n'avance avant elle |
| **BE-09** | Conditionne tout le travail frontend de M1. Des seeders trop propres = une interface qui casse en production |
| **BE-22 + FE-14** | Le webhook de revalidation. Mode d'échec **silencieux** : à tester de bout en bout, pas seulement unitairement |
| **BE-34** | Le comptage du quota. Non transactionnel, il gaspille des crédits sur un plafond de 50 par mois |
