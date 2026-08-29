# Conventions API

Le contrat lui-même est [`openapi.yaml`](../../openapi.yaml), à la racine du dépôt. Ce document décrit les conventions transverses qui s'y appliquent.

`openapi.yaml` est la **source de vérité** (décision D05, [ADR 0005](../01-architecture/adr/0005-openapi-source-de-verite.md)). Il est modifié **avant** le code, et il appartient au responsable architecture.

---

## Base et versionnement

```
https://api.garage.com/api/v1
```

- La version est dans le chemin. Un changement cassant crée `/v2`, il ne modifie pas `/v1`.
- Un ajout de champ optionnel n'est pas cassant : il n'exige pas de nouvelle version.
- Sont cassants : supprimer ou renommer un champ, changer un type, rendre un champ obligatoire, retirer une valeur d'énumération.

## Les trois espaces

| Espace | Préfixe | Auth | Consommateur |
|---|---|---|---|
| Public | `/api/v1/…` | aucune | `apps/web` en SSR |
| Auth | `/api/v1/auth/…` | selon l'endpoint | BFF de `apps/admin` |
| Backoffice | `/api/v1/admin/…` | Bearer obligatoire | BFF de `apps/admin` |

Le namespace `/admin` est **séparé du public, pas une variante protégée** : les deux renvoient des formes différentes. L'admin voit les brouillons, les compteurs, les dérivés non approuvés ; le public ne les voit jamais. Aucune classe de ressource n'est partagée entre les deux — c'est une frontière de sécurité.

## Authentification

`Authorization: Bearer <jeton>` (Sanctum). L'API est **sans état** : aucune session, aucun cookie.

Le jeton n'est jamais manipulé par du JavaScript de navigateur : il vit dans un cookie httpOnly détenu par le BFF ([ADR 0004](../01-architecture/adr/0004-auth-bearer-bff.md)).

---

## Format des réponses

### Succès — ressource unique

```json
{ "data": { "id": 42, "slug": "toyota-corolla-2018-42" } }
```

### Succès — collection paginée

```json
{
  "data": [ ... ],
  "meta": { "current_page": 1, "per_page": 20, "total": 47, "last_page": 3 },
  "links": { "first": "...", "prev": null, "next": "...", "last": "..." }
}
```

### Erreur — format unique pour toute l'API

```json
{
  "error": {
    "code": "QUOTA_EXCEEDED",
    "message": "Quota mensuel de suppression de fond atteint (50/50).",
    "details": { "provider": "removebg", "used": 50, "limit": 50, "resets_at": "2026-09-01T00:00:00Z" }
  }
}
```

- `code` : identifiant stable en majuscules. **Le front compare sur `code`, jamais sur `message`.**
- `message` : français, affichable tel quel au mécanicien.
- `details` : facultatif, structuré.

### Erreur de validation

```json
{
  "error": {
    "code": "VALIDATION_FAILED",
    "message": "Certains champs sont invalides.",
    "details": {
      "price_xaf": ["Le prix doit être un nombre entier positif."],
      "year": ["L'année ne peut pas être supérieure à 2027."]
    }
  }
}
```

`details` est une correspondance champ → liste de messages, ce qui permet au front d'afficher chaque erreur sous son champ.

---

## Règles de forme

Ces règles font partie du contrat et sont vérifiées par le test de conformité.

1. **Aucun champ omis.** Un champ optionnel est présent avec la valeur `null`. Le front peut écrire `car.description ?? '…'` sans distinguer « absent » de « vide ».
2. **Toute énumération sort en `{ value, label }`.** `label` est en français et affichable.
3. **Toute date en ISO 8601 UTC** : `"2026-08-25T14:30:00Z"`.
4. **Tout montant est un entier en FCFA**, sans sous-unité.
5. **Toute URL de média est complète et absolue.** Le front ne construit jamais d'URL de fournisseur.
6. **Toute photo porte `width` et `height`.** Obligatoire : le front en a besoin pour éviter le décalage de mise en page.
7. **Les booléens sont des booléens JSON.** Jamais `1`, jamais `"true"`.

