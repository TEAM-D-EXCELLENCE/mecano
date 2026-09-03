# 05 — Intégrations externes

Le CDC §4 impose « priorité aux outils et API gratuits ou à faible coût ». Chaque intégration ci-dessous est documentée avec son plafond réel et ce qui se passe quand on l'atteint — parce qu'un plan gratuit dont on ignore la limite est une panne à retardement.

## Vue d'ensemble

| Service | Rôle | Plan | Plafond | Si dépassé |
|---|---|---|---|---|
| **Cloudinary** | Stockage et transformation des photos, CDN | Gratuit | ~25 crédits/mois (stockage + transformations + bande passante) | Transformations bridées. Voir mitigation ci-dessous |
| **remove.bg** | Suppression de fond | Gratuit | ~50 appels/mois, résolution réduite | Refus en `409` avant appel. Quota compté (D13) |
| **wa.me** | Contact WhatsApp | Gratuit | aucun | — |
| **Vercel** | Hébergement des deux apps Next | Hobby | 100 Go de bande passante/mois | Passage au plan payant, ou CDN devant |
| **Meta Graph** | Publication Facebook | — | — | **Hors V1** (écart E4) |

## Règle d'intégration : jamais d'appel direct depuis le métier

Chaque service est derrière un contrat dans `app/Support/Contracts/`. Une classe métier ne connaît pas le nom du fournisseur.

```php
interface ImageStorage {
    public function signedUploadParams(string $folder, UploadConstraints $c): SignedUpload;
    public function derivativeUrl(string $key, ImageTransform $t): string;
    public function exists(string $key): ?ObjectMeta;
    public function delete(string $key): void;
}

interface VideoStorage      { /* presignedPut, exists, delete, publicUrl */ }
interface BackgroundRemover { /* remove(string $sourceUrl): RemovedBackground */ }
interface FrontendRevalidator { /* revalidate(array $tags): void */ }
```

Trois bénéfices concrets : les tests tournent sans réseau (implémentations factices), changer de fournisseur ne touche pas le métier, et les modes dégradés sont explicites au lieu d'être des exceptions qui remontent.

---

## Cloudinary — photos

### Configuration

```
CLOUDINARY_CLOUD_NAME=
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=      # secret — jamais côté client, jamais dans le dépôt
CLOUDINARY_UPLOAD_FOLDER=mecano/cars
```

### Signature d'upload

Laravel signe un `timestamp` + les paramètres autorisés avec le secret. La signature contraint le dossier, la taille maximale et les formats acceptés : **Cloudinary refuse lui-même** un fichier hors contrainte. La restriction n'est pas déclarative, elle est appliquée à la source.

Durée de validité : 10 minutes. Au-delà, le front redemande une signature.

### Transformations utilisées

Toutes gratuites sur le plan de base :

| Paramètre | Effet |
|---|---|
| `f_auto` | Sert WebP ou AVIF selon le navigateur |
| `q_auto` | Compression adaptative |
| `c_fill,g_auto` | Recadrage centré sur le sujet détecté |
| `c_limit` | Redimensionne sans jamais agrandir |
| `e_improve`, `e_auto_contrast`, `e_sharpen` | L'« amélioration automatique » du CDC §3.2 |

**Écarté :** `e_background_removal`, qui est un add-on payant. C'est pourquoi la suppression de fond passe par remove.bg (écart E6).

### Maîtriser la consommation de crédits

Le plan gratuit se consomme par transformations **distinctes** générées. Trois règles :

1. **Un jeu fermé de dérivés** (`thumb`, `card`, `detail`, `og`) et pas de dimensions arbitraires. Une largeur dictée par la fenêtre du navigateur générerait des dizaines de variantes par photo.
2. **Transformations nommées** côté Cloudinary plutôt qu'en clair dans l'URL : plus courtes, mises en cache, et modifiables sans redéployer.
3. **Purge des orphelins et des annonces archivées** pour libérer du stockage.

Surveillance : le tableau de bord Cloudinary une fois par mois, tant que le volume ne justifie pas d'automatiser.

---

## Cloudinary — vidéos

Aucune variable supplémentaire : les vidéos utilisent le même compte et les
mêmes identifiants que les photos, sur le point d'entrée `video` de Cloudinary.

```
# le dossier d'envoi est partagé avec les photos
CLOUDINARY_UPLOAD_FOLDER=mecano/cars
```

Upload direct signé, signature valable 15 minutes, comme pour les photos. La
diffusion passe par `f_auto,q_auto` : Cloudinary choisit le conteneur et le
débit selon le lecteur, ce qui évite de servir le fichier d'origine à un
téléphone sur réseau mobile.

**Un seul hébergeur plutôt que deux** ([ADR 0010](adr/0010-videos-sur-cloudinary.md)).
Cloudflare R2 avait été retenu pour son égress gratuit, mais faire vivre un
second fournisseur — deuxième jeu de clés, deuxième domaine, deuxième pilote,
deuxième panne possible — coûtait plus cher en complexité que la bande
passante économisée sur deux vidéos par annonce.

