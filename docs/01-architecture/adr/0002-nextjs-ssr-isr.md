# 0002 — Next.js en SSR + ISR pour l'indexation du catalogue

- **Statut** : Accepté
- **Date** : 2026-08-25
- **Décideur** : responsable architecture
- **Décision liée** : D02
- **Écart au CDC** : [E2](../../00-contexte/ecarts-cahier-des-charges.md#e2--le-rendu-serveur-est-assuré-par-nextjs-pas-par-blade)

## Contexte

L'[ADR 0001](0001-api-json-decouplee.md) découple le frontend. Cela crée une obligation nouvelle : le CDC §4 exige une « structure HTML favorisant le SEO (pages statiques/rendues côté serveur) », et §4 exige aussi un chargement en moins de 3 secondes sur mobile.

Ce n'est pas une exigence secondaire. **Le SEO est le canal d'acquisition du garage** : le parcours qui justifie tout le projet commence par une recherche Google sur « Toyota Corolla occasion ». Si les fiches véhicules ne sont pas indexées, la plateforme ne remplace pas le bouche-à-oreille — elle ne sert à rien.

## Options examinées

### Option A — React côté client seul (Vite SPA)

**Apporte** : le plus simple à héberger — des fichiers statiques, aucun processus Node en production, donc compatible avec l'hébergement mutualisé prévu au CDC §5.1.

**Coûte** : le HTML initial est vide. Le contenu n'apparaît qu'après exécution du JavaScript et appel de l'API. Google exécute du JavaScript, mais avec un budget d'exploration limité et de façon différée : sur un site neuf sans autorité, l'indexation est lente, partielle, parfois inexistante. **Cette option contredit directement l'exigence du CDC.**

### Option B — SPA avec prérendu au build

Les pages véhicules sont générées en HTML au moment de la construction, régénérées par webhook à chaque publication.

**Apporte** : hébergement statique gratuit, SEO correct, pas de runtime Node.

**Coûte** : chaque publication déclenche une reconstruction complète du site. Avec quelques dizaines d'annonces c'est tenable ; le mécanicien attend néanmoins plusieurs minutes avant de voir son annonce en ligne. La fraîcheur dépend d'un processus de build, ce qui est fragile.

### Option C — Next.js en SSR + ISR

Rendu serveur, mise en cache au niveau du CDN, régénération à la demande sur invalidation.

**Apporte** : HTML complet dès la première réponse, donc indexation immédiate et fiable. Dans le cas nominal la page est servie depuis le cache CDN, sans solliciter Laravel — la performance est indépendante de la charge du serveur. La publication est visible en quelques secondes. `generateMetadata`, images Open Graph générées, `sitemap.ts` et optimisation d'images font partie du cadre.

**Coûte** : un runtime Node en production, incompatible avec l'hébergement mutualisé du CDC §5.1. Un cache à invalider — nouvelle source de bugs. Deux écosystèmes à opérer.

### Option D — Astro avec îlots React

**Apporte** : les meilleurs scores de performance et de SEO du lot, HTML statique par défaut, JavaScript uniquement là où il y a de l'interactivité.

**Coûte** : le dev frontend doit apprendre Astro, et le backoffice resterait du React classique — deux modèles mentaux différents pour une même personne.

## Décision

**Option C, Next.js en SSR + ISR.**

Deux critères ont tranché. D'abord la fiabilité de l'indexation : l'option A la met en jeu, et c'est le canal d'acquisition du projet — un risque inacceptable. Ensuite l'expérience du mécanicien : il doit publier une annonce et la voir en ligne immédiatement, pas attendre une reconstruction (option B).

L'option D est techniquement supérieure sur la performance pure, mais imposait deux modèles de rendu différents à un seul développeur frontend. Le gain marginal ne le justifiait pas.

L'hébergement mutualisé du CDC §5.1 est abandonné en conséquence : voir [E3](../../00-contexte/ecarts-cahier-des-charges.md#e3--lhébergement-change).

## Conséquences

### Positives

- Exigence SEO du CDC §4 tenue, et de manière plus solide qu'avec du Blade : cache CDN mondial, images optimisées, métadonnées par page.
- Objectif « moins de 3 s sur mobile » atteignable indépendamment de la charge du serveur, puisque le cas nominal ne le sollicite pas.
- Publication visible en quelques secondes.
- Server Components par défaut : très peu de JavaScript envoyé à la vitrine.

### Négatives

- **Un cache à invalider.** C'est la source de bugs la plus subtile du projet : une page périmée est un bug **silencieux** — personne ne voit d'erreur, le visiteur voit juste une donnée fausse. Traité par [ADR 0009](0009-revalidation-isr-webhook.md) et un filet horaire.
- Hébergement mutualisé abandonné (E3).
- Deux écosystèmes à opérer : PHP sur le serveur, Node sur Vercel.
- Dépendance à Vercel pour le confort de l'ISR. Un déménagement resterait possible, mais coûteux.

### Ce que ça implique concrètement

- Tout ce qui doit être indexé est rendu côté serveur. `"use client"` se justifie en revue de PR.
- Les filtres du catalogue vivent dans l'URL, pas dans un état React — sinon ils sont invisibles de Google.
- Chaque route déclare ses tags de revalidation et un `revalidate` d'une heure en filet.
- Lighthouse CI bloque les PR dès M1.

## Quand reconsidérer

Si Vercel devenait payant à un niveau inacceptable pour ce projet, ou si le nombre d'annonces devenait si faible et si stable qu'une génération statique complète (option B) suffirait.
