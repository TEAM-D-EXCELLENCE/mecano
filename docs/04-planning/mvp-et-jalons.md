# MVP et jalons

**Pas de deadline** (décision D22). Le découpage se fait par **valeur métier décroissante** : chaque jalon est déployable en production tel quel, et le responsable architecture peut décider d'arrêter après n'importe lequel.

## Le parcours qui justifie tout le projet

> Un acheteur cherche « Toyota Corolla occasion » sur Google → il tombe sur une fiche du garage → il voit de belles photos et un prix → il clique sur WhatsApp → il appelle le mécanicien.

C'est le seul parcours qui compte. **M1 le réalise en entier.** Tous les jalons suivants le crédibilisent ou le confortent, mais aucun n'y ajoute de valeur essentielle.

Corollaire important : **M0 seul ne vaut rien pour le client.** Il ne doit donc pas être présenté comme une livraison, seulement comme une étape technique.

---

## Vue d'ensemble

| Jalon | Nom | Valeur pour le garage | Lots CDC |
|---|---|---|---|
| **M0** | Socle | Aucune. Étape technique | Lot 1 |
| **M1** | **Vendre une voiture** | **Remplace le bouche-à-oreille. C'est le MVP** | Lot 2 (photos), Lot 3 (voitures), Lot 4 |
| **M2** | Crédibilité | Un garage qu'on trouve et en qui on a confiance | Lot 3 (reste) |
| **M3** | Qualité visuelle | Des photos qui vendent, des vidéos qui rassurent | Lot 2 (vidéos), Lot 5 |
| **M4** | Confort | Application installable, et des chiffres pour décider | §2.2 |
| **V2** | — | Facebook, habillage vidéo | Lot 6, Lot 7 |

---

## M0 — Socle

**Objectif :** la chaîne complète fonctionne de bout en bout, avec un écran vide.

### Contenu

| Domaine | Contenu |
|---|---|
| Dépôt | Monorepo, CODEOWNERS, gabarit de PR, `.editorconfig` |
| CI | Format des commits, Pint, Larastan 6, ESLint, Prettier, `tsc --noEmit` |
| Contrat | `openapi.yaml` avec santé et authentification |
| API | Laravel initialisé, MySQL, Sanctum, `users`, migration initiale, `/health`, `/auth/*` |
| `apps/web` | Next initialisé, coquille, jetons Tailwind, shadcn/ui, déployé sur `garage.com` |
| `apps/admin` | Next initialisé, BFF, page de connexion, garde serveur, `noindex`, déployé sur `admin.garage.com` |
| Infra | Domaine branché sur Vercel, serveur préparé, TLS, comptes Cloudinary et R2 créés |

### Critères de sortie

- [ ] Le mécanicien se connecte sur `admin.garage.com` et voit un écran authentifié vide.
- [ ] Il se déconnecte, le cookie est purgé, l'accès est refusé.
- [ ] `garage.com` répond en SSR sur un domaine réel.
- [ ] La CI bloque une PR mal formatée.
- [ ] Le cookie de session est invisible dans la console du navigateur.
- [ ] Aucune réponse admin n'est indexable.

### Ce que M0 ne contient pas

Aucune annonce, aucune photo, aucune page publique de contenu. **Ce n'est pas une livraison client.**

---

## M1 — Vendre une voiture ← **le MVP**

**Objectif :** le mécanicien publie une annonce **seul**, un acheteur la trouve sur Google et le contacte sur WhatsApp.

C'est le seul jalon dont l'absence rendrait le projet inutile. Tout le reste est amélioration.

### Contenu

| Domaine | Contenu |
|---|---|
| Contrat | Marques, catalogue, fiche, événements, CRUD annonces, médias |
| Données | `brands`, `cars`, `media`, `car_events`, `settings` + factories et seeders réalistes |
| API publique | `/brands`, `/cars` avec filtres, `/cars/{slug}`, `/cars/{slug}/events` |
| API admin | CRUD annonces, changement de statut, signature d'upload, confirmation, réordonnancement, photo principale |
| Médias | Upload direct signé vers Cloudinary, dérivés automatiques (`thumb`, `card`, `detail`, `og`) |
| Revalidation | Webhook signé Laravel → Next, avec filet horaire |
| `apps/web` | Accueil, catalogue avec filtres dans l'URL, fiche véhicule, galerie, bouton WhatsApp, badge Vendu, 404 |
| `apps/admin` | Liste des annonces, formulaire de création et d'édition, gestionnaire de photos, changement de statut |
| SEO | `generateMetadata`, JSON-LD `Vehicle`, image Open Graph, `robots.txt` |
| Qualité | Tests Pest bloquants, conformité au contrat, Lighthouse CI |

### Critères de sortie

- [ ] **Le mécanicien crée une annonce complète, avec 8 photos, sans aucune aide.**
- [ ] **Il publie, et la page publique est à jour en moins de 10 secondes.**
- [ ] `curl` sur la fiche montre le prix, le titre et la description dans l'HTML.
- [ ] Les filtres marque / prix / année fonctionnent et produisent une URL partageable.
- [ ] Le bouton WhatsApp ouvre la bonne conversation, avec le bon message et le bon lien.
- [ ] Une annonce vendue reste en ligne avec son badge, hors des filtres par défaut, sans bouton WhatsApp.
- [ ] Un brouillon n'est visible sur aucun endpoint public — vérifié par un test.
- [ ] Lighthouse mobile ≥ 90 en performance et en SEO sur la fiche véhicule.
- [ ] Upload de 8 photos réussi depuis un téléphone, sur connexion mobile.
- [ ] Le site est vérifié dans la Search Console.

### Pourquoi ces éléments sont dans M1 et pas plus tard

