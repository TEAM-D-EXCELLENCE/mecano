import "server-only";

import { apiRequest } from "./server";
import type { AdminPost, AdminService, AdminSettings, Envelope, Paginated } from "./schemas";

/** Lectures serveur des forfaits atelier, du blog et des réglages. */

export async function listServices(): Promise<AdminService[]> {
  const { data } = await apiRequest<{ data: AdminService[] }>("admin/services");
  return data;
}

export async function listPosts(page = 1): Promise<Paginated<AdminPost>> {
  const suffix = page > 1 ? `?page=${page}` : "";
  return apiRequest<Paginated<AdminPost>>(`admin/posts${suffix}`);
}

export async function getPost(id: number): Promise<AdminPost> {
  const { data } = await apiRequest<Envelope<AdminPost>>(`admin/posts/${id}`);
  return data;
}

export async function getSettings(): Promise<AdminSettings> {
  const { data } = await apiRequest<Envelope<AdminSettings>>("admin/settings");
  return data;
}
