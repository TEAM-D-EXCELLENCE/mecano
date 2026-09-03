import "server-only";

import { apiRequest } from "./server";
import type {
  AdminBrand,
  AdminCar,
  AdminMedia,
  Envelope,
  IntegrationQuota,
  Paginated,
} from "./schemas";

/**
 * Lectures serveur du domaine véhicules.
 *
 * Le contrat n'expose qu'un filtre `status` sur `/admin/cars` : tout autre
 * paramètre n'existe pas tant qu'il n'est pas écrit dans `openapi.yaml`.
 */

export interface CarListParams {
  status?: string;
  page?: number;
}

export async function listCars(params: CarListParams = {}): Promise<Paginated<AdminCar>> {
  const query = new URLSearchParams();
  if (params.status) query.set("status", params.status);
  if (params.page && params.page > 1) query.set("page", String(params.page));

  const suffix = query.size > 0 ? `?${query}` : "";
  return apiRequest<Paginated<AdminCar>>(`admin/cars${suffix}`);
}

export async function getCar(id: number): Promise<AdminCar> {
  const { data } = await apiRequest<Envelope<AdminCar>>(`admin/cars/${id}`);
  return data;
}

export async function listBrands(): Promise<AdminBrand[]> {
  const { data } = await apiRequest<{ data: AdminBrand[] }>("admin/brands");
  return data;
}

export async function listCarMedia(carId: number): Promise<AdminMedia[]> {
  const { data } = await apiRequest<{ data: AdminMedia[] }>(`admin/cars/${carId}/media`);
  return data;
}

/**
 * Quota mensuel de suppression de fond.
 *
 * Lu côté serveur pour que le compteur soit affiché **avant** le premier clic
 * (CDC §3.2) : le mécanicien doit savoir ce qu'il lui reste avant de choisir,
 * pas après avoir consommé un crédit.
 */
export async function getRemoveBgQuota(): Promise<IntegrationQuota | null> {
  try {
    const { data } = await apiRequest<Envelope<IntegrationQuota>>("admin/quotas");
    return data;
  } catch {
    // Un quota indisponible ne doit pas faire tomber la fiche véhicule : la
    // page reste utilisable, le panneau affichera simplement l'état inconnu.
    return null;
  }
}
