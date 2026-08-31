"use client";

import { useRef, useState } from "react";
import { useRouter } from "next/navigation";
import { toast } from "sonner";
import { ImagePlus, Loader2 } from "lucide-react";

import { Button } from "@/components/ui/button";
import { messageForError } from "@/lib/api/errors";
import { UploadError, uploadMedia } from "@/lib/api/upload";
import type { MediaRole } from "@/lib/api/schemas";
import { cn } from "@/lib/utils";

interface MediaUploaderProps {
  carId: number;
  /** Aucune photo encore : la première envoyée devient la photo principale. */
  hasMainPhoto: boolean;
}

interface Pending {
  name: string;
  ratio: number;
}

const ACCEPTED = "image/jpeg,image/png,image/webp,image/heic";

export function MediaUploader({ carId, hasMainPhoto }: MediaUploaderProps) {
  const router = useRouter();
  const inputRef = useRef<HTMLInputElement>(null);
  const [pending, setPending] = useState<Pending[]>([]);
  const [dragging, setDragging] = useState(false);

  const busy = pending.length > 0;

  const send = async (files: FileList | File[]) => {
    const list = Array.from(files);
    if (list.length === 0) return;

    setPending(list.map((file) => ({ name: file.name, ratio: 0 })));

    let mainAssigned = hasMainPhoto;
    let succeeded = 0;

    // Séquentiel plutôt que parallèle : la photo principale est attribuée à la
    // première envoyée, et l'ordre d'arrivée décide des positions.
    for (const [index, file] of list.entries()) {
      const role: MediaRole = mainAssigned ? "gallery" : "main";

      try {
        await uploadMedia({
          carId,
          file,
          role,
          onProgress: (ratio) =>
            setPending((current) =>
              current.map((item, i) => (i === index ? { ...item, ratio } : item)),
            ),
        });
        if (role === "main") mainAssigned = true;
        succeeded += 1;
      } catch (error) {
        const message =
          error instanceof UploadError ? error.message : messageForError(error);
        toast.error(`${file.name} — ${message}`);
      }
    }

    setPending([]);
    if (inputRef.current) inputRef.current.value = "";

    if (succeeded > 0) {
      toast.success(
        succeeded > 1 ? `${succeeded} photos ajoutées` : "Photo ajoutée",
      );
      router.refresh();
    }
  };

  return (
    <div className="flex flex-col gap-3">
      <div
        onDragOver={(event) => {
          event.preventDefault();
          setDragging(true);
        }}
        onDragLeave={() => setDragging(false)}
        onDrop={(event) => {
          event.preventDefault();
          setDragging(false);
          if (!busy) void send(event.dataTransfer.files);
        }}
        className={cn(
          "flex flex-col items-center gap-3 border border-dashed px-6 py-10 text-center transition-colors",
          dragging ? "border-primary bg-primary/5" : "border-border",
        )}
      >
        <ImagePlus className="text-muted-foreground size-6" aria-hidden="true" />
        <div className="flex flex-col gap-1">
          <p className="text-sm font-medium">
            {hasMainPhoto
              ? "Ajouter des photos"
              : "Ajoutez la photo principale pour pouvoir publier"}
          </p>
          <p className="text-muted-foreground text-xs">
            JPEG, PNG, WebP ou HEIC — 15 Mo maximum par photo
          </p>
        </div>

        <input
          ref={inputRef}
          type="file"
          accept={ACCEPTED}
          multiple
          className="sr-only"
          onChange={(event) => {
            if (event.target.files) void send(event.target.files);
          }}
        />

        <Button
          type="button"
          variant="outline"
          disabled={busy}
          onClick={() => inputRef.current?.click()}
        >
          {busy ? (
            <>
              <Loader2 className="animate-spin" aria-hidden="true" />
              Envoi en cours…
            </>
          ) : (
            "Choisir des photos"
          )}
        </Button>
      </div>

      {pending.length > 0 ? (
        <ul className="flex flex-col gap-2">
          {pending.map((item) => (
            <li key={item.name} className="flex flex-col gap-1">
              <div className="flex items-baseline justify-between gap-3 text-xs">
                <span className="truncate">{item.name}</span>
                <span className="text-muted-foreground tabular-nums">
                  {Math.round(item.ratio * 100)} %
                </span>
              </div>
              <div className="bg-muted h-1 w-full overflow-hidden">
                <div
                  className="bg-primary h-full transition-[width]"
                  style={{ width: `${Math.round(item.ratio * 100)}%` }}
                />
              </div>
            </li>
          ))}
        </ul>
      ) : null}
    </div>
  );
}
