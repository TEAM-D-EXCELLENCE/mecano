# Matrice de traçabilité des exigences

Chaque exigence du cahier des charges est reliée à un jalon et aux tâches qui la réalisent.
Aucune exigence ne doit rester sans jalon : si elle n'en a pas, elle est soit reportée, soit un écart à documenter.

Légende des statuts : **V1** = livré dans le périmètre V1 · **V2** = reporté · **Écart** = livré différemment, voir [écarts](ecarts-cahier-des-charges.md) · **CDC** = exclu par le CDC lui-même.

## §3.1 — Gestion des annonces (backoffice)

| Exigence CDC | Jalon | Tâches | Statut |
|---|---|---|---|
| Authentification sécurisée du mécanicien | M0 | CTR-01, BE-04, BE-05, FE-04, FE-05 | V1 |
| Créer une annonce (marque, modèle, année, km, prix, carburant, transmission, couleur, état, description) | M1 | BE-07, BE-10, BE-15, FE-18 | V1 |
| Modifier ou supprimer une annonce | M1 | BE-15, FE-18 | V1 (suppression = archivage) |
| Changer le statut (disponible / réservé / vendu) | M1 | BE-16, FE-19 | V1 |
| Uploader plusieurs photos, désigner une photo principale | M1 | BE-17, BE-18, BE-19, BE-20, BE-21, FE-20, FE-21, FE-22 | V1 |
| Uploader deux vidéos par annonce (intérieur, extérieur) | M3 | BE-32, FE-30 | V1 |

## §3.2 — Amélioration IA des médias

| Exigence CDC | Jalon | Tâches | Statut |
|---|---|---|---|
| Amélioration automatique : retouche | M3 | BE-33, FE-31 | V1 |
| Amélioration automatique : recadrage | M3 | BE-33, FE-31 | V1 |
| Amélioration automatique : suppression de fond | M3 | BE-34, FE-32 | **Écart E6** — plafonné à ~50/mois |
| Habillage publicitaire des vidéos (logo, intro, texte) | V2 | V2-05 | **Écart E5** — Lot 7 reporté |
| Visualiser original et version améliorée avant publication | M3 | BE-35, BE-36, FE-31 | V1 |

## §3.3 — Vitrine des services

| Exigence CDC | Jalon | Tâches | Statut |
|---|---|---|---|
| Page publique listant les services | M2 | BE-27, FE-23 | V1 |
| Ajouter, modifier, désactiver un service depuis le backoffice | M2 | BE-27, FE-27 | V1 |

## §3.4 — Blog

| Exigence CDC | Jalon | Tâches | Statut |
|---|---|---|---|
| Rédiger et publier des articles | M2 | BE-28, FE-28 | **Écart E7** — texte simple |
| Associer un article à un service | M2 | BE-28, FE-28 | V1 |
| Consulter la liste des articles publiés | M2 | BE-28, FE-24 | V1 |
| Lire un article en détail | M2 | BE-28, FE-24 | V1 |

## §3.5 — Publication automatique Facebook

| Exigence CDC | Jalon | Tâches | Statut |
|---|---|---|---|
| Publier une annonce sur la Page Facebook depuis le backoffice | V2 | V2-01, V2-03, V2-04 | **Écart E4** — Lot 6 reporté |
| Conserver une trace de la publication (date, statut) | V2 | V2-02 | V2, table `social_publications` |

## §3.6 — Contact WhatsApp

| Exigence CDC | Jalon | Tâches | Statut |
|---|---|---|---|
| Bouton WhatsApp pré-rempli avec les informations du véhicule sur chaque fiche | M1 | BE-13, FE-11, FE-15 | V1 |

## §3.7 — Site public

| Exigence CDC | Jalon | Tâches | Statut |
|---|---|---|---|
| Liste des voitures disponibles avec filtres (marque, prix, année) | M1 | BE-11, BE-12, BE-13, FE-08 | V1 |
| Fiche détaillée (photos, vidéos, caractéristiques) | M1 (photos) / M3 (vidéos) | BE-13, FE-09, FE-10, FE-29 | V1 |

