import { cn } from "@/lib/utils";

/**
 * Marque du backoffice : un carré rouge plein, le disque du drapeau réduit à
 * l'essentiel, puis le nom en capitales resserrées. Aucune icône décorative —
 * la couleur suffit à identifier l'outil.
 */
export function Wordmark({ className }: { className?: string }) {
  return (
    <span className={cn("flex items-center gap-2.5", className)}>
      <span
        aria-hidden="true"
        className="bg-sidebar-primary flex size-7 items-center justify-center"
      >
        <span className="size-2.5 rounded-full bg-white" />
      </span>
      <span className="text-sm font-semibold tracking-[0.12em] uppercase">Mecano</span>
    </span>
  );
}
