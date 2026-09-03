# 0010 — Les vidéos passent sur Cloudinary, R2 est retiré

- **Statut** : Accepté
- **Date** : 2026-09-04
- **Décideur** : responsable architecture
- **Décision liée** : D23
- **Remplace** : [0006](0006-medias-cloudinary-r2.md) pour la partie vidéo

## Contexte

L'[ADR 0006](0006-medias-cloudinary-r2.md) plaçait les photos sur Cloudinary et
les vidéos sur Cloudflare R2. L'argument était l'égress : R2 ne facture pas la
bande passante sortante, Cloudinary la compte dans les crédits du plan gratuit.
Sur le papier, une vidéo de 80 Mo vue deux cents fois suffisait à justifier le
second hébergeur.

Ce que l'implémentation a montré est différent. R2 a imposé un deuxième jeu
d'identifiants, un deuxième domaine à faire pointer, un deuxième pilote de
stockage, une deuxième forme d'envoi — un PUT présigné là où Cloudinary attend
un POST multipart — et un deuxième fournisseur susceptible de tomber. Cette
double mécanique a d'ailleurs produit un défaut réel : le backoffice envoyait
les vidéos en formulaire vers une URL qui n'accepte que PUT.

Le volume attendu, lui, est resté modeste : deux vidéos par annonce au maximum,
sur un catalogue de quelques dizaines de véhicules.

## Options examinées

### Option A — Garder R2 pour les vidéos

**Apporte** : bande passante vidéo gratuite, quel que soit le succès du site.

**Coûte** : deux fournisseurs à configurer, à surveiller et à payer un jour.
Deux chemins d'envoi à maintenir et à tester dans le backoffice. Un compte de
plus dont dépend la mise en ligne, alors que le garage n'a pas d'équipe
technique pour arbitrer une panne Cloudflare.

### Option B — Tout sur Cloudinary

**Apporte** : un seul compte, un seul jeu de clés, une seule mécanique d'envoi
signé. Le transcodage automatique en prime : `f_auto,q_auto` sert un fichier
adapté au lecteur, là où R2 servait l'original tel quel — ce qui réduit la
bande passante réellement consommée sur mobile.

**Coûte** : la bande passante vidéo est décomptée des crédits du plan gratuit.

## Décision

**Option B.** Les vidéos rejoignent les photos sur Cloudinary. R2 est retiré du
projet : code, configuration, variables d'environnement et contrat.

La complexité d'un second fournisseur se paie tous les jours, en configuration
et en surface de panne. L'égress gratuit ne se paie que si le trafic vidéo
devient important — ce qui n'est pas le cas, et ce qui serait de toute façon
une bonne nouvelle qu'on traiterait alors.

## Conséquences

- `CloudinaryVideoStorage` remplace `R2VideoStorage` derrière le contrat
  `VideoStorage` inchangé ; le reste du pipeline n'a pas bougé.
- La méthode `presignedPutUrl` du contrat devient `signedUploadParams` : elle ne
  décrivait plus la réalité.
- Les vidéos sont servies en `f_auto,q_auto`, pas telles quelles.
- Les variables `R2_*` et le domaine `media.garage.com` disparaissent.
- Le `provider` des médias vidéo vaut désormais `cloudinary`.

## Ce qu'il faudra surveiller

La bande passante Cloudinary devient le point de tension, là où elle ne l'était
pas. **À relire à la fin de M3** : si le poids servi approche les crédits du
plan gratuit, deux issues existent avant de revenir à un second hébergeur —
abaisser la qualité par défaut du lecteur, ou limiter les vidéos aux annonces
les plus chères.
