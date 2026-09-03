# Répartition de l'équipe

Trois rôles, deux développeurs (décision D21).

---

## Les rôles

### Responsable architecture — toi

**Propriétaire de :** `openapi.yaml`, `docs/`, `.github/`.

| Responsabilité | Concrètement |
|---|---|
| Écrire et arbitrer le contrat | `CTR-01` à `CTR-05`. **Avant** l'implémentation, jamais après |
| Maintenir la documentation | Les décisions vivent dans les ADR, pas dans les fils de discussion |
| Faire respecter les règles du dépôt | Via CODEOWNERS et la CI, pas par rappel à l'ordre |
| Tenir la relation client | Validation des écarts au CDC, démonstrations de jalon |
| Poser et maintenir la CI | Les garde-fous ne s'assouplissent pas par PR |
| Préparer l'infrastructure | Domaine, comptes, serveur, secrets |
| Clôturer les jalons | Vérification manuelle, revue de sécurité, déploiement |

**Charge : environ 10,5 j sur les 89 j du V1.** Elle est concentrée en début de jalon (le contrat) et en fin (la clôture), ce qui laisse le milieu de jalon disponible pour la revue de PR.

Le point le plus important de ce rôle : **CODEOWNERS sur `openapi.yaml` est ce qui rend la responsabilité architecturale réelle.** Sans lui, c'est une intention ; avec lui, GitHub refuse la fusion.

### Développeur backend

**Propriétaire de :** `apps/api/`.

Laravel, MySQL, files d'attente, intégrations externes, tests d'API, déploiement de l'API.

**Charge : environ 39,5 j.**

Ce qui appartient à ce rôle et à personne d'autre : les invariants métier. Une annonce sans photo qui ne peut pas être publiée, un quota qui ne peut pas être dépassé, un brouillon qui ne peut pas fuiter. Ce sont les règles dont la violation est silencieuse, et elles vivent dans les actions et les tests.

### Développeur frontend

**Propriétaire de :** `apps/web/`, `apps/admin/`.

Next.js, React, Tailwind, shadcn/ui, SEO technique, performance, accessibilité, BFF, PWA.

**Charge : environ 39 j.**

Ce qui appartient à ce rôle et à personne d'autre : ce que le visiteur perçoit. Le temps de chargement, la stabilité de la mise en page, le fonctionnement sur un téléphone en atelier. Le budget de performance est sa responsabilité, pas celle de l'API.

**L'équilibre 39,5 / 39 n'est pas un hasard** : il découle du découpage par contrat, où chaque fonctionnalité a une moitié de chaque côté.

---

## Comment les deux devs ne se bloquent jamais

Le principe est simple : **le contrat est écrit avant le code, donc les deux côtés démarrent le même jour.**

```
        Responsable architecture          Dev backend              Dev frontend
        ────────────────────────          ───────────              ────────────
Jour 1  Écrit CTR-02
        PR de contrat, fusionnée
                                    ┌─────────────────────┬─────────────────────┐
Jour 2                              │ migrations          │ génère les types    │
Jour 3                              │ modèles, énums      │ écrans sur factices │
Jour 4                              │ endpoints publics   │ catalogue, filtres  │
Jour 5                              │ endpoints admin     │ fiche, galerie      │
Jour 6                              │ médias              │ formulaire annonce  │
                                    └──────────┬──────────┴──────────┬──────────┘
Jour 7  Revue, clôture                        └── fusionne ──────────▶ branche l'API
                                                                       (les types collent déjà)
```

### Les trois choses qui font marcher ce schéma

**1. Les factories, livrées tôt et réalistes** (`BE-09`, dès le début de M1).

Le dev frontend travaille sur `php artisan migrate:fresh --seed`. Le jeu de données doit contenir les cas qui cassent les mises en page : une annonce vendue, une annonce sans photo, une annonce à 20 photos, une annonce sans description, un prix à 7 chiffres, un modèle au nom très long.

Un seeder qui ne produit que des cas parfaits donne une interface qui casse en production. **C'est la principale cause d'échec de ce mode de travail.**

**2. Les exemples dans le contrat.** Chaque schéma d'`openapi.yaml` porte un `example`. Le front s'en sert pour ses données factices, ce qui garantit que ce qu'il construit correspond à ce qui arrivera.

