# 0007 — Upload direct signé depuis le navigateur

- **Statut** : Accepté
- **Date** : 2026-08-25
- **Décideur** : responsable architecture
- **Décision liée** : D07

## Contexte

Le mécanicien envoie environ 8 photos (jusqu'à 15 Mo chacune) et 2 vidéos (jusqu'à 200 Mo) par annonce. Il faut décider par où passent ces fichiers.

Contrainte technique concrète : PHP impose `upload_max_filesize`, `post_max_size` et un délai maximal d'exécution. Une vidéo de 200 Mo traversant Laravel exige de relever ces trois limites, ce qui augmente d'autant la surface d'un déni de service.

Contrainte d'usage : le mécanicien travaille probablement sur une connexion mobile. Un upload qui échoue au bout de dix minutes parce qu'un délai a expiré est une fonctionnalité inutilisable.

## Options examinées

### Option A — Upload à travers Laravel

Le fichier arrive dans l'API, qui le valide puis le republie vers Cloudinary ou R2.

**Apporte** : validation centralisée au moment de l'upload — type MIME réel lu dans le fichier, dimensions vérifiées, analyse antivirus possible. Un seul chemin de code, facile à raisonner et à tester.

**Coûte** : le serveur encaisse tout le trafic de fichiers, et le fichier est transféré **deux fois** (navigateur → serveur, serveur → fournisseur), ce qui double la durée. Les limites PHP doivent être relevées. Un upload de 200 Mo occupe un processus PHP-FPM pendant toute sa durée — quelques uploads simultanés suffisent à saturer le serveur.

### Option B — Upload direct signé

Laravel ne délivre qu'une signature. Le navigateur envoie le fichier directement à Cloudinary ou R2, puis confirme à l'API.

**Apporte** : le serveur ne voit que des métadonnées. Aucune limite PHP à relever. Un seul transfert, donc deux fois plus rapide. Le serveur reste disponible pendant les uploads. Indispensable pour les vidéos.

**Coûte** : la validation ne peut plus se faire au moment de l'upload, puisque le fichier ne passe pas par nous. Deux allers-retours supplémentaires (signature, confirmation). Un upload non confirmé laisse un fichier orphelin.

### Option C — Mixte : photos via Laravel, vidéos en direct

**Apporte** : chaque chemin est optimal pour son cas. Les photos, petites, sont validées à travers l'API ; les vidéos, lourdes, partent en direct.

**Coûte** : deux chemins de code à écrire, tester et maintenir, pour un bénéfice limité — les photos font 15 Mo au plus, ce qui ne pose pas de vrai problème dans l'option B.

## Décision

**Option B, upload direct signé pour tout.**

Le critère décisif est la vidéo : l'option A n'est pas viable pour 200 Mo, et l'option C imposerait de maintenir deux mécanismes pour un gain marginal. Un seul chemin de code, appliqué uniformément, est plus sûr que deux chemins spécialisés — surtout sur un projet à deux développeurs.

Le coût — le déplacement de la validation — est réel et c'est le point à traiter sérieusement.

## Conséquences

### Positives

- Le serveur ne transfère aucun fichier. Il signe, enregistre, orchestre.
- Aucune limite PHP à relever, donc pas de surface de déni de service supplémentaire.
- Un seul transfert : l'upload est environ deux fois plus rapide pour le mécanicien.
- Le serveur reste réactif pendant qu'une vidéo monte.
- Les fichiers ne sont jamais servis depuis notre domaine : une charge active dans un fichier ne s'exécuterait pas dans notre origine.

### Négatives

- **La validation se déplace, et devient plus subtile.** C'est le vrai coût de cette décision, détaillé ci-dessous.
- **Deux allers-retours en plus** par fichier (signature, confirmation).
- **Fichiers orphelins** : un upload interrompu avant confirmation laisse un fichier qui consomme du quota. Une purge planifiée est **obligatoire**, pas optionnelle.
- Le front porte davantage de logique : signer, envoyer, confirmer, gérer l'échec de chaque étape séparément.

### Comment la validation est traitée

Elle se fait en deux temps, avant et après :

**À la signature** — authentification, appartenance de l'annonce, type MIME dans la liste blanche, taille annoncée sous le maximum, quantité déjà présente (2 vidéos maximum).

Point essentiel : **la signature est contraignante**. Elle encode le dossier, la taille maximale et les formats acceptés, et Cloudinary comme R2 **refusent eux-mêmes** un fichier hors contrainte. La restriction est appliquée par le fournisseur, pas seulement annoncée par nous. Un client malveillant ne peut pas envoyer un fichier de 2 Go avec une signature délivrée pour 15 Mo.

**À la confirmation** — un `HEAD` vérifie que l'objet existe réellement, avec la taille et le type attendus. On ne croit pas ce que le client déclare.

Ce qu'on perd et qu'on assume : l'analyse antivirus du contenu, et la lecture du type MIME réel dans les octets du fichier (on se fie au type déclaré au fournisseur, qui le vérifie). Pour un backoffice à utilisateur unique authentifié, ce risque est acceptable.

### Ce que ça implique concrètement

- Trois endpoints au lieu d'un : signature, upload (chez le fournisseur), confirmation.
- `media.confirmed_at` : un média non confirmé n'est **jamais** renvoyé par l'API.
- `PurgeOrphanUploads` planifié toutes les heures, supprimant en base **et** chez le fournisseur au-delà de 24 h.
- Signatures valables 10 minutes (Cloudinary) et 15 minutes (R2 présigné).
- Limitation de débit sur l'endpoint de signature : 30/min, pour éviter l'épuisement de quota.
- La CSP du backoffice autorise `connect-src` vers Cloudinary et R2 — le navigateur y envoie les fichiers directement.

Détail dans [04 — Pipeline médias](../04-pipeline-medias.md).

## Quand reconsidérer

Si une analyse antivirus des fichiers devenait exigée, il faudrait soit repasser par le serveur pour les photos (option C), soit brancher une analyse asynchrone après confirmation, déclenchée par un événement du fournisseur.
