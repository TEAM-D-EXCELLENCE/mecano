# 0006 — Photos sur Cloudinary, vidéos sur Cloudflare R2

- **Statut** : Accepté
- **Date** : 2026-08-25
- **Décideur** : responsable architecture
- **Décision liée** : D06
- **Écarts au CDC** : [E5](../../00-contexte/ecarts-cahier-des-charges.md#e5--lhabillage-vidéo-est-reporté-en-v2), [E6](../../00-contexte/ecarts-cahier-des-charges.md#e6--la-suppression-de-fond-des-photos-est-limitée-par-un-quota)

## Contexte

Les médias portent la valeur commerciale du projet : de belles photos vendent des voitures. Ils portent aussi tout le coût d'infrastructure. Le CDC §5.1 prévoit « Cloudinary (plan gratuit) ou stockage local », et §4 impose « priorité aux outils et API gratuits ou à faible coût ».

Volumétrie attendue : environ 8 photos et 2 vidéos par annonce. Les vidéos pèsent 50 à 200 Mo chacune.

L'équipe dispose de son propre serveur, ce qui ouvre une option que le CDC n'envisageait pas : traiter les médias localement avec ffmpeg et Intervention Image.

## Options examinées

### Option A — Tout sur le serveur (ffmpeg + Intervention Image)

**Apporte** : coût récurrent nul, aucun quota, et surtout **l'habillage vidéo du Lot 7 devient faisable** — c'est la seule option qui le permette sans surcoût.

**Coûte** : consommation CPU du serveur (le transcodage vidéo est lourd), sauvegardes des fichiers à gérer, aucun CDN devant les images. Sur une connexion mobile africaine, servir des images depuis un serveur unique sans CDN dégrade fortement le temps de chargement — ce qui met en péril l'objectif « moins de 3 s » du CDC §4.

### Option B — Tout sur Cloudinary

**Apporte** : le plus rapide à livrer, transformations à la volée, CDN inclus, Lot 7 réalisable via les transformations vidéo.

**Coûte** : les transformations vidéo consomment des crédits **bien plus vite** que les photos. Le plan gratuit serait dépassé au bout de quelques dizaines de vidéos, et l'égress vidéo est facturé. Une vidéo de 80 Mo vue 200 fois représente 16 Go de bande passante — à elle seule, elle épuise le plan gratuit.

### Option C — Photos sur Cloudinary, vidéos sur R2

Les photos profitent des transformations et du CDN Cloudinary. Les vidéos sont stockées sur R2 et servies derrière le CDN Cloudflare.

**Apporte** : les photos — le gros du trafic et de la valeur — ont le meilleur outil pour elles. **L'égress R2 est gratuit**, ce qui rend la vidéo économiquement neutre quel que soit le nombre de vues. Coût récurrent quasi nul.

**Coûte** : deux fournisseurs à configurer. Aucune transformation vidéo possible, donc **le Lot 7 devient infaisable sans surcoût**.

### Option D — R2 pour tout le stockage, Cloudinary en moteur de transformation

Les originaux et les dérivés vivent sur R2, Cloudinary ne sert qu'à générer les dérivés qu'on récupère et stocke.

**Apporte** : jamais prisonnier de Cloudinary, sauvegardes maîtrisées.

**Coûte** : beaucoup plus de code de pipeline, et on perd les transformations à la volée — qui sont précisément ce qui rend Cloudinary utile.

## Décision

**Option C.**

Le critère décisif est l'égress vidéo. Les vidéos sont peu nombreuses mais lourdes, et leur coût est proportionnel aux **vues**, pas au stockage — c'est-à-dire proportionnel au succès du site. Un modèle où réussir coûte cher est à éviter. R2 rend ce coût nul.

Pour les photos, Cloudinary apporte trois choses irremplaçables sans surcoût : `f_auto` (WebP/AVIF selon le navigateur), `q_auto` (compression adaptative) et le CDN. Ces trois éléments font l'essentiel du budget de performance mobile du CDC §4.

L'option A a été écartée principalement pour l'absence de CDN, dont l'impact sur le temps de chargement mobile est plus grave que le bénéfice du Lot 7. **Conséquence assumée : le Lot 7 est reporté en V2** ([ADR 0008](0008-facebook-video-hors-v1.md), écart E5).

## Conséquences

### Positives

- Coût récurrent quasi nul, conforme au CDC §4.
- Le coût de la vidéo ne croît pas avec le succès du site.
- Les photos bénéficient du CDN et des transformations à la volée : c'est là que se joue la performance mobile.
- Rien de lourd ne tourne sur le serveur : Laravel signe, enregistre, orchestre, mais ne transcode rien.
- `f_auto` et `q_auto` seuls réduisent le poids des images de 30 à 50 %.

### Négatives

- **Le Lot 7 (habillage vidéo) devient infaisable en V1.** C'est la conséquence la plus lourde, et un écart à faire valider par le client (E5).
- **La suppression de fond n'est pas couverte** : `e_background_removal` est un add-on payant chez Cloudinary. D'où le recours à remove.bg et son quota (E6).
- Deux fournisseurs, deux jeux de secrets, deux tableaux de bord à surveiller.
- Un domaine personnalisé (`media.garage.com`) est indispensable devant R2, sans quoi on expose une URL de bucket brute.
- Dépendance à Cloudinary pour les photos : un déménagement imposerait de régénérer tous les dérivés.

### Ce que ça implique concrètement

- Jeu **fermé** de dérivés photo (`thumb`, `card`, `detail`, `og`). Pas de largeurs arbitraires, sinon les crédits partent en variantes inutiles.
- Transformations nommées côté Cloudinary, pas en clair dans les URL.
- R2 via le pilote `s3` de Laravel avec point de terminaison personnalisé.
- Vidéos servies telles quelles, `preload="none"`, avec vignette d'aperçu.
- Dossiers séparés par environnement (`dev`, `preview`, `prod`) : un test local ne doit pas pouvoir supprimer un fichier de production.

Détail dans [04 — Pipeline médias](../04-pipeline-medias.md) et [05 — Intégrations](../05-integrations-externes.md).

## Quand reconsidérer

Si le client accepte un coût mensuel récurrent pour l'habillage vidéo, deux voies s'ouvrent : activer les transformations vidéo Cloudinary, ou monter un worker ffmpeg dédié. C'est la décision différée R05.
