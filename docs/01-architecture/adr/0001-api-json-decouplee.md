# 0001 — Backend = API JSON uniquement, découplée du frontend

- **Statut** : Accepté
- **Date** : 2026-08-25
- **Décideur** : responsable architecture
- **Décision liée** : D01
- **Écart au CDC** : [E1](../../00-contexte/ecarts-cahier-des-charges.md#e1--le-frontend-nest-plus-en-blade-mais-en-react--nextjs)

## Contexte

Le CDC §5.1 prévoit « Blade + Tailwind CSS » : un monolithe Laravel classique où le même code produit le HTML et porte le métier.

L'équipe compte deux personnes **spécialisées** : un développeur backend et un développeur frontend. Dans un monolithe Blade, la frontière entre eux est une convention informelle sur des vues partagées — qui a le droit de faire une requête Eloquent dans une vue, qui possède le fichier, qui casse quoi. Cette frontière est difficile à faire respecter et impossible à vérifier automatiquement.

Par ailleurs, le projet doit servir **deux interfaces** aux besoins très différents : une vitrine publique optimisée pour le SEO et la performance mobile, et un backoffice riche en interactions (upload multiple, aperçu avant/après, polling de traitements asynchrones).

## Options examinées

### Option A — Monolithe Blade, conforme au CDC

Laravel rend les vues. Alpine.js pour l'interactivité.

**Apporte** : une seule base de code, un seul déploiement, aucun contrat à maintenir, aucune duplication de types. Le plus rapide à démarrer, et strictement conforme au document contractuel.

**Coûte** : la frontière entre les deux devs reste informelle et non vérifiable. Le dev frontend doit connaître Laravel et Blade. Le backoffice interactif demande beaucoup de JavaScript écrit à la main dans des vues.

### Option B — Livewire pour le backoffice, Blade pour le public

**Apporte** : très peu de JavaScript à écrire, l'interactivité vient du serveur.

**Coûte** : le dev frontend travaille dans du PHP. Sa spécialisation n'est pas utilisée. Écart au CDC quand même.

### Option C — API JSON + frontend React découplé

Laravel n'expose que du JSON. Deux applications React consomment l'API.

**Apporte** : la frontière devient un **contrat HTTP explicite et versionné**, vérifiable automatiquement. Chaque dev travaille dans sa technologie et sa base de code. Le front peut avancer sur des données factices sans attendre l'API. Le backoffice interactif est bien plus naturel à écrire en React.

**Coûte** : trois bases de code, trois déploiements, un contrat à maintenir, des types dupliqués, et une exigence supplémentaire — le rendu serveur, sans quoi le SEO du CDC §4 est perdu.

## Décision

**Option C.** Le critère décisif est la nature de l'équipe : deux spécialistes, dont la productivité dépend de travailler chacun dans son domaine sans se bloquer. Un contrat HTTP explicite est le seul mécanisme qui rend cette séparation vérifiable — la CI peut valider un schéma OpenAPI, elle ne peut pas valider une convention sur des vues Blade.

Le SEO n'est pas sacrifié : voir [ADR 0002](0002-nextjs-ssr-isr.md).

## Conséquences

### Positives

- Frontière contractualisée, vérifiable en CI.
- Les deux devs travaillent en parallèle, sur des dépôts logiques distincts, sans conflit de fusion.
- Le frontend est remplaçable sans toucher au métier ; une application mobile native (hors périmètre CDC) deviendrait possible sans refonte.
- Le backoffice, très interactif, est écrit dans la technologie adaptée.
- Les tests d'API testent le métier directement, sans passer par du HTML.

### Négatives

- **Trois bases de code au lieu d'une.** Trois jeux de dépendances, trois pipelines.
- **Un contrat à maintenir.** Chaque changement de réponse se propage en trois endroits.
- **Le SEO devient une exigence explicite à traiter**, alors qu'il était gratuit avec Blade. C'est le coût le plus important de cette décision.
- **Écart à un document contractuel**, à faire valider par le client (E1).
- Un saut réseau supplémentaire sur chaque page.

### Ce que ça implique concrètement

- `apps/api` ne contient **aucune vue**. `resources/views` est supprimé.
- Aucun endpoint ne renvoie de HTML, jamais.
- La couche `Actions` porte le métier, pas les contrôleurs.
- Deux familles de ressources, publiques et admin, sans classe partagée — voir [02 — Architecture applicative](../02-architecture-applicative.md).

## Quand reconsidérer

Si l'équipe passait à un seul développeur fullstack, la justification principale disparaîtrait et le coût des trois bases de code deviendrait difficile à défendre.