## §4 — Besoins non fonctionnels

| Exigence CDC | Jalon | Comment | Statut |
|---|---|---|---|
| Chargement < 3 s sur mobile | M1, vérifié à chaque jalon | ISR + CDN Vercel, images Cloudinary en WebP/AVIF, budget de performance en CI. Voir [07](../01-architecture/07-performance-seo-pwa.md) | V1 |
| Backoffice protégé, mots de passe hachés | M0 | Sanctum, bcrypt, BFF cookie httpOnly, `noindex`, limitation de débit sur `/login`. Voir [06](../01-architecture/06-securite.md) | V1 |
| Site responsive mobile / tablette / ordinateur | M1 | Tailwind, conception mobile d'abord | V1 |
| Hébergement stable, sauvegardes régulières de la base | M1 | API conteneurisée sur le serveur Excellence Team, base managée chez Supabase — sauvegardes assurées par le fournisseur ([ADR 0010](../01-architecture/adr/0010-postgresql-supabase.md)) | V1 |
| Coût minimal, API gratuites ou à faible coût | Transverse | Vercel gratuit, Cloudinary gratuit, R2 égress gratuit, remove.bg gratuit plafonné. Voir [05](../01-architecture/05-integrations-externes.md) | V1 |
| Structure HTML favorisant le SEO | M1, complété en M2 | SSR + ISR, `sitemap.xml`, JSON-LD `Vehicle`, balises Open Graph. Voir [07](../01-architecture/07-performance-seo-pwa.md) | **Écart E2** |

## §2.2 — Inclus au périmètre

| Élément | Jalon | Statut |
|---|---|---|
| Site public de présentation et de vente | M1 + M2 | V1 |
| Backoffice de gestion | M0 → M3 | V1 |
| Amélioration automatique des photos par IA | M3 | V1, **Écart E6** sur le fond |
| Habillage des vidéos (intro, logo) | V2 | **Écart E5** |
| Bouton de contact WhatsApp | M1 | V1 |
| PWA | M4 | V1 |

## §2.3 — Exclus par le CDC

| Élément | Traitement |
|---|---|
| Paiement en ligne | Exclu, CDC. Aucune tâche |
| Publication sur le statut WhatsApp personnel | Exclu, CDC — impossible techniquement (pas d'API Meta) |
| Publicité payante Facebook/Instagram | Exclu, CDC. Envisageable V2 |
| Publication automatique sur une Page Facebook | **Contradiction du CDC, tranchée en faveur de l'exclusion.** Voir E4 |
| Application mobile native | Exclu, CDC. La PWA (M4) en couvre une partie du besoin |

## §6 — Livrables

| Livrable CDC | Jalon | Où |
|---|---|---|
| Code source complet (backoffice + site public) | Continu | Ce dépôt |
| Base de données structurée et documentée | M0, tenu à jour | [Modèle de données](../01-architecture/03-modele-de-donnees.md) + migrations |
| Guide d'utilisation du backoffice pour le mécanicien | M2, complété à chaque jalon | Tâche DOC-01, livré hors dépôt (PDF) |
| Compte Facebook Developer configuré | V2 | **Écart E4** — démarche à lancer dès maintenant si le client la veut |

## §7 — Correspondance lots CDC ↔ jalons

| Lot CDC | Contenu | Jalon |
|---|---|---|
| Lot 1 | Structure, base de données, authentification backoffice | M0 |
| Lot 2 | CRUD annonces avec upload photos/vidéos | M1 (photos), M3 (vidéos) |
| Lot 3 | Site public : liste, fiche détail, services, blog | M1 (voitures), M2 (services, blog) |
| Lot 4 | Bouton WhatsApp | M1 |
| Lot 5 | Amélioration IA des photos | M3 |
| Lot 6 | Intégration Facebook Graph API | **V2** |
| Lot 7 | Habillage publicitaire des vidéos | **V2** |