**3. Les cas d'erreur dans le contrat, pas seulement le cas nominal.** Le front doit pouvoir construire l'état `409 QUOTA_EXCEEDED` avant que l'API sache le produire.

---

## Zones de contact — là où il faut se parler

Ce sont les seuls endroits où les deux devs se croisent. Chacun a une règle qui évite la discussion.

| Zone | Le risque | La règle |
|---|---|---|
| **Forme des réponses** | Deux interprétations divergentes | Le contrat tranche. Si ambigu, PR sur `openapi.yaml` |
| **Tags de revalidation** | Le BE oublie un tag → **page périmée, sans erreur** | La table de [07](../01-architecture/07-performance-seo-pwa.md#ce-qui-déclenche-une-revalidation) est contractuelle. Les deux la relisent en PR de contrat |
| **Upload signé** | Le BE signe, le FE envoie, le BE confirme — trois étapes, deux côtés | `BE-18`/`BE-19` **avant** `FE-20`. Le FE ne devine pas la forme de la signature |
| **Codes d'erreur** | Le FE compare sur `message`, qui change | Le FE compare **toujours** sur `code`. Liste exhaustive dans le contrat |
| **`is_publishable`** | Le FE recalcule la règle « photo principale requise » | L'API la calcule, le FE la consomme. Aucune règle métier côté front |
| **Dimensions des photos** | `width`/`height` absents → mise en page qui saute | Champs obligatoires dans le contrat, testés côté API |

**La zone des tags de revalidation est la plus dangereuse** : son mode d'échec ressemble à un bug frontend alors qu'il vient du backend. Toute nouvelle opération d'écriture sur une donnée publique déclare ses tags dans la PR de contrat.

---

## Cadence de travail

| Rituel | Fréquence | Durée | Contenu |
|---|---|---|---|
| Point quotidien | chaque jour | 10 min | Ce que je fais, ce qui me bloque. Pas de rapport d'activité |
| Revue de PR | en continu | — | **Maximum un jour ouvré.** Une PR en attente bloque la moitié de l'équipe |
| Ouverture de jalon | début de jalon | 1 h | Lecture commune du contrat, identification des zones de contact |
| Clôture de jalon | fin de jalon | 1 h | Vérification manuelle, revue de sécurité, démonstration client |

Le délai de revue est la règle la plus importante de cette liste. À deux personnes, il n'y a pas de troisième relecteur : une PR ignorée deux jours arrête littéralement 50 % de la capacité.

---

## Ce que chacun ne fait pas

### Le dev backend ne fait pas

- De décision d'affichage ou de mise en page.
- De formatage pour l'humain (« 4 500 000 FCFA », « il y a 3 jours »).
- D'ajout de champ non prévu au contrat, même utile.
- De modification de `openapi.yaml` sans validation de l'architecture.

### Le dev frontend ne fait pas

- De règle métier. Si une condition d'affichage dépend d'une règle du garage, elle vient de l'API.
- De composition du message WhatsApp, ni de construction d'URL Cloudinary.
- De modification de `types/api.d.ts` (fichier généré).
- D'appel direct à `mecano-api.duckdns.org` depuis `apps/admin` — tout passe par le BFF.

### Le responsable architecture ne fait pas

- De code de fonctionnalité. S'il code, il devient un goulot d'étranglement et perd le recul nécessaire à l'arbitrage.
- De contournement de ses propres règles « pour aller plus vite ». C'est le moyen le plus rapide de les rendre inopérantes.

---

## Si l'équipe change

| Changement | Ce qu'il faut ajuster |
|---|---|
| **Un dev en moins** | Le contrat perd son intérêt principal (le parallélisme) mais garde sa valeur documentaire. Réduire le périmètre : s'arrêter à M2 |
| **Un dev en plus** | Le troisième prend la piste « médias + intégrations » (`BE-17` à `BE-21`, `BE-31` à `BE-37`), la plus isolable du reste |
| **Le rôle architecture est absorbé par un dev** | Le risque devient l'auto-approbation. Garder CODEOWNERS et exiger que la PR de contrat soit approuvée par **l'autre** dev |