- **`car_events` dès M1**, alors que le tableau de bord n'arrive qu'en M4 : sans historique, M4 démarrerait sur des compteurs vides et n'aurait aucune valeur.
- **`settings` dès M1** : le numéro WhatsApp doit être modifiable par le mécanicien sans intervention technique.
- **L'image Open Graph dès M1** : la diffusion sur les réseaux est manuelle en V1 (écart E4), donc l'aperçu du lien collé dans WhatsApp est le seul canal social du projet.

---

## M2 — Crédibilité

**Objectif :** le garage n'est plus seulement un catalogue, c'est un professionnel qu'on trouve et en qui on a confiance.

### Contenu

| Domaine | Contenu |
|---|---|
| Données | `services`, `posts` |
| API | CRUD services, CRUD articles, endpoints publics correspondants, `/settings` |
| `apps/web` | Page services, liste et détail des articles, page contact, pages légales |
| `apps/admin` | Gestion des services (dont désactivation), rédaction d'articles, écran de réglages |
| SEO | `sitemap.xml` complet, JSON-LD `AutoRepair` avec adresse et horaires, `BlogPosting`, fil d'Ariane |
| Livrable | Première version du guide d'utilisation du backoffice (CDC §6) |

### Critères de sortie

- [ ] Le mécanicien crée un service et le voit sur la page publique.
- [ ] Il désactive un service : il disparaît du site sans casser les articles qui y sont rattachés.
- [ ] Il rédige et publie un article, éventuellement rattaché à un service.
- [ ] `sitemap.xml` contient toutes les annonces, services et articles publiés.
- [ ] Le JSON-LD passe l'outil de test des résultats enrichis de Google.
- [ ] Le référencement local fonctionne : le garage apparaît sur une recherche « garage + ville ».
- [ ] Le guide d'utilisation couvre annonces, photos, services et articles.

---

## M3 — Qualité visuelle

**Objectif :** les photos vendent mieux, et les vidéos rassurent l'acheteur.

### Contenu

| Domaine | Contenu |
|---|---|
| Données | `media_enhancements`, `integration_quotas` |
| API | Demande d'amélioration, consultation des dérivés, approbation, quotas |
| Médias | Vidéos sur R2 (upload direct signé), améliorations Cloudinary, suppression de fond remove.bg |
| `apps/web` | Lecteur vidéo habillé, vignette, `preload="none"` |
| `apps/admin` | Panneau d'amélioration, comparateur avant/après, compteur de quota, interrogation d'état |

### Critères de sortie

- [ ] Le mécanicien envoie une vidéo de 150 Mo depuis une connexion mobile, sans échec.
- [ ] Il demande une amélioration, **compare l'avant et l'après**, et approuve ou refuse (CDC §3.2).
- [ ] Une amélioration refusée n'apparaît jamais sur le site public.
- [ ] Le compteur de quota s'affiche **avant** le clic, et le bouton se désactive à l'épuisement.
- [ ] Un échec du fournisseur rend le crédit et affiche un message clair.
- [ ] Le poids des pages n'a pas régressé après l'ajout des vidéos.

### Ce que M3 ne contient pas

**Aucun habillage vidéo réencodé** (écart E5). L'habillage se limite au lecteur du site.

---

## M4 — Confort

**Objectif :** application installable, et des chiffres pour que le mécanicien décide.

### Contenu

| Domaine | Contenu |
|---|---|
| API | `/admin/dashboard`, agrégation nocturne de `car_events` |
| `apps/web` | Manifeste, icônes, service worker, cache hors ligne, page hors connexion, invite d'installation |
| `apps/admin` | Tableau de bord : vues, clics WhatsApp, annonces les plus vues, délai moyen de vente |

### Critères de sortie

- [ ] Installable sur Android et sur iOS.
- [ ] Le catalogue déjà visité s'ouvre hors connexion.
- [ ] **Aucune réponse authentifiée dans le cache du service worker** — vérifié.
- [ ] Une annonce vendue n'est jamais servie comme disponible depuis le cache.
- [ ] Le tableau de bord affiche les clics WhatsApp par annonce, avec un historique réel depuis M1.

---

## V2 — hors périmètre actuel

| Élément | Pourquoi reporté | Prérequis |
|---|---|---|
| Publication Facebook (Lot 6) | Écart E4 — dépendance à une validation Meta de plusieurs semaines | Démarche Meta à lancer dès maintenant si voulue |
| Habillage vidéo réencodé (Lot 7) | Écart E5 — exige un coût récurrent | Accord du client sur le coût, décision R05 |
| Suppression de fond illimitée | Écart E6 — add-on payant | Accord du client, décision R04 |
| Éditeur riche pour le blog | Écart E7 | Besoin confirmé après usage réel |
| Paiement en ligne, publicité payante, application native | Exclus par le CDC §2.3 | — |
| Multi-utilisateur du backoffice | Non demandé | Décision R06 |

---

## Où s'arrêter

Puisqu'il n'y a pas de deadline, la vraie question est : à partir de quand la plateforme est-elle suffisante ?

| Après | Le garage peut… | Ce qui manque |
|---|---|---|
| M0 | rien | tout |
| **M1** | **vendre en ligne, être trouvé sur Google, être contacté** | crédibilité, qualité visuelle |
| M2 | présenter son métier, être trouvé en local | qualité visuelle |
| M3 | montrer des véhicules sous leur meilleur jour | confort |
| M4 | mesurer ce qui marche et arbitrer ses prix | — |

**Recommandation : ne jamais s'arrêter avant M2.** Un catalogue sans page services ni articles ressemble à un site abandonné, ce qui abîme la confiance que M1 cherche à construire. Après M2, l'arrêt est défendable à tout moment.
