import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { ChevronLeft } from "lucide-react";

import { DeleteCarButton } from "@/components/cars/DeleteCarButton";
import { StatusActions } from "@/components/cars/StatusActions";
import { StatusBadge } from "@/components/cars/StatusBadge";
import { CarForm } from "@/components/forms/CarForm";
import { MediaGrid } from "@/components/media/MediaGrid";
import { MediaUploader } from "@/components/media/MediaUploader";
import { ApiError } from "@/lib/api/errors";
import { getCar, listBrands, listCarMedia } from "@/lib/api/cars";
import { formatDate } from "@/lib/format";

async function load(rawId: string) {
  const id = Number(rawId);
  if (!Number.isInteger(id) || id <= 0) notFound();

  try {
    const [car, brands, media] = await Promise.all([
      getCar(id),
      listBrands(),
      listCarMedia(id),
    ]);
    return { car, brands, media };
  } catch (error) {
    if (error instanceof ApiError && error.status === 404) notFound();
    throw error;
  }
}

export async function generateMetadata({ params }: PageProps<"/vehicules/[id]">): Promise<Metadata> {
  const { id } = await params;
  try {
    const car = await getCar(Number(id));
    return { title: `${car.brand?.name ?? ""} ${car.model}`.trim() };
  } catch {
    return { title: "Annonce" };
  }
}

export default async function CarDetailPage({ params }: PageProps<"/vehicules/[id]">) {
  const { id } = await params;
  const { car, brands, media } = await load(id);
  const photos = media.filter((item) => item.kind?.value === "photo");

  const title = `${car.brand?.name ? `${car.brand.name} ` : ""}${car.model}`;

  return (
    <div className="flex max-w-3xl flex-col gap-8">
      <header className="flex flex-col gap-4">
        <Link
          href="/vehicules"
          className="text-muted-foreground hover:text-foreground focus-visible:ring-ring/50 inline-flex w-fit items-center gap-1 rounded-sm text-sm outline-none focus-visible:ring-[3px]"
        >
          <ChevronLeft className="size-4" aria-hidden="true" />
          Véhicules
        </Link>

        <div className="flex flex-wrap items-start justify-between gap-3">
          <div className="flex flex-col gap-2">
            <div className="flex flex-wrap items-center gap-3">
              <h1 className="text-2xl font-semibold tracking-tight">{title}</h1>
              <StatusBadge status={car.status ?? {}} />
            </div>
            <p className="text-muted-foreground font-mono text-xs">{car.slug}</p>
          </div>
          <DeleteCarButton carId={car.id!} label={title} />
        </div>

        <dl className="text-muted-foreground grid grid-cols-2 gap-x-6 gap-y-1 text-sm sm:grid-cols-4">
          <Meta label="Vues" value={String(car.views_count ?? 0)} />
          <Meta label="Clics WhatsApp" value={String(car.whatsapp_clicks_count ?? 0)} />
          <Meta label="Publiée le" value={formatDate(car.published_at ?? null)} />
          <Meta label="Vendue le" value={formatDate(car.sold_at ?? null)} />
        </dl>
      </header>

      <section aria-labelledby="statut" className="flex flex-col gap-3 rounded-xl border p-5">
        <h2 id="statut" className="text-sm font-medium">
          Statut de l&apos;annonce
        </h2>
        <StatusActions car={car} />
      </section>

      <section aria-labelledby="photos" className="flex flex-col gap-4">
        <div className="flex flex-col gap-1">
          <h2 id="photos" className="text-sm font-medium">
            Photos
          </h2>
          <p className="text-muted-foreground text-sm">
            {photos.length === 0
              ? "Une annonce ne peut pas être publiée sans photo principale."
              : `${photos.length} photo${photos.length > 1 ? "s" : ""} — la première est celle que voient les acheteurs dans le catalogue.`}
          </p>
        </div>

        <MediaUploader carId={car.id!} hasMainPhoto={photos.some((p) => p.role?.value === "main")} />
        <MediaGrid carId={car.id!} media={media} carStatus={car.status?.value} />
      </section>

      <section aria-labelledby="infos" className="flex flex-col gap-4">
        <h2 id="infos" className="text-sm font-medium">
          Informations du véhicule
        </h2>
        <CarForm brands={brands} car={car} />
      </section>
    </div>
  );
}

function Meta({ label, value }: { label: string; value: string }) {
  return (
    <div className="flex flex-col">
      <dt className="text-xs">{label}</dt>
      <dd className="text-foreground tabular-nums">{value}</dd>
    </div>
  );
}
