# Documentation — Mecano

Source de vérité du projet. Le code s'y conforme.

```
docs/
├── 00-contexte/      Ce qu'on construit, ce qui est arbitré, ce qui s'écarte du CDC
├── 01-architecture/  Comment le système est construit (+ journal des décisions)
├── 02-conventions/   Comment on travaille
├── 03-api/           Conventions du contrat d'interface
└── 04-planning/      MVP, étapes, backlog, répartition
```

## 00 — Contexte

| Document | Contenu |
|---|---|
| [Glossaire](00-contexte/glossaire.md) | Vocabulaire commun : annonce, média, dérivé, habillage, jalon |
| [Registre des décisions](00-contexte/registre-decisions.md) | **Toutes les décisions arbitrées**, avec leur date et leur auteur |
| [Écarts au cahier des charges](00-contexte/ecarts-cahier-des-charges.md) | Ce qui diffère du CDC signé et pourquoi — **à faire valider par le client** |
| [Matrice de traçabilité](00-contexte/tracabilite-exigences.md) | Chaque exigence du CDC → jalon → tâche |

## 01 — Architecture

| Document | Contenu |
|---|---|
| [01 — Vue d'ensemble](01-architecture/01-vue-densemble.md) | Contexte, conteneurs, flux de bout en bout |
| [02 — Architecture applicative](01-architecture/02-architecture-applicative.md) | Couches et arborescence des trois applications |
| [03 — Modèle de données](01-architecture/03-modele-de-donnees.md) | Tables, colonnes, énumérations, index |
| [04 — Pipeline médias](01-architecture/04-pipeline-medias.md) | Upload signé, dérivés, IA photo, quotas, files d'attente |
| [05 — Intégrations externes](01-architecture/05-integrations-externes.md) | Cloudinary, remove.bg, wa.me, Vercel |
| [06 — Sécurité](01-architecture/06-securite.md) | Authentification, BFF, surface d'attaque, secrets |
| [07 — Performance, SEO, PWA](01-architecture/07-performance-seo-pwa.md) | Budget de performance, ISR, données structurées |
| [08 — Environnements & déploiement](01-architecture/08-environnements-deploiement.md) | Local, préproduction, production, sauvegardes |
| [ADR](01-architecture/adr/README.md) | Journal des décisions d'architecture, une par fichier |

## 02 — Conventions

| Document | Contenu |
|---|---|
| [Règles du dépôt](02-conventions/regles-du-depot.md) | Branches, commits, PR, revue, propriété du code, CI |
| [Conventions backend](02-conventions/backend.md) | Structure Laravel, validation, erreurs, files d'attente |
| [Conventions frontend](02-conventions/frontend.md) | Next.js, Tailwind, shadcn/ui, accessibilité, formulaires |
| [Contrat frontend / backend](02-conventions/contrat-frontend-backend.md) | **La frontière entre les deux devs** |
| [Stratégie de tests](02-conventions/tests.md) | Ce qui est obligatoire, ce qui est libre |
| [Definition of Done](02-conventions/definition-of-done.md) | Quand une tâche est réellement terminée |

## 03 — API

| Document | Contenu |
|---|---|
| [Conventions API](03-api/README.md) | Versionnement, format d'erreur, pagination, filtres, limitation de débit |
| [`openapi.yaml`](../openapi.yaml) | Le contrat lui-même, à la racine du dépôt |

## 04 — Planning

| Document | Contenu |
|---|---|
| [MVP & jalons](04-planning/mvp-et-jalons.md) | M0 → M4 et V2 : contenu, valeur, critères de sortie |
| [Étapes & chemin critique](04-planning/etapes.md) | Séquencement, dépendances, risques, points de blocage |
| [Backlog des tâches](04-planning/backlog-taches.md) | Tâches BE / FE / OPS, estimées et ordonnées |
| [Répartition de l'équipe](04-planning/repartition-equipe.md) | Qui fait quoi, et comment les deux devs ne se bloquent pas |

## Faire évoluer cette documentation

| Changement | Ce qu'il faut mettre à jour, dans la même PR |
|---|---|
| Choix technique structurant | Un nouvel [ADR](01-architecture/adr/) + les docs impactées |
| Nouveau champ ou nouvelle table | `03-modele-de-donnees.md` + la migration |
| Nouvel endpoint ou changement de réponse | `openapi.yaml` + `03-api/README.md` si une convention change |
| Nouvelle exigence client | Ligne dans la matrice de traçabilité + tâche dans le backlog |
| Écart au CDC | `ecarts-cahier-des-charges.md`, et on prévient le client |