---

## Codes de statut

| Code | Usage |
|---|---|
| `200` | Lecture réussie, mise à jour réussie |
| `201` | Création réussie |
| `204` | Suppression réussie, sans corps |
| `400` | Requête malformée (JSON invalide, paramètre inexploitable) |
| `401` | Absent ou invalide : jeton manquant, expiré, révoqué |
| `403` | Authentifié mais non autorisé |
| `404` | Ressource inexistante, ou non visible du public |
| `409` | Conflit d'état : transition interdite, quota épuisé, doublon |
| `422` | Validation échouée |
| `429` | Limitation de débit atteinte |
| `500` | Erreur serveur. Aucun détail exposé en production |

**`404` plutôt que `403` pour une annonce en brouillon** vue depuis un endpoint public : on ne révèle pas l'existence d'une ressource non publiée.

---

## Codes d'erreur métier

| `code` | Statut | Signification |
|---|---|---|
| `VALIDATION_FAILED` | 422 | Champs invalides, détail par champ |
| `UNAUTHENTICATED` | 401 | Jeton absent, expiré ou révoqué |
| `INVALID_CREDENTIALS` | 401 | Email ou mot de passe incorrect |
| `FORBIDDEN` | 403 | Non autorisé sur cette ressource |
| `NOT_FOUND` | 404 | Ressource inexistante ou non publiée |
| `INVALID_STATUS_TRANSITION` | 409 | Ex. `sold` → `draft` |
| `CAR_NOT_PUBLISHABLE` | 409 | Passage en `available` sans photo principale |
| `MEDIA_LIMIT_REACHED` | 409 | Deux vidéos déjà présentes |
| `QUOTA_EXCEEDED` | 409 | Quota mensuel du fournisseur atteint |
| `ENHANCEMENT_IN_PROGRESS` | 409 | Amélioration identique déjà en cours |
| `UPLOAD_NOT_FOUND` | 409 | Confirmation d'un fichier absent chez le fournisseur |
| `PROVIDER_UNAVAILABLE` | 503 | Cloudinary, R2 ou remove.bg injoignable |
| `RATE_LIMITED` | 429 | Trop de requêtes |

Cette liste est exhaustive : le front peut la traiter en entier. Tout nouveau code s'y ajoute dans la même PR que son implémentation.

---

## Pagination

Par page, pas par curseur : le volume est faible et une pagination par page produit des URL partageables et indexables.

| Paramètre | Défaut | Maximum |
|---|---|---|
| `page` | 1 | — |
| `per_page` | 20 | 50 |

Un `per_page` supérieur au maximum est **ramené au maximum**, sans erreur.

---

## Filtres du catalogue

`GET /api/v1/cars`

| Paramètre | Type | Note |
|---|---|---|
| `marque` | slug | Slug de `brands`. CDC §3.7 |
| `prix_min`, `prix_max` | entier FCFA | CDC §3.7 |
| `annee_min`, `annee_max` | entier | CDC §3.7 |
| `carburant` | énumération | |
| `transmission` | énumération | |
| `inclure_vendus` | booléen | `false` par défaut (D14) |
| `tri` | énumération | `recent` (défaut), `prix_asc`, `prix_desc`, `km_asc` |
| `page`, `per_page` | entier | |

**Les noms de paramètres sont en français** : ils apparaissent dans les URL publiques (`/voitures?marque=toyota&prix_max=5000000`), qui sont indexées et partagées par les visiteurs.

Un filtre inconnu est **ignoré silencieusement**, jamais une erreur : une URL partagée ne doit pas casser parce qu'un filtre a été retiré.

---

## Limitation de débit

