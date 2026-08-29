# 0005 — `openapi.yaml` écrit à la main comme source de vérité

- **Statut** : Accepté
- **Date** : 2026-08-25
- **Décideur** : responsable architecture
- **Décision liée** : D05

## Contexte

L'architecture découplée fait du contrat d'API l'artefact le plus critique du projet : c'est la seule chose que les deux développeurs partagent. S'il est ambigu, ils construisent deux moitiés qui ne s'emboîtent pas. S'il n'existe pas, la spécification devient « ce que le code fait aujourd'hui ».

Il faut décider **qui définit le contrat, et quand**.

C'est aussi une question d'organisation, pas seulement de technique : le responsable architecture doit-il pouvoir arbitrer une forme de réponse **avant** qu'elle soit codée ?

## Options examinées

### Option A — Généré depuis le code Laravel (Scramble, L5-Swagger)

Le backend code ses `Resources`, un outil en déduit l'OpenAPI, le front génère ses types.

**Apporte** : jamais de désynchronisation entre la doc et le code. Zéro documentation à maintenir à la main.

**Coûte** : le contrat devient une **conséquence** du code, pas une décision. Le front est toujours en retard d'un pas : il ne peut rien construire avant que le backend ait codé et déployé. Et le responsable architecture ne peut plus arbitrer une forme de réponse en amont — il ne peut que la constater et demander une correction après coup.

### Option B — Types TypeScript écrits à la main côté front

**Apporte** : aucun outillage, démarrage immédiat.

**Coûte** : rien ne garantit qu'un type corresponde à la réponse réelle. Les écarts se découvrent en production, sous forme de `undefined`. Il n'y a pas de contrat, seulement deux suppositions parallèles.

### Option C — OpenAPI écrit à la main, source de vérité

Le contrat est rédigé et validé **avant** le code. Laravel et React s'y conforment tous les deux. Les types TypeScript en sont générés. Un test vérifie que les réponses réelles valident le schéma.

**Apporte** : le contrat devient une **décision arbitrable en amont**. Le front peut construire sur des données factices dès que le contrat existe, sans attendre l'API — les deux devs démarrent le même jour sur la même fonctionnalité. Et le fichier peut appartenir au responsable architecture via CODEOWNERS : personne ne modifie le contrat sans son accord.

**Coûte** : un fichier YAML à écrire et à tenir à jour. Un risque de dérive entre le contrat et le code, à contrer par un test automatique.

## Décision

**Option C.**

Deux critères ont tranché. D'abord le parallélisme : avec deux devs et une seule fonctionnalité en cours, le front doit pouvoir démarrer en même temps que le back, ce qui n'est possible que si le contrat précède le code. Ensuite la gouvernance : le responsable architecture doit pouvoir arbitrer la forme des réponses avant qu'elles existent — l'option A rend cela structurellement impossible.

Le risque de dérive est réel, et c'est pourquoi il est traité par une vérification automatique plutôt que par la discipline : à partir de M1, un test valide chaque réponse d'API contre le schéma déclaré, et la CI échoue si les deux divergent. Sans cette vérification, « source de vérité » ne serait qu'une formule.

## Conséquences

### Positives

- Le contrat est arbitré avant le code, par une personne identifiée.
- Le front démarre sans attendre le back, sur des données factices conformes au contrat.
- Les types TypeScript sont générés, donc exacts par construction.
- La documentation d'API est le contrat lui-même : elle ne peut pas être périmée si la CI passe.
- CODEOWNERS sur `openapi.yaml` donne un point de contrôle réel sur l'architecture.

### Négatives

- **Un fichier YAML à maintenir.** Écrire de l'OpenAPI à la main est fastidieux, et le fichier va devenir long.
- **Une étape de plus dans le cycle** : décrire, puis implémenter. Cela ralentit les changements triviaux.
- **Risque de dérive** si la vérification de conformité n'est pas mise en place. C'est le point faible de cette décision, et c'est pourquoi le test est prévu dès M1.
- La tentation de modifier le code sans le contrat existera. CODEOWNERS et la CI sont là pour ça.

### Ce que ça implique concrètement

- `openapi.yaml` vit à la racine, appartient au responsable architecture (CODEOWNERS).
- **Ordre obligatoire** : contrat → revue → implémentation. Une PR qui change une réponse sans changer le contrat est refusée.
- Chaque app Next génère ses types en `postinstall` ; `types/api.d.ts` est généré et ne se modifie pas à la main.
- Dès M1, un test valide chaque réponse contre le schéma. C'est le seul des quatre garde-fous CI qui peut être abandonné s'il coûte trop cher — mais alors la décision perd sa garantie.
- Les valeurs d'énumération sont **identiques** dans `app/Enums/` et dans le contrat.

## Quand reconsidérer

Si la vérification de conformité s'avérait impossible à mettre en place de façon fiable, il faudrait choisir entre accepter une dérive silencieuse ou basculer sur l'option A. Dans ce cas, l'option A serait préférable : un contrat généré mais exact vaut mieux qu'un contrat déclaré mais faux.
