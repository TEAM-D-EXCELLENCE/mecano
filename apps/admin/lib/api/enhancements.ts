"use client";

import { bff } from "./client";
import type { EnhancementType, IntegrationQuota, MediaEnhancement } from "./schemas";

/**
 * Retouches d'une photo (CDC §3.2).
 *
 * Le dérivé produit n'est jamais servi au public tant qu'il n'est pas approuvé :
 * c'est l'approbation, et elle seule, qui bascule `published_url`. Le
 * backoffice doit donc toujours proposer la comparaison avant l'approbation.
 */

export async function listEnhancements(mediaId: number): Promise<MediaEnhancement[]> {
  const { data } = await bff<{ data: MediaEnhancement[] }>(`admin/media/${mediaId}/enhancements`);
  return data;
}

export async function requestEnhancement(
  mediaId: number,
  type: EnhancementType,
): Promise<MediaEnhancement> {
  const { data } = await bff<{ data: MediaEnhancement }>(`admin/media/${mediaId}/enhance`, {
    method: "POST",
    body: { type },
  });
  return data;
}

export async function approveEnhancement(id: number): Promise<MediaEnhancement> {
  const { data } = await bff<{ data: MediaEnhancement }>(`admin/enhancements/${id}/approve`, {
    method: "POST",
  });
  return data;
}

export async function getQuota(): Promise<IntegrationQuota> {
  const { data } = await bff<{ data: IntegrationQuota }>("admin/quotas");
  return data;
}
