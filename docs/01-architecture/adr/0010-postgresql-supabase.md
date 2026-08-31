# 0010 — PostgreSQL managé (Supabase) plutôt que MySQL auto-hébergé

- **Statut** : Accepté
- **Date** : 2026-08-31
- **Décideur** : responsable architecture
- **Remplace** : le choix MySQL 8 de [01 — Vue d'ensemble](../01-vue-densemble.md) et de [03 — Modèle de données](../03-modele-de-donnees.md)

## Contexte

L'architecture initiale plaçait MySQL 8 sur le serveur d'Excellence Team, aux côtés de l'API. Ce choix n'a jamais été consigné dans un ADR : il est arrivé avec le reste de la documentation, comme une évidence.

Il ne l'était pas. Une base auto-hébergée impose à l'équipe trois responsabilités permanentes : les sauvegardes et leur restauration, les mises à jour de sécurité, et la surveillance de l'espace disque. Pour deux développeurs dont aucun n'est administrateur système, chacune est une source de panne silencieuse — une sauvegarde qu'on croit faite est pire que pas de sauvegarde du tout.

Le code a été écrit contre PostgreSQL avant que cette décision ne soit posée. Cet ADR régularise l'écart plutôt que de le laisser vivre.

## Options examinées

### Option A — MySQL 8 sur le serveur d'Excellence Team

**Apporte** : aucune dépendance externe, aucun coût, une latence nulle entre l'API et la base puisqu'elles partagent la machine.

**Coûte** : les sauvegardes, les mises à jour et la surveillance retombent sur l'équipe. La restauration n'est jamais testée tant qu'on n'en a pas besoin, et c'est alors trop tard. Le serveur devient un point de défaillance unique qui emporte l'API **et** les données.

### Option B — PostgreSQL managé (Supabase)

**Apporte** : sauvegardes automatiques et restauration à un instant donné, mises à jour appliquées par le fournisseur, surveillance incluse. Le plan gratuit couvre largement le volume attendu — quelques dizaines d'annonces et leurs événements. La base survit à la perte du serveur d'API.

**Coûte** : une dépendance externe de plus, une latence réseau sur chaque requête, et un plafond de connexions qui impose de passer par le pooler. Le plan gratuit met le projet en pause après une période d'inactivité prolongée.

### Option C — PostgreSQL auto-hébergé

Cumule la charge d'exploitation de A et le changement de moteur de B, sans le bénéfice des sauvegardes managées. Écartée d'emblée.

## Décision

**Option B.**

Le critère décisif est la sauvegarde. Le CDC engage l'équipe sur les données d'un commerçant : ses annonces, son historique de ventes. Les perdre est le seul incident dont on ne se remet pas, et c'est précisément celui qu'une équipe de deux personnes est la moins bien placée pour prévenir.

La latence réseau est le prix accepté. Elle est sans effet sur le parcours critique : la vitrine sert des pages en cache ISR et n'interroge la base qu'à la régénération.

## Conséquences

### Positives

- Les sauvegardes existent sans que personne n'ait à y penser, et la restauration est un bouton.
- La perte du serveur d'API ne coûte plus les données.
- PostgreSQL offre les index partiels, ce qui **simplifiera** la contrainte de rôle exclusif des médias : la colonne générée `exclusive_role` n'est plus le seul moyen de l'exprimer.

### Négatives

- Une dépendance externe de plus, avec son propre tableau de bord et ses propres secrets.
- Latence réseau sur chaque requête. Sensible sur le backoffice, invisible sur la vitrine.
- Le plan gratuit met le projet en pause après inactivité prolongée : le premier appel après une pause est lent.
- **Les tests tournent sur SQLite en mémoire.** Aucun test ne touche PostgreSQL, donc aucun ne couvre les différences de moteur — au premier rang desquelles la colonne générée `exclusive_role`. C'est le risque résiduel le plus concret de cet ADR.

### Ce que ça implique concrètement

- `DB_CONNECTION=pgsql`, connexion par le pooler Supabase, `DB_SSLMODE=require`.
- [03 — Modèle de données](../03-modele-de-donnees.md) et [08 — Environnements](../08-environnements-deploiement.md) sont mis à jour.
- Le point R03 du [registre des décisions](../../00-contexte/registre-decisions.md) (fréquence et rétention des sauvegardes) devient un réglage Supabase et non une tâche d'exploitation.
