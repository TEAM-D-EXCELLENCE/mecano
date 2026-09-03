"use client";

import { useCallback, useState } from "react";
import { useRouter } from "next/navigation";
import { toast } from "sonner";
import { Check, Loader2, Sparkles, Crop, Scissors, TriangleAlert } from "lucide-react";

import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import {
  approveEnhancement,
  getQuota,
  listEnhancements,
  requestEnhancement,
} from "@/lib/api/enhancements";
import { messageForError } from "@/lib/api/errors";
import type {
  AdminMedia,
  EnhancementType,
  IntegrationQuota,
  MediaEnhancement,
} from "@/lib/api/schemas";

/**
 * Retouche d'une photo, avec comparaison avant / après (CDC §3.2).
 *
 * Le dérivé produit reste invisible du public tant qu'il n'est pas approuvé.
 * L'écran impose donc l'ordre : on demande, on compare, puis on décide — jamais
 * une retouche qui s'applique d'elle-même.
 */

interface EnhanceDialogProps {
  media: AdminMedia;
  /** Quota lu côté serveur : le compteur doit être lisible avant le premier clic. */
  initialQuota: IntegrationQuota | null;
}

const ACTIONS: { type: EnhancementType; label: string; hint: string; icon: typeof Sparkles }[] = [
  {
    type: "auto_improve",
    label: "Améliorer",
    hint: "Contraste, luminosité et netteté",
    icon: Sparkles,
  },
  {
    type: "smart_crop",
    label: "Recadrer",
    hint: "Cadrage centré sur le véhicule",
    icon: Crop,
  },
  {
    type: "background_removal",
    label: "Détourer",
    hint: "Retire le fond — consomme un crédit",
    icon: Scissors,
  },
];

/** Un dérivé prêt attend une décision ; approuvé, il est déjà en ligne. */
const isPending = (item: MediaEnhancement) => item.status?.value === "ready";

