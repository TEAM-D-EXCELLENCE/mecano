# Definition of Done

Une tâche n'est pas terminée quand le code fonctionne. Elle est terminée quand elle satisfait **toute** la liste ci-dessous.

## Pour toute tâche

- [ ] Le comportement décrit dans la tâche du backlog est réalisé, en entier.
- [ ] La PR fait moins de 400 lignes modifiées (hors fichiers générés).
- [ ] Titre de PR et commits au format Conventional Commits, avec portée.
- [ ] Toute la CI est verte.
- [ ] Une approbation de l'autre développeur, plus celle des CODEOWNERS concernés.
- [ ] Aucun `TODO`, `FIXME` ni code commenté laissé derrière.
- [ ] Aucun secret, aucune donnée personnelle réelle dans le diff.
- [ ] Les documents impactés sont mis à jour **dans la même PR**.

## Tâche backend en plus

- [ ] La validation vit dans une `FormRequest`, pas dans le contrôleur.
- [ ] Le métier vit dans une action, pas dans le contrôleur.
- [ ] La réponse **correspond exactement** à `openapi.yaml`. Si elle diffère, le contrat a été modifié d'abord, dans sa propre PR.
- [ ] Aucun champ interne exposé sur un endpoint public.
- [ ] Tests : cas nominal + accès refusé, au minimum. Validation si c'est une écriture.
- [ ] Aucun appel réseau dans les tests (implémentations factices).
- [ ] Pas de requête N+1 (`preventLazyLoading` passe).
- [ ] Migration rétrocompatible, avec un `down()` fonctionnel.
- [ ] La `factory` est à jour — le dev frontend en dépend.
- [ ] **Si la tâche touche une donnée publique : les tags de revalidation sont invalidés**, et c'est testé.
- [ ] [`03-modele-de-donnees.md`](../01-architecture/03-modele-de-donnees.md) est à jour si le schéma a changé.

## Tâche frontend en plus

- [ ] Aucun type d'API redéclaré à la main — importés de `types/api.d.ts`.
- [ ] `tsc --noEmit` passe, aucun `any` ajouté.
- [ ] Les états chargement / vide / erreur sont traités, pas seulement le cas nominal.
- [ ] Testé en **mobile** (375 px) et en bureau. Captures d'écran des deux dans la PR.
- [ ] Toute image a `width`, `height` et un `alt` utile.
- [ ] Navigation clavier possible, focus visible.
- [ ] `"use client"` justifié en description de PR s'il y en a un nouveau.
- [ ] Sur `apps/web` : la route déclare ses tags de revalidation **et** son filet `revalidate`.
- [ ] Sur `apps/web` : le contenu est présent dans l'HTML rendu côté serveur (`curl` pour le vérifier).
- [ ] Aucune règle métier ajoutée côté front.
- [ ] Aucune régression du budget de performance (Lighthouse CI, dès M1).

## Tâche touchant le contrat en plus

- [ ] `openapi.yaml` modifié **avant** toute implémentation.
- [ ] Approbation du responsable architecture (CODEOWNERS l'impose).
- [ ] Un `example` par nouveau schéma — le front s'en sert pour ses données factices.
- [ ] Les codes d'erreur possibles sont documentés.
- [ ] Les valeurs d'énumération correspondent **exactement** à `app/Enums/`.
- [ ] Si c'est un changement cassant : mention `BREAKING CHANGE`, et les PR api/web/admin fusionnées le même jour.

## Pour clôturer un jalon en plus

- [ ] Tous les critères de sortie de [MVP & jalons](../04-planning/mvp-et-jalons.md) sont satisfaits.
- [ ] La liste de vérification manuelle du jalon est cochée ([tests](tests.md#vérification-manuelle-par-jalon)).
- [ ] Déployé en production et vérifié en production, pas seulement sur un aperçu.
- [ ] Revue de sécurité du jalon effectuée ([06 — Sécurité](../01-architecture/06-securite.md#revue-de-sécurité-par-jalon)).
- [ ] La [matrice de traçabilité](../00-contexte/tracabilite-exigences.md) est à jour.
- [ ] Le client a vu le résultat, et les écarts au CDC concernés sont validés par écrit.
- [ ] Le guide d'utilisation du backoffice est complété des nouvelles fonctions (livrable CDC §6).

---

## Ce qui n'est pas « terminé »

- « Ça marche chez moi. »
- « Les tests sont à faire dans une autre PR. »
- « Je documenterai après. »
- « Le contrat, je le mets à jour ensuite. »
- « Le mobile, on verra plus tard. »
- « J'ai laissé un TODO. »

Ces phrases signifient que la tâche est en cours, pas qu'elle est finie. Le report crée une dette invisible qui n'est jamais reprise — et sur un projet à deux personnes, personne d'autre ne la rattrapera.
