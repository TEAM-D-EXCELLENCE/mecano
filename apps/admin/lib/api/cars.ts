import "server-only";

import { apiRequest } from "./server";
import type { AdminBrand, AdminCar, Envelope, Paginated } from "./schemas";

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
