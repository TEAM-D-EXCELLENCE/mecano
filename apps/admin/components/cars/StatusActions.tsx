"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { toast } from "sonner";
import { Loader2 } from "lucide-react";

import { Button } from "@/components/ui/button";
import { bff } from "@/lib/api/client";
import { messageForError } from "@/lib/api/errors";
import type { AdminCar, CarStatus } from "@/lib/api/schemas";

/**
 * Transitions autorisées, telles que `ChangeCarStatus` les applique côté API.
 *
 * Ce n'est pas une règle métier dupliquée : c'est l'affordance de l'interface.
 * L'API reste seule juge et refuse toute transition invalide — le front se
 * contente de ne pas proposer un bouton qui échouerait.
 */
const TRANSITIONS: Record<string, { to: CarStatus; label: string; variant?: "default" | "outline" }[]> = {
  draft: [{ to: "available", label: "Publier l'annonce" }],
  available: [
    { to: "reserved", label: "Marquer réservée", variant: "outline" },
    { to: "sold", label: "Marquer vendue", variant: "outline" },
  ],
  reserved: [
    { to: "available", label: "Remettre en ligne" },
    { to: "sold", label: "Marquer vendue", variant: "outline" },
  ],
  sold: [{ to: "available", label: "Remettre en ligne", variant: "outline" }],
};

export function StatusActions({ car }: { car: AdminCar }) {
  const router = useRouter();
  const [pending, setPending] = useState<CarStatus | null>(null);

  const current = car.status?.value ?? "draft";
  const actions = TRANSITIONS[current] ?? [];

  // Invariant CDC §3.1 : pas de publication sans photo principale. L'API le
  // vérifie ; on l'annonce ici pour éviter un aller-retour inutile.
  const blockedByPhoto = current === "draft" && car.is_publishable === false;

  const change = async (status: CarStatus) => {
    setPending(status);
    try {
      await bff(`admin/cars/${car.id}/status`, { method: "PATCH", body: { status } });
      toast.success("Statut mis à jour");
      router.refresh();
    } catch (error) {
      toast.error(messageForError(error));
    } finally {
      setPending(null);
    }
  };

  if (actions.length === 0) return null;

  return (
    <div className="flex flex-col gap-2">
      <div className="flex flex-wrap gap-2">
        {actions.map((action) => (
          <Button
            key={action.to}
            variant={action.variant ?? "default"}
            size="sm"
            disabled={pending !== null || (action.to === "available" && blockedByPhoto)}
            onClick={() => change(action.to)}
          >
            {pending === action.to ? (
              <>
                <Loader2 className="animate-spin" aria-hidden="true" />
                Mise à jour…
              </>
            ) : (
              action.label
            )}
          </Button>
        ))}
      </div>
      {blockedByPhoto ? (
        <p className="text-muted-foreground text-xs">
          Ajoutez une photo principale pour pouvoir publier cette annonce.
        </p>
      ) : null}
    </div>
  );
}