**Ce qu'il faut surveiller.** La bande passante Cloudinary est comptée dans les
crédits du plan gratuit. Deux vidéos par annonce très regardées peuvent y peser
lourd : le poids servi est à relire à la fin de M3.

---

## remove.bg — suppression de fond

```
REMOVE_BG_API_KEY=
REMOVE_BG_MONTHLY_QUOTA=50      # recopié dans integration_quotas pour garder l'historique
```

Appel serveur à serveur : Laravel envoie l'URL de la photo Cloudinary, reçoit un PNG détouré, le renvoie vers Cloudinary comme dérivé, puis compose un fond studio uniforme.

Le comptage, la protection contre la double soumission et le remboursement en cas d'échec sont décrits dans [04 — Pipeline médias](04-pipeline-medias.md#le-quota-removebg).

**Limite qualitative à connaître :** le plan gratuit renvoie une résolution réduite (~0,25 Mpx). Suffisant pour une vignette de catalogue ou une image de partage, **insuffisant pour la galerie en plein écran**. Conséquence : un dérivé sans fond est utilisé pour la carte et l'Open Graph, mais la galerie continue d'afficher l'original haute résolution. À dire au client — c'est exactement ce que lève l'add-on payant (décision différée R04).

---

## WhatsApp — lien wa.me

Aucune API, aucune authentification (CDC §5.3). L'API **est construite côté serveur** et renvoyée prête à l'emploi :

```
https://wa.me/<numéro>?text=<message urlencodé>
```

Message type :

```
Bonjour, je suis intéressé par la Toyota Corolla 2018 (4 500 000 FCFA)
que j'ai vue sur votre site.
https://garage.com/voitures/toyota-corolla-2018-42
```

**Pourquoi côté serveur.** Le message contient le nom du garage, le prix formaté et l'URL canonique — trois éléments qui appartiennent au métier. Si le front le composait, chaque changement de formulation exigerait un redéploiement du front, et les deux apps pourraient diverger. Le numéro vit dans `settings.whatsapp_number`, modifiable par le mécanicien sans intervention technique.

Le champ s'appelle `whatsapp_url` dans les réponses publiques. Il est **absent** quand `status = sold` : on ne propose pas de contacter le garage pour un véhicule déjà vendu (D14).

Le clic est journalisé via `POST /api/v1/cars/{slug}/events` — c'est l'indicateur de succès du projet.

---

## Vercel — hébergement des deux apps

Deux projets distincts, un par app (D03), chacun connecté au même dépôt avec un répertoire racine différent.

| Projet | Répertoire racine | Domaine |
|---|---|---|
| `mecano-web` | `apps/web` | `garage.com`, `www.garage.com` |
| `mecano-admin` | `apps/admin` | `admin.garage.com` |

Le déploiement se fait sur fusion dans `main`. Les PR produisent des aperçus, qui servent de préproduction de fait (décision différée R02).

**Le domaine doit être branché dès le début du projet**, pas à la fin : la configuration des cookies du BFF, les origines CORS de l'API et les URL canoniques du SEO en dépendent toutes.

---

## Meta Graph API — hors V1

Reporté en V2, écart E4. Rien n'est développé, aucune table n'est créée.

**Ce qu'il faut néanmoins savoir dès maintenant**, parce que c'est administratif et lent :

- La publication n'est possible que sur une **Page professionnelle**, jamais sur un profil personnel (CDC §5.2).
- Elle exige la permission `pages_manage_posts`, soumise à revue Meta : **plusieurs semaines**, sans garantie.
- La revue exige une application Meta configurée, une politique de confidentialité en ligne et une vidéo de démonstration.

**Recommandation :** si le client veut cette fonctionnalité en V2, lancer la démarche maintenant. Le délai est indépendant du développement — l'attendre plus tard coûte le même temps, mais en fin de projet.

---

## Gestion des secrets

| Secret | Où il vit | Où il ne doit **jamais** apparaître |
|---|---|---|
| `CLOUDINARY_API_SECRET` | `.env` du serveur | dépôt, front, réponse d'API |
| `REMOVE_BG_API_KEY` | `.env` du serveur | idem |
| `REVALIDATE_SECRET` | `.env` du serveur **et** variables Vercel de `apps/web` | dépôt |
| `API_TOKEN` du mécanicien | cookie httpOnly du BFF | `localStorage`, code React, journaux |
| `APP_KEY` | `.env` du serveur | dépôt |

`.env.example` contient toutes les clés avec des valeurs vides et un commentaire. C'est le seul fichier d'environnement versionné.

Les variables Vercel sont saisies dans l'interface du projet, pas dans un fichier. Une variable exposée au navigateur doit porter le préfixe `NEXT_PUBLIC_` — **si un secret a besoin de ce préfixe, c'est que l'architecture est fausse**, et il faut passer par le BFF.
