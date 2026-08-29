# Écarts au cahier des charges

Le CDC v1.0 (août 2026) est un document contractuel. Les points ci-dessous s'en écartent.
**Chacun doit être confirmé par écrit par le client avant le démarrage du jalon concerné.**

Ce document sert de base à cette validation : il peut être envoyé tel quel.

---

## E1 — Le frontend n'est plus en Blade mais en React / Next.js

**Ce que dit le CDC** — §5.1 : « Frontend public : Blade + Tailwind CSS ».

**Ce qu'on fait** — Le backend Laravel n'expose qu'une API JSON. Deux applications Next.js (React) la consomment : la vitrine publique et le backoffice.

**Pourquoi** — Décision du responsable architecture. Avec une équipe de deux personnes spécialisées (un backend, un frontend), la frontière devient un contrat HTTP explicite et versionné plutôt qu'une convention informelle sur des vues partagées. Les deux devs travaillent en parallèle sans se bloquer, chacun sur sa base de code.

**Impact pour le client** — Aucun sur les fonctionnalités livrées. Le site reste rendu côté serveur, donc référençable (voir E2).

**Impact sur le budget** — Voir E3.

**Statut** — ⬜ à valider par le client

---

## E2 — Le rendu serveur est assuré par Next.js, pas par Blade

**Ce que dit le CDC** — §4, Référencement : « Structure HTML favorisant le SEO (pages statiques/rendues côté serveur) ».

**Ce qu'on fait** — Next.js en SSR + ISR : chaque page voiture et le catalogue sont rendus en HTML côté serveur, mis en cache, et régénérés dès que le mécanicien modifie une annonce.

**Pourquoi** — L'exigence du CDC est satisfaite, par un autre moyen. Un React purement client aurait cassé cette exigence ; c'est précisément pour cela qu'on ne le fait pas.

**Impact** — Neutre à positif : l'ISR sert de l'HTML pré-rendu depuis un CDN mondial, ce qui aide l'objectif « moins de 3 secondes sur mobile » du §4.

**Statut** — ⬜ à valider par le client

---

## E3 — L'hébergement change

**Ce que dit le CDC** — §5.1 : « Hébergement mutualisé économique (type Hostinger) pour le démarrage ».

**Ce qu'on fait** — L'API Laravel est hébergée sur le serveur d'Excellence Team. Les deux applications Next.js sont déployées sur Vercel, sous le domaine du client.

**Pourquoi** — Un hébergement mutualisé ne fait pas tourner un processus Node en continu, ce qui est nécessaire au rendu serveur. Le serveur Excellence Team est déjà disponible et Vercel dispose d'une offre gratuite suffisante pour ce volume.

**Impact sur le budget** — Neutre voire favorable : pas d'abonnement mutualisé à souscrire. Le domaine doit en revanche être **branché sur Vercel dès le début du projet**, et non en fin de parcours : l'authentification et la configuration des domaines en dépendent.

**Statut** — ⬜ à valider par le client

---

## E4 — La publication Facebook est reportée en V2

**Ce que dit le CDC** — Contradiction interne : §2.3 la déclare hors périmètre V1, tandis que §3.5, §5.1 et le Lot 6 du §7 la décrivent comme livrable.

**Ce qu'on fait** — On retient §2.3. Aucune publication automatique Facebook en V1. En V1, le mécanicien diffuse ses annonces manuellement (copier-coller depuis la fiche publique).

**Pourquoi** — Deux raisons. D'abord, le CDC lui-même l'exclut. Ensuite, cette fonctionnalité dépend d'une validation de Meta (permission `pages_manage_posts`) dont le délai est de plusieurs semaines et dont l'issue n'est pas garantie : elle constituait le seul point de blocage externe du projet.

**Impact pour le client** — Une fonctionnalité attendue en moins. Elle sera livrée en V2 avec la table de traçabilité des publications prévue au §3.5.

**Ce qu'il faut faire malgré tout, dès maintenant** — Si le client souhaite cette fonctionnalité en V2, **la création de l'application Meta for Developers et la demande de permission doivent être lancées dès maintenant**, en parallèle du V1, puisque le délai est indépendant du développement. C'est une démarche administrative, pas technique.

**Statut** — ⬜ à valider par le client — **et à décider : lance-t-on la démarche Meta dès maintenant ?**

---

## E5 — L'habillage vidéo est reporté en V2