Détail et justification dans [06 — Sécurité](../01-architecture/06-securite.md#limitation-de-débit).

| Endpoint | Limite |
|---|---|
| `POST /auth/login` | 5/min par IP **et** 10/h par email |
| `POST /cars/{slug}/events` | 60/min par IP |
| `POST /admin/media/upload-signature` | 30/min |
| `POST /admin/media/{id}/enhance` | 10/min |
| Autres endpoints admin | 120/min |
| Lectures publiques | 300/min par IP |

Réponse `429` avec un en-tête `Retry-After`.

---

## Ce que l'API ne fait jamais

1. Renvoyer du HTML.
2. Formater pour l'affichage (« 4 500 000 FCFA », « il y a 3 jours »).
3. Renvoyer un champ absent du contrat, même utile.
4. Renvoyer un brouillon ou un dérivé non approuvé sur un endpoint public.
5. Renvoyer un mot de passe, un jeton, un secret de fournisseur.
6. Exposer une trace de pile ou une requête SQL en production.
7. Décider d'une mise en page (pas de `css_class`, pas de `display_order` lié à un écran).

---

## Surface complète

| Méthode | Chemin | Auth | Jalon |
|---|---|---|---|
| `GET` | `/health` | — | M0 |
| `POST` | `/auth/login` | — | M0 |
| `POST` | `/auth/logout` | Bearer | M0 |
| `GET` | `/auth/me` | Bearer | M0 |
| `GET` | `/brands` | — | M1 |
| `GET` | `/cars` | — | M1 |
| `GET` | `/cars/{slug}` | — | M1 |
| `POST` | `/cars/{slug}/events` | — | M1 |
| `GET` | `/admin/cars` | Bearer | M1 |
| `POST` | `/admin/cars` | Bearer | M1 |
| `GET` | `/admin/cars/{id}` | Bearer | M1 |
| `PATCH` | `/admin/cars/{id}` | Bearer | M1 |
| `DELETE` | `/admin/cars/{id}` | Bearer | M1 |
| `PATCH` | `/admin/cars/{id}/status` | Bearer | M1 |
| `POST` | `/admin/media/upload-signature` | Bearer | M1 |
| `POST` | `/admin/cars/{id}/media` | Bearer | M1 |
| `GET` | `/admin/cars/{id}/media` | Bearer | M1 |
| `PATCH` | `/admin/media/{id}` | Bearer | M1 |
| `DELETE` | `/admin/media/{id}` | Bearer | M1 |
| `POST` | `/admin/cars/{id}/media/reorder` | Bearer | M1 |
| `GET` | `/admin/brands` | Bearer | M1 |
| `POST` | `/admin/brands` | Bearer | M1 |
| `GET` | `/services` | — | M2 |
| `GET` | `/admin/services` | Bearer | M2 |
| `POST` | `/admin/services` | Bearer | M2 |
| `PATCH` | `/admin/services/{id}` | Bearer | M2 |
| `GET` | `/posts` | — | M2 |
| `GET` | `/posts/{slug}` | — | M2 |
| `GET` | `/admin/posts` | Bearer | M2 |
| `POST` | `/admin/posts` | Bearer | M2 |
| `PATCH` | `/admin/posts/{id}` | Bearer | M2 |
| `DELETE` | `/admin/posts/{id}` | Bearer | M2 |
| `GET` | `/settings` | — | M2 |
| `PATCH` | `/admin/settings` | Bearer | M2 |
| `POST` | `/admin/media/{id}/enhance` | Bearer | M3 |
| `GET` | `/admin/media/{id}/enhancements` | Bearer | M3 |
| `POST` | `/admin/enhancements/{id}/approve` | Bearer | M3 |
| `GET` | `/admin/quotas` | Bearer | M3 |
| `GET` | `/admin/dashboard` | Bearer | M4 |

Reporté en V2 : `POST /admin/cars/{id}/publish/facebook`, `GET /admin/cars/{id}/publications`.
