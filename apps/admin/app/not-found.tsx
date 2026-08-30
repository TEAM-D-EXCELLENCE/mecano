import Link from "next/link";

import { Button } from "@/components/ui/button";

export default function NotFound() {
  return (
    <main className="flex flex-1 flex-col items-center justify-center gap-4 p-6 text-center">
      <p className="text-muted-foreground font-mono text-sm">404</p>
      <h1 className="text-xl font-semibold tracking-tight">Page introuvable</h1>
      <p className="text-muted-foreground max-w-sm text-sm">
        Cette page n&apos;existe pas ou a été déplacée.
      </p>
      <Button asChild variant="outline">
        <Link href="/tableau-de-bord">Retour au tableau de bord</Link>
      </Button>
    </main>
  );
}
