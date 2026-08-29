# Contrat frontend / backend

Le document le plus important pour les deux développeurs. Il définit **qui décide quoi**.

L'architecture découplée ne supprime pas les ambiguïtés de responsabilité, elle les déplace : la question n'est plus « qui a le droit de toucher ce fichier » mais « de quel côté vit cette règle ». Une règle métier implémentée du mauvais côté est un bug en attente.

---

## Le principe

> **Le backend décide. Le frontend présente.**

Si une information peut être calculée de deux manières différentes par les deux applications, elle **doit** venir de l'API. Deux implémentations d'une même règle divergent toujours, tôt ou tard.

---

## Table des responsabilités

| Question | Qui décide | Pourquoi |
|---|---|---|
| Une annonce est-elle visible du public ? | **API** | Règle métier. Le front reçoit ce qu'il a le droit de voir, il ne filtre pas |
| Quel est le statut d'une annonce ? | **API** | `status` est une donnée, jamais dérivée d'autre chose côté front |
| Le message WhatsApp | **API** | Contient le nom du garage, le prix formaté et l'URL canonique. `whatsapp_url` arrive prêt à l'emploi |
| Le slug d'une annonce | **API** | Généré à la création, immuable. Le SEO en dépend |
| Les URL d'images et leurs dimensions | **API** | Le front ne connaît pas Cloudinary. Il reçoit des URL complètes, `width` et `height` |
| Quelle version d'une photo est publiée | **API** | `published_url`. Le front n'a pas à savoir qu'il existe des dérivés approuvés ou non |
| Quels filtres sont disponibles | **API** | La liste des marques vient de `/brands`. Jamais codée en dur côté front |
| Les valeurs d'énumération | **API** | `fuel`, `transmission`, `condition` : l'API renvoie la valeur brute **et** son libellé |
| L'ordre du catalogue | **API** | Le tri par défaut est une décision métier |
| La pagination | **API** | L'API impose la taille de page maximale |
| **Le formatage d'un prix** | **Front** | L'API renvoie `4500000`, le front affiche « 4 500 000 FCFA » |
| **Le formatage d'un kilométrage, d'une date** | **Front** | Idem |
| **Ce qui est visible à l'écran** | **Front** | L'API renvoie les données, le front choisit ce qu'il montre et comment |
| **L'état de l'interface** | **Front** | Onglet ouvert, modale, ordre de la galerie à l'écran |
| **L'accessibilité et le responsive** | **Front** | Entièrement |
| **La stratégie de cache ISR** | **Front** | Mais **les tags viennent du contrat** — voir plus bas |

---

## Ce que le front ne fait jamais

1. **Composer un message WhatsApp.** `whatsapp_url` est fourni. Un message construit côté front divergerait entre les deux apps et exigerait un redéploiement à chaque changement de formulation.
2. **Construire une URL Cloudinary.** Le front ne connaît ni le nom du compte, ni la syntaxe des transformations. Le jeu de dérivés est fermé et décidé côté API pour maîtriser les crédits.
3. **Décider si une annonce est visible.** Un endpoint public ne renvoie **jamais** de brouillon. Le front n'a donc aucun filtre de visibilité à écrire — et s'il en écrivait un, il masquerait un défaut de l'API.
4. **Coder en dur une valeur d'énumération.** `fuel === "essence"` est fragile. L'API renvoie `{ value: "essence", label: "Essence" }`.
5. **Calculer un total, un compteur, une moyenne.** Si un nombre doit être affiché, l'API le fournit.
6. **Modifier `types/api.d.ts`.** Fichier généré.

## Ce que l'API ne fait jamais

1. **Formater pour l'affichage.** Pas de « 4 500 000 FCFA », pas de « il y a 3 jours ». Des entiers et des dates ISO 8601.
2. **Renvoyer du HTML.** Jamais, nulle part.
3. **Décider d'une mise en page.** Pas de champ `css_class`, pas de `display_order` pensé pour un écran précis.
4. **Renvoyer un champ absent du contrat.** Même « juste pour aider ». Le front ne peut pas le consommer, et c'est une fuite potentielle.
5. **Renvoyer un champ interne sur un endpoint public.** Deux familles de ressources séparées, sans classe partagée.

---

## Formats imposés

| Donnée | Format | Exemple |
|---|---|---|
| Prix | entier, FCFA, sans sous-unité | `4500000` |
| Kilométrage | entier, kilomètres | `85000` |
| Année | entier | `2018` |
| Date / horodatage | ISO 8601 UTC | `"2026-08-25T14:30:00Z"` |
| Durée | entier, secondes | `duration_s: 45` |
| Identifiant | entier | `42` |
| Slug | chaîne | `"toyota-corolla-2018-42"` |
| Énumération | objet `{ value, label }` | `{ "value": "diesel", "label": "Diesel" }` |
| Booléen | booléen JSON | `true` — jamais `1`, jamais `"true"` |
| Champ absent | `null` explicite | jamais omis, jamais `""` |

