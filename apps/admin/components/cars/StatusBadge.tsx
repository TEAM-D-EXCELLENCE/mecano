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
  available: "bg-emerald-100 text-emerald-900 border-transparent dark:bg-emerald-950 dark:text-emerald-200",
  reserved: "bg-amber-100 text-amber-900 border-transparent dark:bg-amber-950 dark:text-amber-200",
  sold: "bg-zinc-900 text-zinc-50 border-transparent dark:bg-zinc-100 dark:text-zinc-900",
};

export function StatusBadge({ status, className }: StatusBadgeProps) {
  const tone = status.value ? TONE[status.value] : undefined;

  return (
    <Badge variant="outline" className={cn(tone, className)}>
      {status.label ?? status.value ?? "—"}
    </Badge>
  );
}