**Ce que dit le CDC** — §3.2 : « ajout automatique d'un habillage publicitaire aux vidéos (logo, intro, texte) », Lot 7.

**Ce qu'on fait** — En V1, les vidéos sont mises en ligne telles quelles et lues dans un lecteur aux couleurs du garage. Aucun réencodage.

**Pourquoi** — Superposer un logo et concaténer une intro impose de réencoder la vidéo. Cela exige soit un service payant (les transformations vidéo Cloudinary consomment des crédits bien plus vite que les photos, et le plan gratuit serait dépassé au bout de quelques dizaines de vidéos), soit un serveur de traitement dédié avec ffmpeg — écarté par la décision de ne rien faire tourner de lourd sur le serveur.

**Impact pour le client** — Les vidéos ne portent pas la marque du garage si elles sont téléchargées et repartagées ailleurs.

**Option intermédiaire disponible sans surcoût** — Habiller le lecteur vidéo côté site (logo en superposition, carton d'intro affiché avant la lecture). L'effet visuel est présent sur le site, mais disparaît si la vidéo est récupérée. À arbitrer avec le client s'il veut quelque chose en V1.

**Statut** — ⬜ à valider par le client

---

## E6 — La suppression de fond des photos est limitée par un quota

**Ce que dit le CDC** — §3.2 : « amélioration automatique des photos (retouche, recadrage, **suppression de fond**) », et §4 : « priorité aux outils et API gratuits ou à faible coût ».

**Ce qu'on fait** — Amélioration automatique et recadrage intelligent sur **toutes** les photos, sans limite (transformations Cloudinary, gratuites). Suppression de fond **à la demande**, via remove.bg, dans la limite d'environ **50 photos par mois**, avec un compteur visible dans le backoffice.

**Pourquoi** — Ces deux exigences du CDC sont contradictoires : aucune API de suppression de fond n'est gratuite et illimitée. L'offre gratuite de remove.bg est plafonnée ; l'équivalent Cloudinary est un add-on payant.

**Impact pour le client** — Le mécanicien réserve la suppression de fond aux photos principales de ses plus belles annonces. Le compteur l'informe de ce qui lui reste dans le mois.

**Si ce n'est pas suffisant** — Activer l'add-on Cloudinary lève la limite, avec un coût mensuel récurrent à accepter. Décision différée R04 du [registre](registre-decisions.md).

**Statut** — ⬜ à valider par le client

---

## E7 — L'éditeur du blog est volontairement minimal

**Ce que dit le CDC** — §3.4 : « rédiger et publier des articles de blog décrivant des interventions réalisées ».

**Ce qu'on fait** — Titre, chapeau, texte en paragraphes simples, une image de couverture, rattachement optionnel à un service. Pas de gras, de titres intermédiaires ni d'images dans le corps du texte.

**Pourquoi** — Décision du responsable architecture. Le besoin exprimé est de courts retours d'intervention, pas des articles longs. Un éditeur riche représente plusieurs jours de travail frontend et une politique d'assainissement HTML à écrire sérieusement.

**Impact pour le client** — Rédaction plus rapide, mais mise en forme impossible. Si le besoin évolue, un éditeur riche peut être ajouté en V2 sans casser les articles existants.

**Statut** — ⬜ à valider par le client

---

## Récapitulatif pour validation client

| Écart | Sujet | Impact fonctionnel | Impact budget | Validé |
|---|---|---|---|---|
| E1 | React au lieu de Blade | Aucun | Aucun | ⬜ |
| E2 | SSR par Next.js | Aucun (exigence SEO tenue) | Aucun | ⬜ |
| E3 | Hébergement Vercel + serveur | Aucun | Favorable | ⬜ |
| E4 | Facebook en V2 | **Fonctionnalité en moins** | Aucun | ⬜ |
| E5 | Habillage vidéo en V2 | **Fonctionnalité en moins** | Aucun (sinon payant) | ⬜ |
| E6 | Suppression de fond plafonnée | **Fonctionnalité limitée** | Aucun (sinon payant) | ⬜ |
| E7 | Blog en texte simple | Mise en forme réduite | Favorable | ⬜ |

Les livrables du CDC §6 restent tous dus : code source complet, base de données documentée, guide d'utilisation du backoffice. Le quatrième livrable, « compte Facebook Developer configuré », est reporté avec le Lot 6 (voir E4).
