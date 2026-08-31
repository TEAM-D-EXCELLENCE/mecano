# 0012 — Les vidéos passent sur Cloudinary

- **Statut** : Accepté
- **Date** : 2026-08-31
- **Décideur** : responsable architecture
- **Remplace** : la partie vidéo de [ADR 0006](0006-medias-cloudinary-r2.md)

## Contexte

[ADR 0006](0006-medias-cloudinary-r2.md) plaçait les vidéos sur Cloudflare R2, pour une raison solide : l'égress vidéo y est gratuit, donc le coût ne croît pas avec le succès du site.

L'implémentation ne l'a jamais suivi. `R2VideoStorage` s'appuyait sur un disque `r2` absent de `config/filesystems.php` : chaque appel levait une exception, tombait dans un `catch (\Throwable)` et rendait une URL **sans signature** qu'aucun hébergeur n'aurait acceptée. `exists()` renvoyait toujours `null`, donc aucune confirmation de vidéo ne pouvait aboutir ; `delete()` ne supprimait rien. L'échec était entièrement silencieux, et aucun test ne le voyait puisque l'environnement de test lie l'implémentation factice.

Autrement dit : la V1 n'a jamais eu de stockage vidéo fonctionnel, et personne ne s'en est aperçu.

## Options examinées

### Option A — Réparer R2

Déclarer le disque, vérifier les identifiants, brancher un domaine personnalisé devant le bucket.

**Apporte** : l'égress gratuit voulu par l'ADR 0006.

**Coûte** : un deuxième fournisseur à configurer, surveiller et facturer, pour **deux vidéos par annonce** — une intérieure, une extérieure, plafonnées à 200 Mo. Un domaine personnalisé est indispensable, sans quoi on expose une URL de bucket brute.

### Option B — Vidéos sur Cloudinary

**Apporte** : un seul fournisseur, un seul jeu de secrets, un seul tableau de bord. Le mécanisme d'envoi signé est déjà écrit et éprouvé pour les photos : il suffit de viser le point d'entrée vidéo. Cloudinary transcode et sert en adaptatif.

**Coûte** : l'égress vidéo est décompté du quota Cloudinary. Le plan gratuit peut être atteint si les vidéos sont très regardées.

## Décision

**Option B.**

Le raisonnement de l'ADR 0006 sur l'égress reste juste dans l'absolu, mais il supposait un volume que la V1 n'a pas. Deux vidéos par annonce, sur un catalogue de quelques dizaines de véhicules, ne justifient pas un second fournisseur — surtout quand celui-ci n'a jamais fonctionné et que son échec était invisible.

Le critère décisif est le nombre de choses qui peuvent casser sans qu'on le voie. Un fournisseur de moins, c'est un jeu de secrets, un domaine et un mode de défaillance en moins.

## Conséquences

### Positives

- Le stockage vidéo fonctionne, ce qui n'était pas le cas.
- Un seul fournisseur de médias : mêmes secrets, même signature, même chemin de code.
- La suppression d'une vidéo supprime réellement l'objet distant.

### Négatives

- L'égress vidéo est désormais facturé au quota Cloudinary. **À surveiller** : si les vidéos deviennent très regardées, l'ADR 0006 redevient pertinent et cette décision devra être reprise.
- Aucun domaine personnalisé devant les médias : les URL sont celles de Cloudinary.

### Ce que ça implique concrètement

- `CloudinaryVideoStorage` remplace `R2VideoStorage`, supprimé.
- La valeur `r2` de l'énumération `MediaProvider` est **conservée** : des enregistrements existants peuvent la porter, et la retirer casserait leur lecture. Aucun média nouveau ne l'utilise.
- Les variables `R2_*` disparaissent des fichiers d'environnement.
