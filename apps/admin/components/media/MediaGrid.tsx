"use client";

import { useState, useTransition } from "react";
import { useRouter } from "next/navigation";
import { toast } from "sonner";
import { ArrowLeft, ArrowRight, ImageOff, Loader2, Star, Trash2 } from "lucide-react";

import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { bff } from "@/lib/api/client";
import { messageForError } from "@/lib/api/errors";
import type { AdminMedia } from "@/lib/api/schemas";
import { formatBytes } from "@/lib/format";
import { cn } from "@/lib/utils";

/**
 * Galerie d'une annonce.
 *
 * Le réordonnancement se fait par flèches et non par glisser-déposer : le
 * mécanicien travaille au téléphone, dans un atelier, et un glissement précis
 * y est bien plus difficile qu'un appui sur une cible de 44 px.
 */
interface MediaGridProps {
  carId: number;
  media: AdminMedia[];
  /** Statut de l'annonce : il décide si retirer la dernière photo est risqué. */
  carStatus?: string;
}

export function MediaGrid({ carId, media, carStatus }: MediaGridProps) {
  const router = useRouter();
  const [busyId, setBusyId] = useState<number | null>(null);
  const [confirming, setConfirming] = useState<AdminMedia | null>(null);
  const [broken, setBroken] = useState<Set<number>>(new Set());
  const [, startTransition] = useTransition();

  const photos = media.filter((item) => item.kind?.value === "photo");

  // L'API laisse retirer la dernière photo d'une annonce déjà en ligne : elle
  // reste alors publiée, sans visuel, et continue d'être servie au public.
  // Tant que ce garde-fou n'existe pas côté API, on le pose ici.
  const isLastPhotoOfPublished = photos.length === 1 && carStatus !== undefined && carStatus !== "draft";

  const run = async (id: number, action: () => Promise<unknown>, success: string) => {
    setBusyId(id);
    try {
      await action();
      toast.success(success);
      startTransition(() => router.refresh());
    } catch (error) {
      toast.error(messageForError(error));
    } finally {
      setBusyId(null);
    }
  };

  const setMain = (item: AdminMedia) =>
    run(
      item.id,
      () => bff(`admin/media/${item.id}`, { method: "PATCH", body: { role: "main" } }),
      "Photo principale mise à jour",
    );

  const remove = (item: AdminMedia) =>
    run(
      item.id,
      () => bff(`admin/media/${item.id}`, { method: "DELETE" }),
      "Photo supprimée",
    );

  const askRemove = (item: AdminMedia) => {
    if (isLastPhotoOfPublished) {
      setConfirming(item);
      return;
    }
    void remove(item);
  };

  const move = (index: number, direction: -1 | 1) => {
    const target = index + direction;
    if (target < 0 || target >= photos.length) return;

    const ordered = [...photos];
    [ordered[index], ordered[target]] = [ordered[target], ordered[index]];

    return run(
      photos[index].id,
      () =>
        bff(`admin/cars/${carId}/media/reorder`, {
          method: "POST",
          body: { media_ids: ordered.map((item) => item.id) },
        }),
      "Ordre mis à jour",
    );
  };

  const saveAlt = (item: AdminMedia, alt: string) => {
    if ((item.alt ?? "") === alt) return;
    return run(
      item.id,
      () => bff(`admin/media/${item.id}`, { method: "PATCH", body: { alt: alt || null } }),
      "Description enregistrée",
    );
  };

  if (photos.length === 0) return null;

  return (
    <>
    <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      {photos.map((item, index) => {
        const isMain = item.role?.value === "main";
        const busy = busyId === item.id;

        return (
          <li
            key={item.id}
            className={cn(
              "bg-card flex flex-col border",
              isMain && "border-primary",
            )}
          >
            <div className="bg-muted relative aspect-4/3 overflow-hidden">
              {broken.has(item.id) ? (
                // Une image injoignable affiche autrement son texte alternatif en
                // vrac, sur toute la vignette. Un repère sobre dit la même chose
                // sans casser la grille.
                <div className="text-muted-foreground flex size-full flex-col items-center justify-center gap-2 px-3 text-center">
                  <ImageOff className="size-5" aria-hidden="true" />
                  <span className="text-xs">Image indisponible</span>
                </div>
              ) : (
                /* L'API fournit des URL complètes : le front ne compose jamais
                   d'adresse Cloudinary. `next/image` est écarté ici car les
                   domaines d'hébergement varient selon l'environnement. */
                /* eslint-disable-next-line @next/next/no-img-element */
                <img
                  src={item.published_url ?? item.url}
                  alt={item.alt ?? ""}
                  width={item.width ?? undefined}
                  height={item.height ?? undefined}
                  loading="lazy"
                  onError={() => setBroken((current) => new Set(current).add(item.id))}
                  className="size-full object-cover"
                />
              )}
              {isMain ? (
                <Badge className="bg-primary text-primary-foreground absolute top-2 left-2 border-transparent shadow-sm">
                  Principale
                </Badge>
              ) : null}
              {busy ? (
                <div className="bg-background/70 absolute inset-0 flex items-center justify-center">
                  <Loader2 className="animate-spin" aria-hidden="true" />
                </div>
              ) : null}
            </div>

            <div className="flex flex-col gap-3 p-3">
              <Input
                defaultValue={item.alt ?? ""}
                placeholder="Décrire la photo"
                aria-label={`Description de la photo ${index + 1}`}
                onBlur={(event) => void saveAlt(item, event.target.value.trim())}
              />

              <div className="text-muted-foreground flex items-center justify-between text-xs">
                <span>
                  {item.width && item.height ? `${item.width} × ${item.height}` : "Dimensions inconnues"}
                </span>
                <span>{item.bytes ? formatBytes(item.bytes) : ""}</span>
              </div>

              {/* Une seule rangée, sans repli : les commandes gardent la même
                  place d'une vignette à l'autre, ce qui les rend visables au
                  doigt sans avoir à les chercher. */}
              <div className="flex items-center gap-1">
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  aria-label={`Déplacer la photo ${index + 1} vers la gauche`}
                  disabled={busy || index === 0}
                  onClick={() => void move(index, -1)}
                >
                  <ArrowLeft aria-hidden="true" />
                </Button>
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  aria-label={`Déplacer la photo ${index + 1} vers la droite`}
                  disabled={busy || index === photos.length - 1}
                  onClick={() => void move(index, 1)}
                >
                  <ArrowRight aria-hidden="true" />
                </Button>

                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  aria-label="Définir comme photo principale"
                  title="Définir comme photo principale"
                  disabled={busy || isMain}
                  onClick={() => void setMain(item)}
                >
                  <Star aria-hidden="true" className={cn(isMain && "fill-current")} />
                </Button>

                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  aria-label={`Supprimer la photo ${index + 1}`}
                  title="Supprimer"
                  className="text-destructive ml-auto"
                  disabled={busy}
                  onClick={() => askRemove(item)}
                >
                  <Trash2 aria-hidden="true" />
                </Button>
              </div>
            </div>
          </li>
        );
      })}
    </ul>

    <Dialog open={confirming !== null} onOpenChange={(open) => !open && setConfirming(null)}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Supprimer la dernière photo ?</DialogTitle>
          <DialogDescription>
            Cette annonce est en ligne. Sans photo, elle restera visible du public
            mais sans aucun visuel — et un acheteur ne clique pas sur une annonce
            sans photo. Ajoutez d&apos;abord une autre photo, ou retirez
            l&apos;annonce de la vente.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <DialogClose asChild>
            <Button variant="outline">Annuler</Button>
          </DialogClose>
          <Button
            variant="destructive"
            onClick={() => {
              const target = confirming;
              setConfirming(null);
              if (target) void remove(target);
            }}
          >
            Supprimer quand même
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
    </>
  );
}
