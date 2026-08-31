# 0011 — L'API est livrée sous forme d'image Docker

- **Statut** : Accepté
- **Date** : 2026-08-31
- **Décideur** : responsable architecture

## Contexte

[08 — Environnements](../08-environnements-deploiement.md) décrivait un déploiement sur serveur nu : installer PHP 8.4 et ses extensions, Nginx, Composer, Supervisor, puis déposer le code et recharger. C'est la façon la plus courante de déployer Laravel, et elle a un défaut connu : **l'environnement d'exécution n'est écrit nulle part**. Il vit dans l'historique des commandes tapées sur le serveur.

Deux conséquences concrètes pour ce projet. D'abord, personne ne peut reproduire la production : un développeur qui reprend le projet dans six mois ne saura pas quelles extensions PHP sont actives ni dans quelles versions. Ensuite, une différence entre la machine de développement et le serveur ne se manifeste qu'après déploiement, c'est-à-dire au pire moment.

La base de données étant partie chez Supabase ([ADR 0010](0010-postgresql-supabase.md)), le serveur ne porte plus que l'API. Le conteneuriser devient simple : un seul processus applicatif, aucun état à préserver.

## Options examinées

### Option A — Déploiement sur serveur nu

**Apporte** : rien à apprendre, aucun outillage supplémentaire, un déploiement qui se résume à `git pull` et un rechargement.

**Coûte** : un environnement non reproductible et non versionné. Les mises à jour de PHP se font à la main, sur un serveur en production, sans possibilité de retour arrière rapide.

### Option B — Image Docker

**Apporte** : l'environnement d'exécution devient un fichier versionné, relu comme le reste du code. Le retour arrière consiste à redémarrer l'image précédente. La même image tourne en local et en production, ce qui supprime toute une classe d'écarts.

**Coûte** : un `Dockerfile` à maintenir, une image à construire et à publier, et une couche d'indirection lors du débogage.

### Option C — Plateforme managée (Railway, Fly, Render)

**Apporte** : ni serveur ni image à gérer.

**Coûte** : un coût récurrent, et l'abandon du serveur d'Excellence Team déjà disponible et déjà payé. Contraire au CDC §4 (« priorité aux outils gratuits ou à faible coût »).

## Décision

**Option B.**

Le critère décisif est la reproductibilité. Le projet est repris par des développeurs différents à des mois d'intervalle ; un environnement qui n'existe que dans la mémoire d'une machine est une dette qui se paie au premier incident.

Un seul conteneur regroupe Nginx, PHP-FPM, le worker de file d'attente et le planificateur, orchestrés par Supervisor. C'est contraire à l'usage « un processus par conteneur », et c'est délibéré : pour une application unique sur un serveur unique, quatre conteneurs à coordonner coûteraient plus qu'ils n'apporteraient.

## Conséquences

### Positives

- L'environnement d'exécution est versionné, relu, et identique partout.
- Le retour arrière est immédiat : on redémarre l'image précédente.
- Le worker de file d'attente et le planificateur démarrent avec l'application. Ils étaient jusqu'ici à installer à la main, et **c'est exactement le genre de chose qu'on oublie** : sans planificateur, la purge des envois orphelins et l'agrégation nocturne ne s'exécutent jamais, sans qu'aucune erreur ne le signale.
- Les migrations s'appliquent au démarrage, sous verrou (`migrate --isolated`).

### Négatives

- Un `Dockerfile` de plus à maintenir, et une image à reconstruire à chaque mise à jour de PHP.
- Regrouper quatre processus dans un conteneur écarte l'usage courant : un processus qui meurt est relancé par Supervisor, pas par l'orchestrateur, et les journaux de tous se mêlent sur la sortie standard.
- Le débogage passe par `docker compose exec`, moins direct qu'un accès SSH.

### Ce que ça implique concrètement

- `apps/api/Dockerfile`, `docker-compose.yml` et `docker/` portent l'environnement d'exécution.
- Le conteneur refuse de démarrer sans `APP_KEY` : mieux vaut un échec au démarrage qu'une erreur de chiffrement à la première requête.
- Un terminateur TLS reste nécessaire devant le conteneur. Il impose de déclarer `TRUSTED_PROXIES`, sans quoi toutes les limitations de débit deviennent globales au lieu d'être appliquées par visiteur.
