import type { components } from "@/types/api";

/**
 * Ré-exports des schémas du contrat.
 *
 * `types/api.d.ts` est généré depuis `openapi.yaml` (`npm run generate-types`) et
 * ne se modifie jamais à la main. Ce fichier ne fait que donner des noms courts
 * aux schémas les plus utilisés — il n'en redéfinit aucun.
 */
export type Schemas = components["schemas"];

export type User = Schemas["User"];
export type Brand = Schemas["Brand"];
export type CarListItem = Schemas["CarListItem"];
export type PaginationMeta = Schemas["PaginationMeta"];

/** Enveloppe des ressources unitaires : `{ data: … }`. */
export interface Envelope<T> {
  data: T;
}

/** Enveloppe paginée renvoyée par les endpoints de liste. */
export interface Paginated<T> {
  data: T[];
  meta: PaginationMeta;
}

export type DashboardMetrics = Schemas["DashboardMetrics"];
