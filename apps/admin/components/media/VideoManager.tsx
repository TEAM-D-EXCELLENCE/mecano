"use client";

import { useRef, useState } from "react";
import { useRouter } from "next/navigation";
import { toast } from "sonner";
import { Loader2, Trash2, Video } from "lucide-react";

import { Button } from "@/components/ui/button";
import { bff } from "@/lib/api/client";
import { messageForError } from "@/lib/api/errors";
import { UploadError, uploadMedia } from "@/lib/api/upload";
import type { AdminMedia, MediaRole } from "@/lib/api/schemas";
import { formatBytes } from "@/lib/format";

/**
 * Vidéos d'une annonce (CDC §3.3, FE-30).
 *
 * Deux emplacements seulement, extérieur et intérieur : l'API remplace la
 * précédente quand on réenvoie le même rôle. Le fichier part directement chez
 * l'hébergeur en PUT présigné, jamais à travers l'API.
 *
 * La reprise après coupure n'est pas offerte : elle suppose un envoi en
 * plusieurs morceaux que la signature actuelle ne couvre pas. Un transfert
 * interrompu se relance donc depuis le début.
 */

const SLOTS: { role: MediaRole; label: string }[] = [
  { role: "video_exterior", label: "Extérieur" },
  { role: "video_interior", label: "Intérieur" },
];

const ACCEPTED = "video/mp4,video/quicktime";

interface VideoManagerProps {
  carId: number;
  media: AdminMedia[];
}

export function VideoManager({ carId, media }: VideoManagerProps) {
  const router = useRouter();
  const inputRef = useRef<HTMLInputElement>(null);
  const [target, setTarget] = useState<MediaRole | null>(null);
  const [ratio, setRatio] = useState(0);
  const [removing, setRemoving] = useState<number | null>(null);

  const videos = media.filter((item) => item.kind?.value === "video");
  const busy = target !== null;

  const pick = (role: MediaRole) => {
    setTarget(role);
    inputRef.current?.click();
  };

  const send = async (file: File) => {
    if (target === null) return;

    setRatio(0);
    try {
      await uploadMedia({ carId, file, role: target, onProgress: setRatio });
      toast.success("Vidéo ajoutée");
      router.refresh();
    } catch (error) {
      toast.error(
        error instanceof UploadError ? error.message : messageForError(error),
      );
    } finally {
      setTarget(null);
      setRatio(0);
      if (inputRef.current) inputRef.current.value = "";
    }
  };

  const remove = async (item: AdminMedia) => {
    setRemoving(item.id!);
    try {
      await bff(`admin/media/${item.id}`, { method: "DELETE" });
      toast.success("Vidéo supprimée");
      router.refresh();
    } catch (error) {
      toast.error(messageForError(error));
    } finally {
      setRemoving(null);
    }
  };

  return (
    <div className="flex flex-col gap-3">
      <input
        ref={inputRef}
        type="file"
        accept={ACCEPTED}
        className="sr-only"
        onChange={(event) => {
          const file = event.target.files?.[0];
          if (file) void send(file);
        }}
      />

      <div className="grid gap-2 sm:grid-cols-2">
        {SLOTS.map(({ role, label }) => {
          const existing = videos.find((item) => item.role?.value === role);
          const uploading = target === role;

          return (
            <div key={role} className="flex flex-col gap-2 border p-3">
              <div className="flex items-center justify-between gap-2">
                <span className="flex items-center gap-2 text-sm font-medium">
                  <Video className="size-4" aria-hidden="true" />
                  {label}
                </span>
                {existing ? (
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="text-destructive"
                    aria-label={`Supprimer la vidéo ${label.toLowerCase()}`}
                    disabled={removing === existing.id || busy}
                    onClick={() => void remove(existing)}
                  >
                    {removing === existing.id ? (
                      <Loader2 className="animate-spin" aria-hidden="true" />
                    ) : (
                      <Trash2 aria-hidden="true" />
                    )}
                  </Button>
                ) : null}
              </div>

              {existing ? (
                <>
                  <video
                    src={existing.published_url ?? existing.url ?? undefined}
                    controls
                    preload="none"
                    className="bg-muted aspect-video w-full"
                  />
                  <p className="text-muted-foreground text-xs">
                    {existing.bytes ? formatBytes(existing.bytes) : "Taille inconnue"}
                  </p>
                </>
              ) : (
                <p className="text-muted-foreground text-xs">
                  Aucune vidéo. MP4 ou MOV, 200 Mo maximum.
                </p>
              )}

              {uploading ? (
                <div className="flex flex-col gap-1">
                  <div className="bg-muted h-1.5 w-full overflow-hidden">
                    <div
                      className="bg-primary h-full transition-[width]"
                      style={{ width: `${Math.round(ratio * 100)}%` }}
                    />
                  </div>
                  <p className="text-muted-foreground text-xs tabular-nums">
                    Envoi {Math.round(ratio * 100)} % — ne fermez pas la page.
                  </p>
                </div>
              ) : (
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  disabled={busy}
                  onClick={() => pick(role)}
                >
                  {existing ? "Remplacer" : "Ajouter"}
                </Button>
              )}
            </div>
          );
        })}
      </div>
    </div>
  );
}
