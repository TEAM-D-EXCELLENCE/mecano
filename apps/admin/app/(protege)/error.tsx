"use client";

import { useEffect } from "react";
import { AlertCircle } from "lucide-react";

import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Button } from "@/components/ui/button";

/**
 * Frontière d'erreur du backoffice.
 *
 * Une API injoignable est un état normal du système, pas un plantage : le
 * mécanicien doit voir un message clair et pouvoir réessayer sans recharger.
 */
export default function ProtectedError({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    console.error(error);
  }, [error]);

  return (
    <div className="mx-auto flex max-w-md flex-col gap-4 py-12">
      <Alert variant="destructive">
        <AlertCircle aria-hidden="true" />
        <AlertTitle>Impossible de charger cette page</AlertTitle>
        <AlertDescription>
          Le serveur n&apos;a pas répondu comme prévu. Réessayez dans un instant.
        </AlertDescription>
      </Alert>
      <Button onClick={reset} variant="outline" className="self-start">
        Réessayer
      </Button>
    </div>
  );
}
