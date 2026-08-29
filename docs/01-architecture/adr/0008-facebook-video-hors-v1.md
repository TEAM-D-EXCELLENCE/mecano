# 0008 — Facebook (Lot 6) et habillage vidéo (Lot 7) hors V1

- **Statut** : Accepté
- **Date** : 2026-08-25
- **Décideur** : responsable architecture
- **Décisions liées** : D08
- **Écarts au CDC** : [E4](../../00-contexte/ecarts-cahier-des-charges.md#e4--la-publication-facebook-est-reportée-en-v2), [E5](../../00-contexte/ecarts-cahier-des-charges.md#e5--lhabillage-vidéo-est-reporté-en-v2)

## Contexte

Deux fonctionnalités du CDC sont écartées du V1. Elles sont traitées dans un même ADR parce qu'elles partagent une caractéristique : **elles dépendent d'un facteur que l'équipe ne contrôle pas** — un délai administratif dans un cas, un budget dans l'autre.

### Le cas Facebook : le CDC se contredit

- **§2.3**, « Ce qui est exclu (hors périmètre V1) », liste explicitement « Publication automatique sur une Page Facebook ».
- **§3.5** décrit la fonctionnalité comme un besoin fonctionnel.
- **§5.1** liste l'API Graph dans la stack retenue.
- **Lot 6** du §7 la planifie, avec la mention « démarche de validation Meta lancée en parallèle dès le Lot 1 ».

Le document se contredit. Il faut trancher.

Élément déterminant, indépendamment de la contradiction : la publication sur une Page exige la permission `pages_manage_posts`, soumise à revue Meta. Le CDC §5.2 le reconnaît lui-même — « le délai de validation pouvant prendre plusieurs jours à plusieurs semaines ». Ce délai est **hors du contrôle de l'équipe**, et l'issue n'est pas garantie.

### Le cas vidéo : conséquence d'une autre décision

L'habillage publicitaire (§3.2, Lot 7) impose de **réencoder** la vidéo pour y incruster un logo et concaténer une intro. L'[ADR 0006](0006-medias-cloudinary-r2.md) a écarté tout traitement lourd sur le serveur, et R2 ne transforme rien. Restent deux voies, toutes deux payantes : les transformations vidéo Cloudinary (crédits consommés rapidement) ou un worker ffmpeg dédié (machine à louer).

## Options examinées

### Pour Facebook

**A — Le maintenir en V1.** Apporte la fonctionnalité complète. Coûte : le projet dépend d'une validation externe de plusieurs semaines, à l'issue incertaine. Si Meta refuse ou traîne, un lot entier reste bloqué et le V1 ne peut pas être déclaré terminé.

**B — Le reporter en V2.** Apporte un V1 sans aucune dépendance externe bloquante. Coûte une fonctionnalité attendue par le client, remplacée par une diffusion manuelle.

**C — Coder l'abstraction, brancher plus tard.** Une interface `Publisher` et la table de traçabilité, avec une implémentation manuelle. Apporte une V2 sans refonte. Coûte du code et une table qui ne servent à rien pendant tout le V1 — de la dette déguisée en anticipation.

### Pour la vidéo

**A — Transformations vidéo Cloudinary.** Faisable immédiatement, mais coût mensuel récurrent dès quelques dizaines de vidéos.

**B — Worker ffmpeg dédié.** Contrôle total, mais une machine à louer et à administrer, contraire à la décision de ne rien faire tourner de lourd.

**C — Habillage côté lecteur.** Logo en superposition CSS et carton d'intro dans le lecteur du site. Coût nul, livrable immédiatement. Mais l'habillage disparaît si la vidéo est téléchargée et repartagée — ce qui est justement l'usage visé.

**D — Reporter.** Vidéos servies telles quelles en V1.

## Décision

**Facebook : option B, report en V2.** §2.3 fait foi.

Le critère décisif n'est pas la contradiction du document, c'est le risque de planning. Le Lot 6 était le **seul point de blocage externe** du projet : en le sortant, le V1 devient entièrement sous le contrôle de l'équipe. Aucune tranche livrable ne dépend plus d'un tiers.

**Vidéo : option D, report en V2**, l'option C restant disponible sans surcoût si le client la souhaite.

Le critère est le coût récurrent : le CDC §4 impose « priorité aux outils et API gratuits ou à faible coût », et l'habillage vidéo est la seule fonctionnalité du projet qui exige un abonnement. Elle ne peut pas être engagée sans l'accord explicite du client.

## Conséquences

### Positives

- **Le V1 n'a plus aucune dépendance externe bloquante.** C'est le bénéfice principal : plus rien ne peut retarder une livraison pour une raison extérieure à l'équipe.
- Le risque le plus élevé du projet est éliminé, pas seulement atténué.
- Aucun coût récurrent engagé sans décision du client.
- L'effort libéré va sur ce qui porte réellement la valeur : le catalogue, les photos, le SEO.

### Négatives

- **Deux fonctionnalités attendues en moins.** Le client a lu §3.2 et §3.5, il les attend. C'est un écart contractuel à assumer par écrit.
- La diffusion sur les réseaux reste **manuelle** en V1 : le mécanicien copie le lien de la fiche. Le travail que la plateforme devait automatiser reste à sa charge.
- Les vidéos ne portent pas la marque du garage si elles sont récupérées et repartagées.
- Le livrable §6 « compte Facebook Developer configuré » est reporté avec le lot.

### Mesures d'atténuation

- **L'image Open Graph est soignée.** Puisque la diffusion est manuelle, c'est le seul canal social du V1 : quand le mécanicien colle le lien dans WhatsApp ou Facebook, l'aperçu doit être impeccable — photo principale, prix incrusté, nom du garage. Traité dans [07 — Performance et SEO](../07-performance-seo-pwa.md).
- **Recommandation forte au client** : si Facebook est voulu en V2, lancer la démarche Meta **maintenant**. Le délai est administratif et indépendant du développement ; l'attendre plus tard coûte exactement le même temps, mais décalé.
- L'option « habillage côté lecteur » reste proposable au client à coût nul.

### Ce qu'on ne fait pas

**Aucune table `social_publications`, aucune interface `Publisher` en V1.** L'option C a été écartée délibérément : une abstraction sans second cas d'usage est presque toujours mal dimensionnée, et une table vide est de la dette. Quand le Lot 6 démarrera, on écrira le code avec le besoin réel sous les yeux.

## Quand reconsidérer

- **Facebook** : dès que la permission `pages_manage_posts` est accordée par Meta. Le développement lui-même est modeste — c'est l'attente qui coûte.
- **Vidéo** : dès que le client accepte un coût récurrent, ou si le volume de vidéos reste si faible que les crédits Cloudinary suffisent. Décision différée R05.
