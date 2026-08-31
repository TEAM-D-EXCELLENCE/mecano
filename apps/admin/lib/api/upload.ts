"use client";

import { bff } from "./client";
import type { AdminMedia, MediaRole, SignedUpload } from "./schemas";

/**
 * Upload direct vers l'hébergeur.
 *
 * Le fichier ne traverse jamais l'API : le BFF ne délivre qu'une signature, le
 * navigateur envoie le fichier lui-même, puis confirme. C'est ce qui évite de
 * mobiliser un processus PHP pendant tout un transfert (ADR 0007).
 */

interface UploadOptions {
  carId: number;
  file: File;
  role: MediaRole;
  /** Progression de 0 à 1 pendant le transfert vers l'hébergeur. */
  onProgress?: (ratio: number) => void;
  signal?: AbortSignal;
}

/** Erreur survenue chez l'hébergeur, en dehors de l'API. */
export class UploadError extends Error {
  constructor(message: string) {
    super(message);
    this.name = "UploadError";
  }
}

const VIDEO_ROLES: MediaRole[] = ["video_interior", "video_exterior"];

/**
 * Dimensions lues dans le navigateur.
 *
 * L'hébergeur factice utilisé en développement ne renvoie pas de métadonnées ;
 * les lire ici évite d'enregistrer une photo sans largeur ni hauteur, ce dont
 * dépend la vitrine pour réserver la place de l'image et éviter que la page
 * saute au chargement.
 */
async function readDimensions(file: File): Promise<{ width?: number; height?: number }> {
  if (!file.type.startsWith("image/")) return {};

  try {
    const bitmap = await createImageBitmap(file);
    const size = { width: bitmap.width, height: bitmap.height };
    bitmap.close();
    return size;
  } catch {
    return {};
  }
}

/** Envoie le fichier à l'hébergeur en suivant la progression. */
function putToProvider(
  signed: SignedUpload,
  file: File,
  onProgress?: (ratio: number) => void,
  signal?: AbortSignal,
): Promise<void> {
  return new Promise((resolve, reject) => {
    const form = new FormData();
    for (const [key, value] of Object.entries(signed.fields ?? {})) {
      form.append(key, String(value));
    }
    form.append("file", file);

    const request = new XMLHttpRequest();
    request.open("POST", signed.upload_url);

    request.upload.addEventListener("progress", (event) => {
      if (event.lengthComputable) onProgress?.(event.loaded / event.total);
    });

    request.addEventListener("load", () => {
      if (request.status >= 200 && request.status < 300) {
        resolve();
      } else {
        reject(
          new UploadError(
            `L'hébergeur a refusé le fichier (${request.status}). Vérifiez son format et sa taille.`,
          ),
        );
      }
    });

    request.addEventListener("error", () =>
      reject(new UploadError("Le transfert vers l'hébergeur a échoué. Vérifiez votre connexion.")),
    );
    request.addEventListener("abort", () => reject(new DOMException("Aborted", "AbortError")));

    signal?.addEventListener("abort", () => request.abort(), { once: true });

    request.send(form);
  });
}

/**
 * Signature, envoi, confirmation. Renvoie le média enregistré.
 *
 * Un échec après l'envoi laisse un objet orphelin chez l'hébergeur : la commande
 * `media:purge-orphans` s'en charge, c'est pourquoi on n'essaie pas de rattraper
 * le coup ici.
 */
export async function uploadMedia({
  carId,
  file,
  role,
  onProgress,
  signal,
}: UploadOptions): Promise<AdminMedia> {
  const kind = VIDEO_ROLES.includes(role) ? "video" : "photo";

  const { data: signed } = await bff<{ data: SignedUpload }>("admin/media/upload-signature", {
    method: "POST",
    body: { car_id: carId, kind, mime: file.type, bytes: file.size },
    signal,
  });

  await putToProvider(signed, file, onProgress, signal);

  const { width, height } = await readDimensions(file);

  const { data: media } = await bff<{ data: AdminMedia }>(`admin/cars/${carId}/media`, {
    method: "POST",
    body: {
      storage_key: signed.storage_key,
      role,
      mime: file.type,
      bytes: file.size,
      width: width ?? null,
      height: height ?? null,
    },
    signal,
  });

  return media;
}
