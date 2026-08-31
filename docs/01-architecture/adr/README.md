# Journal des décisions d'architecture (ADR)

Un ADR consigne une décision structurante : son contexte, les options examinées, le choix retenu, et ce qu'il coûte.

**Pourquoi on en écrit.** Dans six mois, personne ne se souviendra pourquoi le backoffice a un BFF, ni pourquoi les vidéos sont sur R2 et les photos sur Cloudinary. Sans ADR, quelqu'un « simplifiera » un choix dont il ignore la raison, et réintroduira le problème qu'il évitait.

## Règles

1. Un ADR par décision, numéroté, **jamais supprimé**.
2. Un ADR n'est pas modifié après acceptation. Pour revenir sur une décision, on écrit un nouvel ADR qui **remplace** l'ancien, et on marque l'ancien `Remplacé par 00XX`.
3. Une décision structurante est codée **après** son ADR, pas avant.
4. Toute PR qui contredit un ADR accepté est refusée — ou accompagnée de l'ADR qui le remplace.
5. `openapi.yaml` et ce dossier appartiennent au responsable architecture (voir [CODEOWNERS](../../../.github/CODEOWNERS)).

## Index

| # | Décision | Statut | Date |
|---|---|---|---|
| [0001](0001-api-json-decouplee.md) | Backend = API JSON uniquement, découplée du frontend | Accepté | 2026-08-25 |
| [0002](0002-nextjs-ssr-isr.md) | Next.js en SSR + ISR pour l'indexation du catalogue | Accepté | 2026-08-25 |
| [0003](0003-monorepo-trois-apps.md) | Monorepo de trois applications, sans paquet partagé | Accepté | 2026-08-25 |
| [0004](0004-auth-bearer-bff.md) | Jeton Bearer détenu par un BFF Next en cookie httpOnly | Accepté | 2026-08-25 |
| [0005](0005-openapi-source-de-verite.md) | `openapi.yaml` écrit à la main comme source de vérité | Accepté | 2026-08-25 |
| [0006](0006-medias-cloudinary-r2.md) | Photos sur Cloudinary, vidéos sur Cloudflare R2 | Partiellement remplacé par [0012](0012-videos-sur-cloudinary.md) | 2026-08-25 |
| [0007](0007-upload-direct-signe.md) | Upload direct signé depuis le navigateur | Accepté | 2026-08-25 |
| [0008](0008-facebook-video-hors-v1.md) | Facebook (Lot 6) et habillage vidéo (Lot 7) hors V1 | Accepté | 2026-08-25 |
| [0009](0009-revalidation-isr-webhook.md) | Revalidation ISR par webhook signé | Accepté | 2026-08-25 |
| [0010](0010-postgresql-supabase.md) | PostgreSQL managé (Supabase) plutôt que MySQL auto-hébergé | Accepté | 2026-08-31 |
| [0011](0011-api-conteneurisee.md) | L'API est livrée sous forme d'image Docker | Accepté | 2026-08-31 |
| [0012](0012-videos-sur-cloudinary.md) | Les vidéos passent sur Cloudinary | Accepté | 2026-08-31 |

## Gabarit

```markdown
# 00XX — Titre à l'infinitif ou au constat

- **Statut** : Proposé | Accepté | Remplacé par 00YY
- **Date** : AAAA-MM-JJ
- **Décideur** : responsable architecture
- **Décisions liées** : Dnn du registre

## Contexte
Quelle contrainte force à décider. Ce qui est en jeu si on se trompe.

## Options examinées
### Option A — …
Ce que c'est. Ce que ça apporte. Ce que ça coûte.

## Décision
Ce qu'on retient, et le critère qui a tranché.

## Conséquences
### Positives
### Négatives
Ce qu'on accepte de perdre. Un ADR sans conséquence négative est un ADR mal écrit.

## Quand reconsidérer
Le signal concret qui justifierait de rouvrir la décision.
```
