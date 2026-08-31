# Registre des décisions

Toutes les décisions structurantes du projet, dans l'ordre où elles ont été prises.
Ce registre est l'**index** ; le raisonnement détaillé de chaque décision majeure vit dans un [ADR](../01-architecture/adr/).

Arbitre : responsable architecture (b.brun@ongsenam.com). Date de la session d'arbitrage : **25 août 2026**.

## Statut des décisions

| # | Décision | Statut | ADR |
|---|---|---|---|
| D01 | Backend = **API JSON uniquement**. Aucune vue, aucun rendu HTML côté Laravel | Arbitrée | [0001](../01-architecture/adr/0001-api-json-decouplee.md) |
| D02 | Frontend en **React / Next.js**, avec **SSR + ISR** pour l'indexation du catalogue | Arbitrée | [0002](../01-architecture/adr/0002-nextjs-ssr-isr.md) |
| D03 | **Monorepo** `apps/api` + `apps/web` + `apps/admin`, **rien de partagé** entre les apps | Arbitrée | [0003](../01-architecture/adr/0003-monorepo-trois-apps.md) |
| D04 | Auth **Sanctum Bearer**, jeton détenu par un **BFF Next** en cookie httpOnly. Backoffice sur hôte séparé et `noindex` | Arbitrée | [0004](../01-architecture/adr/0004-auth-bearer-bff.md) |
| D05 | `openapi.yaml` **écrit à la main** = source de vérité. Types TS générés dans chaque app | Arbitrée | [0005](../01-architecture/adr/0005-openapi-source-de-verite.md) |
| D06 | Photos sur **Cloudinary**, vidéos sur **Cloudflare R2**. Rien de lourd sur le serveur | Arbitrée | [0006](../01-architecture/adr/0006-medias-cloudinary-r2.md) |
| D07 | **Upload direct signé** depuis le navigateur, puis confirmation à l'API | Arbitrée | [0007](../01-architecture/adr/0007-upload-direct-signe.md) |
| D08 | **Lot 6 (Facebook) et Lot 7 (habillage vidéo) hors V1** | Arbitrée | [0008](../01-architecture/adr/0008-facebook-video-hors-v1.md) |
| D09 | Revalidation ISR par **webhook signé** Laravel → Next | Arbitrée | [0009](../01-architecture/adr/0009-revalidation-isr-webhook.md) |
| D10 | Table `media` unique + modèles métier `Photo` / `Video` distincts | Arbitrée | — voir [modèle de données](../01-architecture/03-modele-de-donnees.md) |
| D11 | Table `brands` (référentiel fermé) + `model` en texte libre | Arbitrée | — |
| D12 | Prix en **FCFA (XAF)**, entier, sans sous-unité. Site en français uniquement | Arbitrée | — |
| D13 | IA photos = amélioration + recadrage Cloudinary, **+ suppression de fond remove.bg sur quota compté** | Arbitrée | — voir [pipeline médias](../01-architecture/04-pipeline-medias.md) |
| D14 | Annonce vendue : **reste en ligne** avec badge, exclue des filtres par défaut | Arbitrée | — |
| D15 | **PWA en M4**, en fin de V1 | Arbitrée | — |
| D16 | Blog : **texte simple + image de couverture**, pas d'éditeur riche | Arbitrée | — |
| D17 | Dépôt **trunk-based**, branches courtes, **squash merge** | Arbitrée | — voir [règles du dépôt](../02-conventions/regles-du-depot.md) |
| D18 | **Conventional Commits vérifiés en CI** (bloquant) | Arbitrée | — |
| D19 | Tests **obligatoires sur l'API**, libres sur le front | Arbitrée | — voir [tests](../02-conventions/tests.md) |
| D20 | Quatre garde-fous CI, échelonnés M0 puis M1 | Arbitrée | — |
| D21 | Équipe : **2 devs**, 1 backend + 1 frontend | Arbitrée | — voir [répartition](../04-planning/repartition-equipe.md) |
| D22 | **Pas de deadline.** Découpage par valeur métier décroissante | Arbitrée | — |

## Décisions différées

| # | Sujet | À trancher quand | Qui |
|---|---|---|---|
| R01 | Pilote de file d'attente : `database` ou Redis | Quand le volume de médias le justifie. `database` en M0/M1 | Architecture |
| R02 | Préproduction : sous-domaine dédié ou aperçus Vercel seuls | Avant le premier déploiement de M1 | Architecture |
| R03 | Sauvegardes : fréquence et rétention | Tranché — assuré par Supabase ([ADR 0010](../01-architecture/adr/0010-postgresql-supabase.md)) | Architecture |
| R04 | Add-on payant Cloudinary pour la suppression de fond illimitée | Si le quota remove.bg de 50/mois devient bloquant | Client (coût récurrent) |
| R05 | Habillage vidéo réencodé : worker ffmpeg dédié ou crédits Cloudinary | Au démarrage du Lot 7 en V2 | Architecture + client |
| R06 | Multi-utilisateur du backoffice (employés du garage) | Si le garage grandit | Client |

## Contradiction du cahier des charges, tranchée

Le CDC se contredit sur la publication Facebook :

- **§2.3** la range explicitement dans « Ce qui est exclu (hors périmètre V1) » ;
- **§3.5**, **§5.1** et le **Lot 6** du §7 la décrivent comme un livrable.

**Arbitrage retenu : §2.3 fait foi.** La publication Facebook est hors V1 et reportée en V2.
Conséquence favorable : le projet n'a plus de dépendance à la validation Meta, dont le délai (plusieurs semaines) constituait le principal risque de planning.

Cet arbitrage est un **écart à un document contractuel** : il doit être confirmé par écrit par le client.
Voir [écarts au cahier des charges](ecarts-cahier-des-charges.md).

## Comment ajouter une décision

1. Si elle est structurante (elle serait coûteuse à défaire), écrire un [ADR](../01-architecture/adr/README.md).
2. Ajouter une ligne dans le tableau ci-dessus, avec le numéro `Dnn` suivant.
3. Si elle s'écarte du CDC, l'ajouter à [`ecarts-cahier-des-charges.md`](ecarts-cahier-des-charges.md).
4. Mettre à jour les documents impactés **dans la même PR**.
