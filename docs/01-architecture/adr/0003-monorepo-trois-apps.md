# 0003 — Monorepo de trois applications, sans paquet partagé

- **Statut** : Accepté
- **Date** : 2026-08-25
- **Décideur** : responsable architecture
- **Décision liée** : D03

## Contexte

L'architecture découplée produit trois unités déployables : l'API Laravel, la vitrine Next, le backoffice Next. Il faut décider où elles vivent, et ce qu'elles partagent.

Deux questions distinctes, souvent confondues :

1. **Un dépôt ou trois ?** — question d'organisation du travail.
2. **Du code partagé ou non ?** — question de couplage technique.

## Options examinées

### Option A — Trois dépôts indépendants

**Apporte** : frontière très nette entre les devs, chacun son historique, sa CI, son rythme de livraison.

**Coûte** : tout changement de contrat devient une coordination à deux ou trois PR dans des dépôts différents. Rien ne garantit qu'elles soient fusionnées ensemble. Sur un projet à deux personnes, cette coordination coûte plus qu'elle ne rapporte, et la fenêtre où le contrat est cassé n'est jamais nulle.

### Option B — Monorepo avec workspaces et paquets partagés

`packages/api-client` (types et client HTTP générés) et `packages/ui` (design system) consommés par les deux apps Next.

**Apporte** : une seule définition du contrat et de la marque. Aucune duplication.

**Coûte** : un ordre de construction entre les paquets, un outil de workspace (pnpm, Turborepo) à poser et à maintenir, et surtout un **couplage de déploiement** : modifier `packages/ui` impose de reconstruire et redéployer les deux apps ensemble. La configuration Vercel devient plus délicate (répertoires racine, dépendances hors du répertoire de l'app).

### Option C — Monorepo, apps indépendantes, contrat à la racine

Un dépôt. `openapi.yaml` à la racine. Chaque app Next génère ses propres types localement. Aucun paquet partagé, aucun workspace.

**Apporte** : une PR peut modifier le contrat, l'API et les deux fronts **ensemble** — on ne peut pas casser le contrat sans le voir dans le même diff. Les trois apps restent réellement indépendantes au déploiement : chacune se construit sans connaître les autres. Aucun outil de workspace à maintenir.

**Coûte** : la commande de génération de types est dupliquée dans deux `package.json`. Le code UI commun aux deux apps est dupliqué ou recopié à la main.

## Décision

**Option C.**

Le critère décisif est le contrat. C'est l'artefact le plus fragile de l'architecture découplée, et le placer dans le même dépôt que ses trois consommateurs signifie qu'un changement incohérent est visible dans un seul diff, revu par une seule personne, fusionné d'un seul coup. C'est le principal bénéfice du monorepo, et il est obtenu sans aucun outil de workspace.

Sur les paquets partagés : le besoin réel de mutualisation est faible. La vitrine et le backoffice n'ont pas les mêmes besoins UI — la vitrine est optimisée pour la performance et le poids, le backoffice pour la densité d'information. Un `packages/ui` commun aurait servi le plus petit dénominateur des deux, tout en imposant un couplage de déploiement. La duplication ici est **voulue**, pas subie.

## Conséquences

### Positives

- Un changement de contrat est atomique : un commit, un diff, une revue.
- Les trois apps se déploient indépendamment, sans ordre de construction.
- Aucun outil de workspace à maintenir. La configuration Vercel reste triviale : un répertoire racine par projet.
- CODEOWNERS par dossier donne une propriété claire : `apps/api` au dev backend, `apps/web` et `apps/admin` au dev frontend, `openapi.yaml` et `docs/` au responsable architecture.

### Négatives

- **Commande de génération de types dupliquée** dans deux `package.json`. Coût réel : deux lignes.
- **Code UI dupliqué** entre les deux apps. Si la duplication devenait douloureuse (au-delà de cinq ou six composants réellement identiques), il faudra reconsidérer.
- Un historique Git commun : `git log` mélange les trois apps. Atténué par la portée obligatoire des messages de commit (`feat(web):`, `fix(api):`).
- La CI doit détecter quels chemins ont changé pour ne pas tout reconstruire à chaque PR.

### Ce que ça implique concrètement

```
.
├── openapi.yaml          propriété du responsable architecture
├── apps/api/             Laravel
├── apps/web/             Next — vitrine
├── apps/admin/           Next — backoffice
├── docs/
└── .github/
```

- `npx openapi-typescript ../../openapi.yaml -o types/api.d.ts` en `postinstall` de chaque app Next.
- `types/api.d.ts` est généré : le modifier à la main est un motif de refus de PR.
- La CI est découpée par chemin : une PR touchant `docs/` ne lance pas les tests PHP.

## Quand reconsidérer

Si plus de cinq ou six composants UI réellement identiques apparaissaient dans les deux apps, ou si une troisième application front voyait le jour. À ce moment, `packages/ui` avec pnpm workspaces deviendrait justifié.
