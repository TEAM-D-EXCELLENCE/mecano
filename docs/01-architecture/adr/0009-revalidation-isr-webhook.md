# 0009 — Revalidation ISR par webhook signé

- **Statut** : Accepté
- **Date** : 2026-08-25
- **Décideur** : responsable architecture
- **Décision liée** : D09

## Contexte

L'[ADR 0002](0002-nextjs-ssr-isr.md) met les pages publiques en cache pour tenir le budget de performance. Ce cache crée un problème nouveau : **quand le mécanicien publie une annonce ou la marque vendue, la page en cache est fausse.**

L'enjeu dépasse le confort. Une annonce vendue mais affichée disponible fait perdre du temps au visiteur **et** au mécanicien, qui reçoit des messages WhatsApp pour une voiture déjà partie. Un prix modifié mais affiché à l'ancienne valeur est un problème commercial.

Enjeu de confiance, aussi : si le mécanicien publie et ne voit rien changer, il conclut que l'outil ne fonctionne pas. Il republie, il appelle. La perception de fiabilité de toute la plateforme se joue là.

## Options examinées

### Option A — Revalidation par délai

Chaque page se rerend automatiquement au bout de N secondes si elle est visitée.

**Apporte** : aucune plomberie, aucun secret à gérer, aucun point de défaillance. Robuste par construction.

**Coûte** : un décalage systématique. Avec 60 secondes, le mécanicien publie et ne voit rien pendant une minute — **il va croire que ça n'a pas marché**. Avec un délai court, on rerend des pages inutilement.

### Option B — Revalidation à la demande par webhook

Laravel appelle un endpoint Next protégé, qui invalide précisément les tags concernés.

**Apporte** : mise à jour en quelques secondes, aucun rendu inutile, invalidation ciblée (la fiche, le catalogue, le sitemap). C'est le comportement qu'attend un utilisateur : je publie, je rafraîchis, c'est là.

**Coûte** : une plomberie à écrire, un secret partagé, et un point de défaillance dont **l'échec est silencieux** — personne ne voit d'erreur, le visiteur voit juste une donnée périmée.

### Option C — Les deux

Webhook pour l'immédiat, délai long (1 h) en filet de sécurité.

**Apporte** : la réactivité du webhook, plus la garantie qu'un webhook perdu ne laisse pas une page périmée indéfiniment.

**Coûte** : la plomberie de l'option B, et quelques rendus superflus dus au filet.

### Option D — Pas de cache du tout

**Apporte** : toujours frais, rien à invalider.

**Coûte** : chaque visite frappe le serveur Laravel. Le budget « moins de 3 s » devient dépendant de sa charge — ce qui annule le bénéfice principal de l'ADR 0002.

## Décision

**Option C : webhook signé, plus un filet de 3600 secondes sur chaque route.**

Le webhook seul serait suffisant dans le cas nominal, mais son mode d'échec est le pire possible : **silencieux**. Aucune alerte, aucune erreur visible, juste une page fausse servie indéfiniment. Un mode d'échec silencieux et permanent n'est pas acceptable pour des données commerciales — un prix erroné servi pendant une semaine est un vrai problème.

Le filet horaire borne les dégâts à une heure. C'est le prix de quelques rendus superflus, ce qui est négligeable au regard du risque couvert.

## Conséquences

### Positives

- Publication visible en quelques secondes : le mécanicien fait confiance à l'outil.
- Invalidation ciblée par tags : on ne rerend que ce qui a changé.
- Un webhook perdu ne cause au pire qu'une heure de décalage.
- Le sitemap est régénéré avec les pages, donc jamais périmé.

### Négatives

- **Plomberie à écrire des deux côtés** : un job Laravel avec réessais, une route handler Next avec vérification de signature.
- **Un secret partagé** entre le `.env` du serveur et les variables Vercel. Deux endroits à tenir synchronisés.
- **Mode d'échec silencieux**, atténué mais non supprimé : jusqu'à une heure de décalage possible.
- Quelques rendus superflus dus au filet horaire.
- Débogage délicat : « la page est périmée » peut venir du webhook, du tag, du cache CDN ou du navigateur. Cette chaîne doit être connue de l'équipe.

### Ce que ça implique concrètement

**Sécurité du webhook** — HMAC-SHA256 du corps dans `X-Revalidate-Signature`, comparaison à temps constant, horodatage avec rejet au-delà de 5 minutes (anti-rejeu), limitation de débit sur la route Next. Sans cela, n'importe qui pourrait invalider le cache en boucle et faire s'écrouler la performance du site.

**Convention de tags** — `car:{slug}`, `cars`, `home`, `services`, `posts`, `post:{slug}`, `settings`. Toute route déclare les tags qu'elle consomme, tout écrit déclare les tags qu'il invalide. La table de correspondance vit dans [07 — Performance et SEO](../07-performance-seo-pwa.md#ce-qui-déclenche-une-revalidation) et **fait partie du contrat entre les deux devs** : un dev backend qui ajoute un écrit sans invalider le bon tag crée un bug côté front.

**Fiabilité** — `RevalidateFrontend` est un job en file d'attente avec 5 réessais à délai croissant, jamais un appel synchrone : une revalidation lente ne doit pas ralentir la réponse du backoffice. Un échec définitif lève une alerte, puisque c'est le seul cas invisible de l'utilisateur.

**Test obligatoire dès M1** — publier une annonce et vérifier que la page publique est à jour en moins de 10 secondes. C'est un critère de sortie de M1, pas un test optionnel.

## Quand reconsidérer

Si les échecs de webhook devenaient fréquents (réseau instable entre le serveur et Vercel), il faudrait raccourcir le filet ou basculer sur une interrogation périodique côté Next. Si à l'inverse aucun échec n'était constaté sur plusieurs mois, le filet pourrait être allongé pour économiser des rendus.
