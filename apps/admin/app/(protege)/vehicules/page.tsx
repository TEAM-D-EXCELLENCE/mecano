import type { Metadata } from "next";
import Link from "next/link";
import { Plus } from "lucide-react";

import { StatusBadge } from "@/components/cars/StatusBadge";
import { Button } from "@/components/ui/button";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { listCars } from "@/lib/api/cars";
import type { CarStatus } from "@/lib/api/schemas";
import { formatMileage, formatPriceXaf } from "@/lib/format";
import { cn } from "@/lib/utils";

export const metadata: Metadata = {
  title: "Véhicules",
};

const FILTERS: { value: CarStatus | "tous"; label: string }[] = [
  { value: "tous", label: "Toutes" },
  { value: "draft", label: "Brouillons" },
  { value: "available", label: "En ligne" },
  { value: "reserved", label: "Réservées" },
  { value: "sold", label: "Vendues" },
];

const VALID_STATUSES: CarStatus[] = ["draft", "available", "reserved", "sold"];

export default async function CarsPage({ searchParams }: PageProps<"/vehicules">) {
  const params = await searchParams;

  // L'état filtrable vit dans l'URL : la vue est partageable et le bouton
  // retour du navigateur fonctionne.
  const raw = typeof params.statut === "string" ? params.statut : undefined;
  const status = VALID_STATUSES.find((s) => s === raw);
  const page = Number(typeof params.page === "string" ? params.page : "1") || 1;

  const { data: cars, meta } = await listCars({ status, page });

  return (
    <div className="flex flex-col gap-6">
      <header className="flex flex-wrap items-start justify-between gap-4">
        <div className="flex flex-col gap-1">
          <h1 className="text-2xl font-semibold tracking-tight">Véhicules</h1>
          <p className="text-muted-foreground text-sm">
            {countLabel(meta.total ?? 0, status)}
          </p>
        </div>
        <Button asChild>
          <Link href="/vehicules/nouveau">
            <Plus aria-hidden="true" />
            Nouvelle annonce
          </Link>
        </Button>
      </header>

      <nav aria-label="Filtrer par statut" className="flex flex-wrap gap-2">
        {FILTERS.map((filter) => {
          const active = filter.value === "tous" ? !status : filter.value === status;
          const href = filter.value === "tous" ? "/vehicules" : `/vehicules?statut=${filter.value}`;

          return (
            <Link
              key={filter.value}
              href={href}
              aria-current={active ? "page" : undefined}
              className={cn(
                "inline-flex min-h-9 items-center rounded-full border px-4 text-sm font-medium transition-colors",
                "focus-visible:ring-ring/50 outline-none focus-visible:ring-[3px]",
                active
                  ? "bg-primary text-primary-foreground border-transparent"
                  : "text-muted-foreground hover:bg-accent hover:text-accent-foreground",
              )}
            >
              {filter.label}
            </Link>
          );
        })}
      </nav>

      {cars.length === 0 ? (
        <EmptyState filtered={Boolean(status)} />
      ) : (
        <>
          <div className="overflow-x-auto rounded-xl border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Véhicule</TableHead>
                  <TableHead>Statut</TableHead>
                  <TableHead className="text-right">Prix</TableHead>
                  <TableHead className="text-right">Kilométrage</TableHead>
                  <TableHead className="text-right">Vues</TableHead>
                  <TableHead className="text-right">WhatsApp</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {cars.map((car) => (
                  <TableRow key={car.id}>
                    <TableCell>
                      <Link
                        href={`/vehicules/${car.id}`}
                        className="focus-visible:ring-ring/50 rounded-sm font-medium outline-none hover:underline focus-visible:ring-[3px]"
                      >
                        {car.brand?.name ? `${car.brand.name} ` : ""}
                        {car.model}
                      </Link>
                      <span className="text-muted-foreground block text-xs">
                        {car.year} · {car.color}
                      </span>
                    </TableCell>
                    <TableCell>
                      <StatusBadge status={car.status} />
                    </TableCell>
                    <TableCell className="text-right tabular-nums">
                      {formatPriceXaf(car.price_xaf)}
                    </TableCell>
                    <TableCell className="text-muted-foreground text-right tabular-nums">
                      {formatMileage(car.mileage_km)}
                    </TableCell>
                    <TableCell className="text-muted-foreground text-right tabular-nums">
                      {car.views_count ?? 0}
                    </TableCell>
                    <TableCell className="text-muted-foreground text-right tabular-nums">
                      {car.whatsapp_clicks_count ?? 0}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>

          <Pagination
            currentPage={meta.current_page}
            lastPage={meta.last_page}
            status={status}
          />
        </>
      )}
    </div>
  );
}

/**
 * Le décompte suit le filtre : annoncer « au total » sur une liste filtrée
 * ferait croire au mécanicien qu'il a perdu des annonces.
 */
function countLabel(total: number, status?: CarStatus): string {
  const plural = total > 1 ? "s" : "";
  if (status) return `${total} annonce${plural} dans cet état.`;
  return `${total} annonce${plural} au total, brouillons compris.`;
}

function EmptyState({ filtered }: { filtered: boolean }) {
  return (
    <div className="flex flex-col items-center gap-3 rounded-xl border border-dashed px-6 py-16 text-center">
      <p className="font-medium">
        {filtered ? "Aucune annonce dans cet état" : "Aucune annonce pour le moment"}
      </p>
      <p className="text-muted-foreground max-w-sm text-sm">
        {filtered
          ? "Changez de filtre pour voir les autres annonces."
          : "Créez votre première annonce, ajoutez-lui une photo principale, puis publiez-la."}
      </p>
      <Button asChild variant={filtered ? "outline" : "default"} className="mt-1">
        <Link href={filtered ? "/vehicules" : "/vehicules/nouveau"}>
          {filtered ? "Voir toutes les annonces" : "Créer une annonce"}
        </Link>
      </Button>
    </div>
  );
}

function Pagination({
  currentPage,
  lastPage,
  status,
}: {
  currentPage?: number;
  lastPage?: number;
  status?: CarStatus;
}) {
  const current = currentPage ?? 1;
  const last = lastPage ?? 1;

  if (last <= 1) return null;

  const href = (page: number) => {
    const query = new URLSearchParams();
    if (status) query.set("statut", status);
    if (page > 1) query.set("page", String(page));
    return query.size > 0 ? `/vehicules?${query}` : "/vehicules";
  };

  return (
    <nav aria-label="Pagination" className="flex items-center justify-between gap-4">
      <Button asChild variant="outline" size="sm" disabled={current <= 1}>
        <Link href={href(current - 1)} aria-disabled={current <= 1} tabIndex={current <= 1 ? -1 : undefined}>
          Précédent
        </Link>
      </Button>
      <p className="text-muted-foreground text-sm tabular-nums">
        Page {current} sur {last}
      </p>
      <Button asChild variant="outline" size="sm" disabled={current >= last}>
        <Link href={href(current + 1)} aria-disabled={current >= last} tabIndex={current >= last ? -1 : undefined}>
          Suivant
        </Link>
      </Button>
    </nav>
  );
}
