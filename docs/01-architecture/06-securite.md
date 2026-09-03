# 06 — Sécurité

Le CDC §4 demande « accès au backoffice protégé par authentification ; mots de passe hashés ». C'est le minimum. Ce document décrit ce qu'on fait réellement, et pourquoi.

## Ce qu'on protège, et contre quoi

Un seul compte contrôle **tout** : le contenu du site, les prix, les médias, le numéro WhatsApp affiché aux clients. Il n'y a pas de données bancaires, pas de comptes clients, pas de données personnelles au-delà d'un numéro de téléphone professionnel affiché volontairement.

Le risque principal n'est donc pas le vol de données, c'est la **prise de contrôle du compte unique** : quelqu'un qui change le numéro WhatsApp détourne tous les prospects du garage. C'est le scénario qui dicte les choix ci-dessous.

| Menace | Gravité | Mitigation |
|---|---|---|
| Vol du jeton d'authentification par XSS | **Critique** | BFF, cookie httpOnly — le JS ne voit jamais le jeton |
| Force brute sur la connexion | Élevée | Limitation de débit par IP et par email, journalisation |
| Découverte du backoffice | Moyenne | Sous-domaine séparé, `noindex`, aucun lien entrant |
| Fuite d'un secret de fournisseur | Élevée | Secrets serveur uniquement, jamais préfixés `NEXT_PUBLIC_` |
| Upload de fichier malveillant | Moyenne | Liste blanche MIME, signature contraignante, vérification à la confirmation |
| Injection HTML via le blog | Faible | `body` en texte brut, aucun HTML stocké ni rendu (D16) |
| Injection SQL | Faible | Eloquent et requêtes préparées exclusivement |
| Appel non autorisé du webhook de revalidation | Faible | Secret partagé, comparaison à temps constant |
| Épuisement de quota par abus | Moyenne | Quota compté, limitation de débit sur les endpoints coûteux |

---

## Authentification

**Sanctum, jetons personnels** (D04). Pas de session côté API, pas de cookie de session : l'API est sans état.

### Le BFF, et pourquoi il existe

Le backoffice héberge un éditeur de contenu — c'est-à-dire un endroit où du texte saisi finit affiché. C'est précisément la surface où une XSS peut apparaître. Or avec un jeton en `localStorage`, **une seule XSS donne un accès admin permanent** : le jeton est exfiltré et reste valide.

D'où le BFF :

```
Navigateur ──fetch('/bff/…')──▶ Route handler Next ──Authorization: Bearer──▶ mecano-api.duckdns.org
             cookie httpOnly                          jeton lu côté serveur
             invisible au JS                           uniquement
```

Le code React n'a **aucun moyen** de lire le jeton. Une XSS peut faire des requêtes au nom de l'utilisateur pendant la session, ce qui est déjà grave — mais elle ne peut pas emporter le jeton et revenir plus tard.

### Le cookie du BFF

| Attribut | Valeur | Pourquoi |
|---|---|---|
| `httpOnly` | `true` | Inaccessible au JavaScript. C'est tout l'intérêt |
| `secure` | `true` | HTTPS uniquement |
| `sameSite` | `lax` | Suffisant : le BFF est sur le même hôte que l'app admin |
| `path` | `/` | |
| `maxAge` | 7 jours | Compromis entre confort du mécanicien et fenêtre d'exposition |

Le nom du cookie ne révèle rien (`mc_s`, pas `admin_token`).

### Cycle de vie du jeton

- Créé à la connexion, expiration **7 jours** côté Sanctum. Un jeton perdu ne vaut pas indéfiniment.
- Révoqué à la déconnexion, côté API **et** côté cookie.
- `GET /api/v1/auth/me` sert de vérification : un `401` déclenche la purge du cookie et la redirection vers la connexion.
- Un seul jeton actif à la fois : une nouvelle connexion révoque le précédent. Pour un utilisateur unique, deux jetons valides ne servent à rien et doublent la surface.

### Mot de passe

- Hachage bcrypt, coût 12 (défaut Laravel). Aucun stockage réversible, nulle part.
- Minimum 12 caractères à la création et au changement.
- Confrontation à la liste des mots de passe compromis (`Password::uncompromised()`) — un seul compte, autant qu'il soit solide.
- Pas de récupération par email en V1 : un compte unique, réinitialisable en ligne de commande par l'équipe. Une fonction d'oubli de mot de passe est une surface d'attaque supplémentaire pour un bénéfice nul ici.

---

## Limitation de débit

| Endpoint | Limite | Pourquoi |
|---|---|---|
| `POST /api/v1/auth/login` | 5 / minute / IP **et** 10 / heure / email | Force brute. La double clé empêche de contourner par rotation d'IP |
| `POST /api/v1/cars/{slug}/events` | 60 / minute / IP | Empêche de gonfler les compteurs |
| `POST /admin/media/upload-signature` | 30 / minute | Empêche l'épuisement du quota Cloudinary |
| `POST /admin/media/{id}/enhance` | 10 / minute | Endpoint le plus coûteux du système |
| Autres endpoints admin | 120 / minute | Garde-fou général |
| Endpoints publics de lecture | 300 / minute / IP | Généreux : le SSR de Vercel appelle depuis quelques IP seulement |

Les échecs de connexion sont journalisés avec l'IP et l'horodatage. Cinq échecs consécutifs pour un même email déclenchent une entrée de journal de niveau `warning`.

