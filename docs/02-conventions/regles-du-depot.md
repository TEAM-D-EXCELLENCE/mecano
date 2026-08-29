# Règles du dépôt

Ce document fait autorité sur l'organisation du travail. Il est la propriété du **responsable architecture**, qui arbitre toute exception.

À lire **avant le premier commit**.

---

## 1. Branches

**Trunk-based, branches courtes** (décision D17).

- `main` est **toujours déployable**. Aucun commit direct, aucune exception.
- Une branche vit **1 à 3 jours maximum**. Au-delà, la tâche était trop grosse : on la découpe.
- Fusion en **squash merge** exclusivement. Un historique linéaire, un commit par tâche.
- La branche est supprimée après fusion.

### Nommage

```
<type>/<jalon>-<description-courte>
```

| Type | Usage | Exemple |
|---|---|---|
| `feat` | Nouvelle fonctionnalité | `feat/M1-car-crud-api` |
| `fix` | Correction | `fix/M1-slug-duplique` |
| `refactor` | Réorganisation sans changement de comportement | `refactor/M2-extraction-actions` |
| `docs` | Documentation seule | `docs/M0-contrat-medias` |
| `chore` | Outillage, dépendances, CI | `chore/M0-lighthouse-ci` |
| `test` | Tests seuls | `test/M1-endpoints-publics` |

Le jalon dans le nom n'est pas décoratif : il permet de voir d'un coup d'œil si une branche appartient encore au jalon en cours.

### Pourquoi des branches courtes

À deux développeurs sur un contrat partagé, une branche qui vit une semaine accumule des conflits et retarde la détection d'une incompatibilité entre le front et l'API. Une branche courte échoue tôt, quand c'est bon marché.

---

## 2. Commits

**Conventional Commits, vérifiés en CI** (décision D18). Une PR dont un commit ne respecte pas le format est **refusée automatiquement**.

```
<type>(<portée>): <description à l'impératif, minuscule, sans point final>

[corps facultatif : le pourquoi, jamais le comment]

[pied facultatif : BREAKING CHANGE, Refs #12]
```

### Types

`feat`, `fix`, `refactor`, `docs`, `test`, `chore`, `perf`, `style`, `revert`.

### Portées — obligatoires

`api`, `web`, `admin`, `contract`, `docs`, `ci`.

`contract` est réservé aux modifications de `openapi.yaml`. Elle est volontairement distincte : elle rend visible dans `git log` chaque évolution du contrat, ce qui est l'information la plus utile pour retrouver l'origine d'une incompatibilité.

### Exemples

```
feat(api): ajoute le filtre par marque sur le catalogue public
feat(web): affiche le badge Vendu sur la fiche véhicule
fix(admin): corrige la perte du jeton après expiration du cookie
docs(contract): documente les codes d'erreur de la signature d'upload
refactor(api): extrait ChangeCarStatus dans une action dédiée
chore(ci): active Lighthouse CI sur les aperçus
```

### Interdits

```
❌ wip
❌ fix
❌ ça marche
❌ feat: modifications diverses         (portée manquante, description vague)
❌ Feat(api): Ajoute Le Filtre.         (casse et point final)
```

Comme la fusion est en squash, c'est **le titre de la PR** qui devient le commit sur `main`. Il doit donc lui aussi respecter ce format — la CI le vérifie.

---

## 3. Pull requests

### Taille

**Moins de 400 lignes modifiées**, hors fichiers générés et fichiers de verrouillage. Au-delà, la revue devient superficielle : personne ne relit sérieusement 1 200 lignes.

Si une tâche dépasse cette taille, elle se découpe. Exemple concret : « CRUD annonces » se découpe en migration + modèle, puis endpoints de lecture, puis endpoints d'écriture, puis validation.

### Contenu obligatoire

Le [gabarit de PR](../../.github/pull_request_template.md) est rempli, pas vidé. En particulier :

- **Ce que ça change**, en une ou deux phrases.
- **Comment le vérifier** : les étapes concrètes que le relecteur doit suivre. Une PR sans procédure de vérification est refusée.
- **Le lien vers la tâche** du backlog (`BE-08`, `FE-11`…).
- **Captures d'écran** pour tout changement visible, en mobile **et** en bureau.

### Ce qui bloque la fusion

Décision D20, échelonnée :

| Contrôle | Actif depuis | Ce qu'il vérifie |
|---|---|---|
| Format des commits et du titre | M0 | Conventional Commits |
| Qualité du code | M0 | Pint, Larastan niveau 6, ESLint, Prettier, `tsc --noEmit` |
| **Revue humaine** | M0 | Une approbation de l'autre dev, plus les CODEOWNERS concernés |
| Tests | M1 | Pest sur l'API, Vitest sur le front |
| Conformité au contrat | M1 | Chaque réponse valide le schéma `openapi.yaml` |
| Budget de performance | M1 | Lighthouse CI sur les aperçus, plafonds de [07](../01-architecture/07-performance-seo-pwa.md) |

### Revue

- **Une approbation minimum**, toujours de l'autre développeur.
- Les CODEOWNERS concernés doivent approuver : une PR touchant `openapi.yaml` exige l'approbation du responsable architecture.
- Un relecteur commente **le code**, jamais la personne.
- Une remarque de style est illégitime : c'est le rôle de Pint, Prettier et ESLint. Si un point de style revient en revue, on configure l'outil au lieu d'en discuter.
- Un relecteur qui demande un changement propose une alternative concrète.
- L'auteur ne fusionne pas sa PR sans approbation, même si la CI est verte.

### Délai