**La dernière ligne est importante** : un champ optionnel est **toujours présent** avec la valeur `null`. Le front peut alors écrire `car.description ?? "…"` sans avoir à distinguer « absent » de « vide », deux cas qui divergent en TypeScript.

---

## Le cas des énumérations

L'API renvoie systématiquement la valeur et son libellé :

```json
{ "fuel": { "value": "diesel", "label": "Diesel" } }
```

Le front affiche `label` et compare sur `value`. Trois bénéfices : les libellés changent sans redéployer le front, un nouveau carburant apparaît sans modifier le front, et la comparaison reste stable.

---

## Comment les deux devs travaillent en parallèle

C'est le bénéfice qui justifie toute l'architecture. Il ne fonctionne que si la séquence est respectée.

```
Jour 1  │ Responsable architecture : le contrat de la fonctionnalité est écrit et fusionné
        │
Jour 2  │ Dev backend                        Dev frontend
        │ ├─ migration + factory             ├─ génère les types depuis le contrat
        │ ├─ modèle + action                 ├─ construit l'écran sur données factices
        │ └─ endpoints + tests               └─ maquette les états : chargement, vide, erreur
        │
Jour 3  │ Le backend fusionne  ──────────▶  Le front branche l'API réelle
        │                                    (les types collent déjà : le contrat est le même)
```

### Trois conditions pour que ça marche

**1. Les factories sont obligatoires et réalistes.** Le dev frontend s'appuie sur `php artisan migrate:fresh --seed` pour avoir des données crédibles : des marques réelles, des prix plausibles en FCFA, des photos de dimensions variées, une annonce vendue, une annonce sans photo, une annonce à 20 photos.

Un jeu de données qui ne contient que des cas parfaits produit une interface qui casse en production.

**2. Le contrat couvre les cas d'erreur, pas seulement le cas nominal.** Le front doit pouvoir construire l'état d'erreur avant que l'API sache la produire. Chaque endpoint documente ses codes d'erreur dans le contrat.

**3. Les données factices viennent des exemples du contrat.** `openapi.yaml` porte un `example` par schéma. Le front les utilise, ce qui garantit que ce qu'il construit correspond à ce qui arrivera.

---

## Les états que le front doit toujours traiter

Ce ne sont pas des cas limites — ce sont des états normaux du système, et ils font partie du contrat.

| État | Quand | Comportement attendu |
|---|---|---|
| Chargement | toujours | Squelette de contenu, jamais un décalage de mise en page |
| Vide | aucune annonce, aucun résultat de filtre | Message utile et une action (« élargir les critères ») |
| Erreur réseau | API injoignable | Message clair et un bouton pour réessayer |
| `401` | jeton expiré (admin) | Redirection vers la connexion, sans perdre le formulaire en cours |
| `403` | droits insuffisants | Message, pas de page blanche |
| `404` | slug inconnu | Page 404 dédiée, avec un lien vers le catalogue |
| `409` | quota épuisé, conflit de statut | Message **spécifique** : « quota mensuel atteint (50/50) » |
| `422` | validation | Erreurs **par champ**, affichées sous le champ concerné |
| `429` | limitation de débit | « Trop de tentatives, réessayez dans un instant » |
| Média sans dérivé | amélioration en cours | Afficher l'original, sans état d'erreur |
| Amélioration `failed` | échec fournisseur | Message et possibilité de réessayer |

---

## Les tags de revalidation font partie du contrat

C'est le point d'intégration le plus facile à rater, parce qu'il n'apparaît dans aucune réponse d'API.

Le backend invalide des tags, le front les consomme. Si le backend ajoute une écriture sans invalider le bon tag, **le front affiche une donnée périmée sans qu'aucune erreur ne se produise**. Le bug est silencieux, et il ressemble à un bug frontend.

La table de correspondance vit dans [07 — Performance et SEO](../01-architecture/07-performance-seo-pwa.md#ce-qui-déclenche-une-revalidation) et **elle est contractuelle** :

- Le dev backend qui ajoute une opération d'écriture sur une donnée publique **doit** déclarer les tags invalidés.
- Le dev frontend qui ajoute une route qui consomme des données publiques **doit** déclarer les tags dont elle dépend.
- Les deux listes sont revues ensemble dans la PR de contrat.

---

## Quand un désaccord survient

1. Si le contrat répond, **le contrat gagne** — sans discussion.
2. Si le contrat est ambigu, c'est un défaut du contrat : on ouvre une PR sur `openapi.yaml`, arbitrée par le responsable architecture.
3. Si les deux devs sont d'accord entre eux mais que ça sort du contrat, **il faut quand même modifier le contrat**. Un accord verbal n'est pas un contrat, et il est perdu dans six mois.

Une seule règle à retenir : **rien ne se décide dans un fil de discussion**. Ce qui n'est pas écrit dans `openapi.yaml` ou dans `docs/` n'existe pas.
