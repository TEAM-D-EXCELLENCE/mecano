# 0004 — Jeton Bearer détenu par un BFF Next en cookie httpOnly

- **Statut** : Accepté
- **Date** : 2026-08-25
- **Décideur** : responsable architecture
- **Décision liée** : D04

## Contexte

Un seul compte administre toute la plateforme : contenu, prix, médias, et **le numéro WhatsApp affiché aux clients**. La compromission de ce compte ne fait pas perdre des données — elle détourne tous les prospects du garage. C'est le scénario de sécurité qui compte.

Le backoffice est hébergé sur `admin.garage.com` (Vercel), l'API sur `mecano-api.duckdns.org` (serveur Excellence Team). Ce sont **deux domaines distincts** : le mode cookie de session de Sanctum, qui exige un domaine racine commun, n'est pas applicable. L'authentification se fait donc par jeton Bearer.

Reste la question qui compte : **où vit ce jeton dans le navigateur ?**

Élément aggravant : le backoffice contient un éditeur d'articles, c'est-à-dire un endroit où du texte saisi finit affiché. C'est exactement la surface où une faille XSS peut apparaître.

## Options examinées

### Option A — Jeton en `localStorage`

Le client React lit le jeton et le place dans l'en-tête `Authorization`.

**Apporte** : le plus simple. Aucune couche intermédiaire, aucun proxy, fonctionne quels que soient les domaines.

**Coûte** : `localStorage` est lisible par tout JavaScript de la page. **Une seule XSS exfiltre le jeton, qui reste valide après le départ de l'attaquant** — accès administrateur persistant, sans aucune trace. Par ailleurs le rendu serveur ne peut pas lire `localStorage`, donc aucun garde d'authentification côté serveur : chaque page se protège côté client, avec le clignotement que cela implique.

### Option B — Jeton en mémoire seule, rafraîchi au chargement

**Apporte** : le jeton ne persiste jamais sur disque, la fenêtre d'exposition se limite à la session en cours.

**Coûte** : il faut un jeton de rafraîchissement stocké quelque part — donc la même question, déplacée. Et le mécanicien se reconnecte à chaque ouverture d'onglet.

### Option C — BFF : cookie httpOnly côté Next

Le code React n'a pas de jeton. Il appelle ses propres route handlers Next (`/bff/*`), qui lisent le jeton dans un cookie httpOnly du domaine `admin.garage.com` et le retransmettent en `Bearer` à l'API.

**Apporte** : le JavaScript de la page **n'a aucun moyen** de lire le jeton. Une XSS peut agir pendant la session, ce qui est déjà grave, mais elle ne peut pas emporter le jeton et revenir plus tard. Le rendu serveur voit le cookie, donc les gardes d'authentification sont côté serveur : pas de contenu protégé qui clignote avant redirection. Fonctionne entre domaines distincts.

**Coûte** : une couche de proxy à écrire et maintenir. Un saut réseau supplémentaire par requête. Les appels passent par Vercel, ce qui ajoute de la latence.

## Décision

**Option C, le BFF.**

Le critère décisif est la persistance du vol. Avec `localStorage`, une XSS donne un accès administrateur **durable** ; avec le BFF, elle donne un accès limité à la session, dans le navigateur de la victime. La différence de gravité est considérable, et le backoffice contient précisément la surface (un éditeur de contenu) où une XSS est le plus plausible.

Le coût — un proxy et un saut réseau — est supporté par le backoffice, utilisé par une seule personne. Il n'affecte pas la vitrine publique, où la performance compte.

## Conséquences

### Positives

- Le jeton est inaccessible au JavaScript. La classe d'attaque « vol de jeton persistant » est éliminée.
- Gardes d'authentification côté serveur : aucun contenu protégé n'apparaît avant redirection.
- Fonctionne entre domaines distincts, sans contrainte sur l'hébergement.
- L'API ne reçoit **jamais** de cookie : `supports_credentials` reste à `false`, la surface CSRF côté API est nulle.
- Le backoffice n'a besoin d'aucune variable `NEXT_PUBLIC_`. Si l'une devient nécessaire, c'est un signal d'alarme.

### Négatives

- **Une couche de proxy à écrire.** `apps/admin/app/bff/[...path]/route.ts` doit relayer méthode, corps, en-têtes pertinents et codes d'erreur fidèlement. C'est du code peu intéressant mais qui doit être juste.
- **Latence supplémentaire.** Navigateur → Vercel → serveur, au lieu de navigateur → serveur.
- Le BFF est un point de passage unique : s'il est mal écrit, il peut masquer des erreurs de l'API et rendre le débogage pénible.
- Une XSS reste dangereuse pendant la session active. Le BFF réduit la gravité, il ne remplace pas la CSP.

### Ce que ça implique concrètement

- Cookie `mc_s` : `httpOnly`, `secure`, `sameSite=lax`, `maxAge` 7 jours, nom non révélateur.
- Jeton Sanctum expirant à 7 jours ; un seul jeton actif à la fois, une nouvelle connexion révoque le précédent.
- CSP stricte sur le backoffice, avec `connect-src` autorisant Cloudinary et R2 (upload direct) mais **pas** `mecano-api.duckdns.org` — le backoffice ne parle qu'à son BFF.
- Limitation de débit sur `/login` : 5/min par IP **et** 10/h par email.
- `noindex` sur toutes les réponses du backoffice, y compris la page de connexion.

Détail dans [06 — Sécurité](../06-securite.md).

## Quand reconsidérer

Si l'API et le backoffice se retrouvaient un jour sur le même domaine racine, le mode cookie de session de Sanctum deviendrait applicable et supprimerait le besoin de proxy. Ce serait une simplification nette — à saisir si l'occasion se présente.
