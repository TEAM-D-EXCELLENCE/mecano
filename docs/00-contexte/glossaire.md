# Glossaire

Vocabulaire commun au client, au responsable architecture et aux deux devs.
Quand un terme est dans ce glossaire, on l'utilise tel quel — dans le code, dans les PR, dans les échanges avec le client.

## Métier

| Terme | Définition | Nom technique |
|---|---|---|
| **Annonce** | Une offre de vente portant sur un véhicule précis. Une annonce = un véhicule physique. | `Car` / table `cars` |
| **Statut d'annonce** | `brouillon` (invisible du public), `disponible`, `réservé`, `vendu`. | `status` |
| **Vendu** | Le véhicule est parti, mais **la page reste en ligne** avec un badge. Elle sort des filtres par défaut du catalogue. | `status = sold` |
| **Marque** | Constructeur, issu d'un référentiel fermé (`brands`). Le mécanicien ne saisit pas de texte libre. | `Brand` |
| **Modèle** | Texte libre (« Corolla », « Hilux double cabine »). | `cars.model` |
| **Service** | Une prestation de mécanique présentée dans la vitrine. Activable / désactivable, jamais supprimée. | `Service` |
| **Article** | Retour d'expérience publié au blog, éventuellement rattaché à un service. | `Post` |
| **Vitrine** | Le site public, `garage.com`. | `apps/web` |
| **Backoffice** | L'espace de gestion du mécanicien, `admin.garage.com`. | `apps/admin` |

## Médias

| Terme | Définition |
|---|---|
| **Média** | Un fichier rattaché à une annonce : photo ou vidéo. Table `media`. |
| **Original** | Le fichier tel que le mécanicien l'a envoyé. **Jamais écrasé, jamais modifié en place.** |
| **Dérivé** | Une version transformée d'un original (recadrée, améliorée, fond supprimé, convertie en WebP). |
| **Amélioration** | Une demande de dérivé, avec son état et son résultat. Table `media_enhancements`. C'est ce qui permet l'avant/après du CDC §3.2. |
| **Photo principale** | La photo affichée dans le catalogue et en image Open Graph. Exactement une par annonce. |
| **Habillage vidéo** | Réencodage d'une vidéo avec intro, logo et texte publicitaire. **Hors V1** (Lot 7). Ne pas confondre avec le simple lecteur habillé côté front. |
| **Upload signé** | Le navigateur envoie le fichier directement à Cloudinary, avec une signature délivrée par l'API. Le fichier ne traverse jamais notre serveur. |
| **Confirmation** | L'appel que le front fait à l'API après un upload signé réussi, pour enregistrer le média en base. Un upload non confirmé est un fichier orphelin. |

## Technique

| Terme | Définition |
|---|---|
| **BFF** | *Backend For Frontend*. Les route handlers Next qui détiennent le jeton en cookie httpOnly et le retransmettent en `Bearer` à Laravel. Le navigateur ne voit jamais le jeton. |
| **ISR** | *Incremental Static Regeneration*. Next sert une page en cache et la régénère sur invalidation. Voir [07 — Performance](../01-architecture/07-performance-seo-pwa.md). |
| **Revalidation** | L'invalidation d'un cache ISR, déclenchée par un webhook signé que Laravel envoie à Next quand une donnée publique change. |
| **Contrat** | `openapi.yaml`. Seule définition de ce que l'API promet. |
| **Jalon** | Une tranche livrable et déployable en production : M0 à M4. Voir [MVP & jalons](../04-planning/mvp-et-jalons.md). |
| **Lot** | Le découpage du CDC §7 (Lot 1 à Lot 7). Conservé pour dialoguer avec le client ; en interne on parle en jalons. |
| **Quota** | Le compteur mensuel d'appels à une API externe limitée (remove.bg). Visible dans le backoffice. |

## Termes à ne pas employer

| À éviter | Pourquoi | Dire plutôt |
|---|---|---|
| « voiture » dans le code | Ambigu entre l'objet physique et l'offre | `Car` désigne l'annonce |
| « supprimer une annonce » | On archive (`deleted_at`), on ne supprime jamais un historique de vente | « archiver » |
| « image » | Trop vague | « photo » (fichier) ou « dérivé » (version transformée) |
| « IA » face au client | Le CDC promet de l'IA mais on livre surtout des transformations déterministes | « amélioration automatique » |