Une PR ouverte attend au maximum **un jour ouvré**. À deux développeurs, une PR en attente bloque littéralement la moitié de l'équipe.

---

## 4. Propriété du code

Voir [`.github/CODEOWNERS`](../../.github/CODEOWNERS).

| Chemin | Propriétaire | Conséquence |
|---|---|---|
| `openapi.yaml` | **Responsable architecture** | Aucun changement de contrat sans son accord |
| `docs/` | **Responsable architecture** | La documentation d'architecture est arbitrée, pas négociée en PR |
| `docs/01-architecture/adr/` | **Responsable architecture** | Un ADR ne se modifie pas : on en écrit un nouveau |
| `apps/api/` | Dev backend | |
| `apps/web/`, `apps/admin/` | Dev frontend | |
| `.github/` | **Responsable architecture** | Les règles ne s'assouplissent pas par PR |
| `apps/api/database/migrations/` | Dev backend + responsable architecture | Une migration est irréversible en pratique |

**Ce mécanisme est ce qui rend la responsabilité architecturale réelle** plutôt que déclarative : ce n'est pas une convention à respecter, c'est GitHub qui refuse la fusion.

---

## 5. Migrations de base de données

Cinq règles, sans exception :

1. **Une migration fusionnée sur `main` ne se modifie plus jamais.** On en ajoute une nouvelle. Modifier une migration déjà appliquée casse l'environnement de tous les autres.
2. Toute nouvelle colonne est **nullable ou dotée d'une valeur par défaut**. Une migration ne doit jamais échouer sur des données existantes.
3. Une migration doit avoir un `down()` fonctionnel, ou déclarer explicitement en commentaire pourquoi elle est irréversible.
4. Une migration reste **rétrocompatible** avec le code précédent, pour permettre un retour arrière. Renommer une colonne se fait en trois étapes (ajouter, migrer les données, supprimer), sur trois déploiements.
5. Une PR touchant une migration met à jour [`03-modele-de-donnees.md`](../01-architecture/03-modele-de-donnees.md) **et** la `factory` correspondante. Le dev frontend dépend des factories pour ses données d'exemple.

---

## 6. Le contrat d'API

Décision D05, [ADR 0005](../01-architecture/adr/0005-openapi-source-de-verite.md).

**Ordre obligatoire :**

```
1. Modifier openapi.yaml          → PR de contrat, revue par l'architecture
2. Implémenter côté API           → PR api
3. Consommer côté front           → PR web / admin
```

Les étapes 2 et 3 peuvent être **simultanées** : c'est tout l'intérêt d'un contrat écrit d'avance. Le front construit sur des données factices conformes au contrat pendant que le back implémente.

**Interdit :**

- Modifier une réponse d'API sans modifier le contrat. Refus immédiat.
- Modifier `types/api.d.ts` à la main. C'est un fichier généré, il serait écrasé.
- Consommer un champ absent du contrat, même s'il est présent dans la réponse réelle.

**Changement cassant** (champ supprimé ou renommé, type modifié, nouveau champ obligatoire) : mention `BREAKING CHANGE` dans le pied du commit, et les trois PR sont fusionnées **le même jour**.

---

## 7. Secrets

- Aucun secret dans le dépôt. Jamais. Aucune exception, même temporaire, même en commentaire.
- `.env` est dans `.gitignore`. Seul `.env.example` est versionné, avec des valeurs vides.
- Un secret commis par accident est **considéré comme compromis** : on le révoque et on en génère un nouveau. Réécrire l'historique ne suffit pas — il est déjà dans les caches GitHub, les clones locaux et éventuellement des services d'indexation.
- Une variable `NEXT_PUBLIC_*` est **publique par définition**. Si un secret a besoin de ce préfixe, l'architecture est fausse : passer par le BFF.

---

## 8. Dépendances

- Une nouvelle dépendance se justifie dans la PR : ce qu'elle apporte, son poids, sa maintenance.
- Côté `apps/web`, une dépendance qui alourdit le bundle client doit être pesée face au [budget de performance](../01-architecture/07-performance-seo-pwa.md).
- Les fichiers de verrouillage sont **toujours** commis.
- Les composants shadcn/ui sont générés dans `components/ui/` et **ne se modifient pas à la main** : une personnalisation passe par les variantes ou par un composant qui l'enveloppe.

---

## 9. Fichiers générés

Ces fichiers sont versionnés mais **jamais modifiés à la main** :

| Fichier | Généré par |
|---|---|
| `apps/web/types/api.d.ts` | `openapi-typescript` |
| `apps/admin/types/api.d.ts` | `openapi-typescript` |
| `composer.lock`, `package-lock.json` | gestionnaires de paquets |
| `apps/*/components/ui/*` | CLI shadcn |

Une PR qui les modifie à la main est refusée.

---

## 10. Ce qui n'entre pas dans le dépôt

- Fichiers `.env`, sauvegardes de base, exports SQL.
- Médias de test volumineux (photos, vidéos) — utiliser des données d'exemple générées.
- `node_modules/`, `vendor/`, `.next/`, `storage/logs/`.
- Fichiers de configuration d'éditeur personnels (`.idea/`, `.vscode/settings.json`).
- Documents contractuels retravaillés : le `cahier_des_charges.docx` d'origine reste, ses dérivés non.

---

## 11. Exceptions

Une règle de ce document ne se contourne pas dans une PR. Elle se modifie dans **une PR dédiée à `docs/02-conventions/regles-du-depot.md`**, approuvée par le responsable architecture.

Si une règle bloque le travail de façon répétée, c'est un signal qu'elle est mal calibrée : il faut la changer, pas la contourner en silence.
