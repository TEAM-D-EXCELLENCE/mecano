# Backoffice — Mecano (`apps/admin`)

Interface de gestion du garage. Next.js (App Router), TypeScript strict,
Tailwind, shadcn/ui. Elle ne parle jamais directement à l'API : tout passe par
le BFF, qui détient le jeton d'authentification.

## Démarrer en local

```bash
npm install                 # régénère aussi types/api.d.ts depuis openapi.yaml
cp .env.example .env.local  # puis renseigner API_BASE_URL
npm run dev -- -p 3001
```

L'API Laravel doit tourner en parallèle (voir `apps/api`).

## Variables d'environnement

| Variable | Obligatoire | Rôle |
|---|---|---|
| `API_BASE_URL` | **oui** | Racine de l'API Laravel, sans barre oblique finale. Ex. `https://mecano-api.duckdns.org/api/v1` |
| `COOKIE_NAME` | non | Nom du cookie de session du BFF. `mc_s` par défaut |

Elles sont lues **à la requête**, jamais au chargement des modules : le build
n'a pas besoin de connaître l'API, seul l'exécution en a besoin.

## Déploiement Vercel

- **Root Directory** : `apps/admin`
- **Environment Variables** : `API_BASE_URL` sur les trois environnements
  (Production, Preview, Development)

Le reste est détecté automatiquement.

## Ce qu'il faut savoir avant de contribuer

- `types/api.d.ts` est **généré** depuis `openapi.yaml`. Le modifier à la main
  fait refuser la PR. Un champ manquant se corrige dans le contrat.
- Les composants de `components/ui/` viennent de la CLI shadcn et ne se
  modifient pas à la main : `npx shadcn@latest add <composant>`.
- Aucune règle métier ici. Voir
  [le contrat frontend / backend](../../docs/02-conventions/contrat-frontend-backend.md).

## Vérifications avant de pousser

```bash
npx tsc --noEmit   # ou npx next build, qui inclut le typage
npx eslint .
```