---

## Le backoffice n'est pas découvrable

Ce n'est pas une mesure de sécurité en soi — c'est une réduction de la surface exposée aux scanners automatiques.

- Hôte séparé : `admin.garage.com`.
- `robots.txt` interdisant tout.
- En-tête `X-Robots-Tag: noindex, nofollow, noarchive` sur **chaque** réponse, y compris la page de connexion.
- Aucun lien depuis la vitrine, pas même en pied de page.
- Aucune mention de l'URL du backoffice dans le dépôt public ni dans les métadonnées de la vitrine.

---

## CORS et origines

L'API n'accepte que des origines connues, déclarées explicitement :

```php
// config/cors.php
'allowed_origins' => [
    env('APP_FRONTEND_URL'),   // https://garage.com
    env('APP_ADMIN_URL'),      // https://admin.garage.com
],
'allowed_methods' => ['GET','POST','PATCH','DELETE','OPTIONS'],
'supports_credentials' => false,   // Bearer, pas de cookie inter-domaines
```

`supports_credentials` est à `false` : c'est le BFF qui porte le cookie, sur son propre domaine. L'API ne reçoit jamais de cookie, uniquement un en-tête `Authorization`. Aucun joker (`*`) n'est accepté, même en développement.

---

## Sécurité des uploads

Le fichier ne traverse pas l'API (D07), donc la validation est déplacée. Détail dans [04 — Pipeline médias](04-pipeline-medias.md#le-prix-à-payer--la-validation-se-déplace).

- Liste blanche MIME, jamais de liste noire.
- La signature d'upload est **contraignante** : le fournisseur refuse lui-même un fichier hors format ou hors taille.
- Vérification par `HEAD` à la confirmation : on ne croit pas ce que le client déclare.
- Aucun nom de fichier fourni par le client n'est réutilisé. La clé de stockage est générée côté serveur (UUID).
- Les fichiers ne sont jamais servis depuis notre domaine : Cloudinary les sert depuis le sien. Une éventuelle charge active dans un fichier ne s'exécute donc pas dans notre origine.

---

## En-têtes de réponse

Côté Next (les deux apps), via `next.config.js` :

| En-tête | Valeur |
|---|---|
| `Strict-Transport-Security` | `max-age=63072000; includeSubDomains` |
| `X-Content-Type-Options` | `nosniff` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `X-Frame-Options` | `DENY` |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` |
| `Content-Security-Policy` | voir ci-dessous |

CSP du backoffice, stricte, car c'est là que la XSS coûterait cher :

```
default-src 'self';
img-src 'self' https://res.cloudinary.com https://media.garage.com data:;
media-src https://media.garage.com;
connect-src 'self' https://api.cloudinary.com https://media.garage.com;
script-src 'self';
style-src 'self' 'unsafe-inline';
frame-ancestors 'none';
```

`connect-src` autorise Cloudinary parce que le navigateur y envoie les fichiers en direct. Il n'autorise **pas** `mecano-api.duckdns.org` : le backoffice ne parle qu'à son BFF.

---

## Le webhook de revalidation

Laravel appelle `POST https://garage.com/api/revalidate`. Sans protection, n'importe qui pourrait invalider le cache en boucle et faire s'écrouler la performance du site.

- En-tête `X-Revalidate-Signature` : HMAC-SHA256 du corps, avec un secret partagé.
- Comparaison **à temps constant** (`hash_equals` / `timingSafeEqual`).
- Horodatage dans le corps, rejet au-delà de 5 minutes d'écart (anti-rejeu).
- Limitation de débit sur la route Next.
- Le secret vit dans le `.env` du serveur et dans les variables Vercel de `apps/web`. Jamais dans le dépôt.

---

## Ce que l'API ne renvoie jamais

Vérifié par des tests dédiés, pas seulement par relecture :

- `password`, `remember_token`, aucun jeton.
- Aucune clé ni secret de fournisseur.
- Aucune annonce `draft` sur un endpoint public.
- Aucun dérivé non approuvé sur un endpoint public.
- Aucune trace de pile ni requête SQL en production (`APP_DEBUG=false`, vérifié au déploiement).
- Aucune adresse IP en clair : `car_events.ip_hash` est un SHA-256 salé.

---

## Sauvegardes et récupération

Décision différée R03 — à figer avant la mise en production de M1.

Proposition : `mysqldump` chiffré quotidien, rétention 30 jours, copie hors du serveur d'exécution. **Une restauration testée au moins une fois** — une sauvegarde jamais restaurée n'est pas une sauvegarde.

Les médias ne sont pas sauvegardés par nous : Cloudinary est durable. En revanche, une base perdue rend les médias inexploitables (les clés de stockage y vivent), ce qui fait de la sauvegarde de la base la seule chose réellement critique.

---

## Revue de sécurité par jalon

| Jalon | À vérifier |
|---|---|
| M0 | Cookie httpOnly effectif, limitation sur `/login`, `noindex` sur toutes les réponses admin, CORS sans joker |
| M1 | Aucune fuite de `draft`, signatures d'upload contraignantes, webhook signé, en-têtes de sécurité en place |
| M2 | `body` d'article rendu sans HTML, CSP effective sur les deux apps |
| M3 | Aucune fuite de dérivé non approuvé, quota infranchissable |
| M4 | Le service worker ne met en cache aucune réponse authentifiée |