export function EnhanceDialog({ media, initialQuota }: EnhanceDialogProps) {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [quota, setQuota] = useState<IntegrationQuota | null>(initialQuota);
  const [enhancements, setEnhancements] = useState<MediaEnhancement[]>([]);
  const [comparing, setComparing] = useState<MediaEnhancement | null>(null);
  const [running, setRunning] = useState<EnhancementType | null>(null);
  const [approving, setApproving] = useState(false);
  const [loading, setLoading] = useState(false);

  const refresh = useCallback(async () => {
    setLoading(true);
    try {
      const list = await listEnhancements(media.id!);
      setEnhancements(list);
      setComparing((current) => current ?? list.find(isPending) ?? null);
    } catch (error) {
      toast.error(messageForError(error));
    } finally {
      setLoading(false);
    }
  }, [media.id]);

  // La lecture est déclenchée à l'ouverture plutôt que par un effet : elle
  // répond à un geste, pas à une synchronisation d'état.
  const toggle = useCallback(
    (next: boolean) => {
      setOpen(next);
      if (next) void refresh();
    },
    [refresh],
  );

  const remaining = quota?.available ?? null;
  const exhausted = remaining !== null && remaining <= 0;

  const run = async (type: EnhancementType) => {
    setRunning(type);
    try {
      const created = await requestEnhancement(media.id!, type);

      if (created.status?.value === "failed") {
        // Le crédit est rendu par l'API ; on le relit plutôt que de le déduire.
        toast.error(created.error ?? "La retouche a échoué.");
      } else {
        setComparing(created);
        toast.success("Retouche prête — comparez avant d'approuver.");
      }

      setEnhancements((current) => [created, ...current]);

      if (type === "background_removal") {
        setQuota(await getQuota().catch(() => quota));
      }
    } catch (error) {
      toast.error(messageForError(error));
    } finally {
      setRunning(null);
    }
  };

  const approve = async (item: MediaEnhancement) => {
    setApproving(true);
    try {
      await approveEnhancement(item.id!);
      toast.success("Retouche publiée sur la fiche.");
      setOpen(false);
      router.refresh();
    } catch (error) {
      toast.error(messageForError(error));
    } finally {
      setApproving(false);
    }
  };

  const original = media.url ?? media.published_url ?? "";
  const busy = running !== null || approving;

  return (
    <Dialog open={open} onOpenChange={toggle}>
      <DialogTrigger asChild>
        <Button type="button" variant="ghost" size="icon" title="Retoucher" aria-label="Retoucher la photo">
          <Sparkles aria-hidden="true" />
        </Button>
      </DialogTrigger>

      <DialogContent className="sm:max-w-2xl">
        <DialogHeader>
          <DialogTitle>Retoucher la photo</DialogTitle>
          <DialogDescription>
            Rien n&apos;est publié tant que vous n&apos;avez pas approuvé. Comparez
            l&apos;avant et l&apos;après, puis décidez.
          </DialogDescription>
        </DialogHeader>

        <div className="flex flex-col gap-5">
          <div className="flex flex-col gap-3">
            <div className="grid gap-2 sm:grid-cols-3">
              {ACTIONS.map(({ type, label, hint, icon: Icon }) => {
                const blocked = type === "background_removal" && exhausted;

                return (
                  <Button
                    key={type}
                    type="button"
                    variant="outline"
                    className="h-auto flex-col items-start gap-1 py-3 text-left whitespace-normal"
                    disabled={busy || blocked}
                    title={blocked ? "Quota mensuel épuisé" : hint}
                    onClick={() => void run(type)}
                  >
                    <span className="flex items-center gap-2 font-medium">
                      {running === type ? (
                        <Loader2 className="size-4 animate-spin" aria-hidden="true" />
                      ) : (
                        <Icon className="size-4" aria-hidden="true" />
                      )}
                      {label}
                    </span>
                    <span className="text-muted-foreground text-xs font-normal">{hint}</span>
                  </Button>
                );
              })}
            </div>

            {/* Compteur visible avant le clic : le mécanicien doit savoir ce
                qu'il lui reste au moment de choisir, pas après. */}
            <p
              className={
                exhausted
                  ? "text-destructive flex items-center gap-2 text-xs"
                  : "text-muted-foreground text-xs"
              }
            >
              {quota === null ? (
                "Crédits de détourage indisponibles pour le moment."
              ) : exhausted ? (
                <>
                  <TriangleAlert className="size-3.5" aria-hidden="true" />
                  Quota de détourage épuisé ({quota.used} / {quota.limit}). Il repart le 1er du mois.
                </>
              ) : (
                <>
                  Détourage : {quota.available} crédit{quota.available > 1 ? "s" : ""} restant
                  {quota.available > 1 ? "s" : ""} sur {quota.limit} ce mois-ci.
                </>
              )}
            </p>
          </div>

          {comparing?.result_url ? (
            <div className="flex flex-col gap-3">
              <div className="grid grid-cols-2 gap-3">
                <figure className="flex flex-col gap-1">
                  <figcaption className="text-muted-foreground text-xs">Avant</figcaption>
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img
                    src={original}
                    alt="Photo actuelle"
                    className="bg-muted aspect-4/3 w-full border object-cover"
                  />
                </figure>
                <figure className="flex flex-col gap-1">
                  <figcaption className="text-muted-foreground text-xs">Après</figcaption>
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img
                    src={comparing.result_url}
                    alt="Photo retouchée, en attente d'approbation"
                    className="bg-muted aspect-4/3 w-full border object-cover"
                  />
                </figure>
              </div>

              <div className="flex flex-wrap items-center gap-2">
                <Button type="button" disabled={busy} onClick={() => void approve(comparing)}>
                  {approving ? (
                    <Loader2 className="animate-spin" aria-hidden="true" />
                  ) : (
                    <Check aria-hidden="true" />
                  )}
                  Approuver et publier
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  disabled={busy}
                  onClick={() => setComparing(null)}
                >
                  Garder l&apos;original
                </Button>
              </div>
            </div>
          ) : null}

          {loading ? (
            <p className="text-muted-foreground flex items-center gap-2 text-xs">
              <Loader2 className="size-3.5 animate-spin" aria-hidden="true" />
              Lecture des retouches déjà demandées…
            </p>
          ) : null}

          {enhancements.length > 0 ? (
            <ul className="flex flex-col gap-2 border-t pt-3">
              {enhancements.map((item) => (
                <li key={item.id} className="flex items-center justify-between gap-3 text-xs">
                  <span className="flex items-center gap-2">
                    <Badge variant="outline">{item.type?.label ?? item.type?.value}</Badge>
                    <span className="text-muted-foreground">
                      {item.status?.label ?? item.status?.value}
                      {item.error ? ` — ${item.error}` : ""}
                    </span>
                  </span>
                  {isPending(item) && item.id !== comparing?.id ? (
                    <Button
                      type="button"
                      variant="ghost"
                      size="sm"
                      disabled={busy}
                      onClick={() => setComparing(item)}
                    >
                      Comparer
                    </Button>
                  ) : null}
                </li>
              ))}
            </ul>
          ) : null}
        </div>
      </DialogContent>
    </Dialog>
  );
}
