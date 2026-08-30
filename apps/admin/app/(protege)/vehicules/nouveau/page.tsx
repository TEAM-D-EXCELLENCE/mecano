import type { Metadata } from "next";
import Link from "next/link";
import { ChevronLeft } from "lucide-react";

import { CarForm } from "@/components/forms/CarForm";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { listBrands } from "@/lib/api/cars";

export const metadata: Metadata = {
  title: "Nouvelle annonce",
};

export default async function NewCarPage() {
  const brands = await listBrands();

  return (
    <div className="flex max-w-3xl flex-col gap-6">
      <div className="flex flex-col gap-2">
        <Link
          href="/vehicules"
          className="text-muted-foreground hover:text-foreground focus-visible:ring-ring/50 inline-flex w-fit items-center gap-1 rounded-sm text-sm outline-none focus-visible:ring-[3px]"
        >
          <ChevronLeft className="size-4" aria-hidden="true" />
          Véhicules
        </Link>
        <h1 className="text-2xl font-semibold tracking-tight">Nouvelle annonce</h1>
        <p className="text-muted-foreground text-sm">
          L&apos;annonce est créée en brouillon. Elle ne sera visible du public
          qu&apos;après ajout d&apos;une photo principale et publication.
        </p>
      </div>

      {brands.length === 0 ? (
        <Alert variant="destructive">
          <AlertDescription>
            Aucune marque n&apos;est enregistrée. Créez-en une avant d&apos;ajouter un véhicule.
          </AlertDescription>
        </Alert>
      ) : (
        <CarForm brands={brands} />
      )}
    </div>
  );
}
