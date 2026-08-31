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

export type AdminCar = Schemas["AdminCarDetail"];
export type AdminBrand = Schemas["AdminBrand"];
export type AdminMedia = Schemas["AdminMedia"];
export type CreateCarBody = Schemas["CreateCarRequest"];
export type UpdateCarBody = Schemas["UpdateCarRequest"];
export type PaginationLinks = Schemas["PaginationLinks"];

/** Statuts d'annonce, tels que le contrat les déclare. */
export type CarStatus = "draft" | "available" | "reserved" | "sold";

export type AdminService = Schemas["AdminService"];
export type AdminPost = Schemas["AdminPost"];
export type AdminSettings = Schemas["AdminSettings"];
export type PostStatus = "draft" | "published";

export type SignedUpload = Schemas["SignedUpload"];

/** Rôles de média. `main`, `video_interior` et `video_exterior` sont exclusifs par annonce. */
export type MediaRole = "main" | "gallery" | "video_interior" | "video_exterior";
