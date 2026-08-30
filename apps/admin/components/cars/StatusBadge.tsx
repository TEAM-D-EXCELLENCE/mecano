import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";

interface StatusBadgeProps {
  /** Énumération renvoyée par l'API : on affiche `label`, on compare sur `value`. */
  status: { value?: string; label?: string };
  className?: string;
}

/**
 * Le libellé vient toujours de l'API — il peut changer sans redéploiement du
 * front. Seule la couleur est une décision d'affichage, prise sur `value`.
 */
const TONE: Record<string, string> = {
  draft: "bg-muted text-muted-foreground border-transparent",
  available: "bg-success text-success-foreground border-transparent",
  reserved: "bg-warning text-warning-foreground border-transparent",
  sold: "bg-foreground text-background border-transparent",
};

export function StatusBadge({ status, className }: StatusBadgeProps) {
  const tone = status.value ? TONE[status.value] : undefined;

  return (
    <Badge variant="outline" className={cn(tone, className)}>
      {status.label ?? status.value ?? "—"}
    </Badge>
  );
}
