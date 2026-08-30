import Image from "next/image";
import Link from "next/link";
import type { Car } from "@/lib/mock-data";
import { formatMileage, formatPriceXaf } from "@/lib/format";

interface CarCardProps { car: Car; }

/** Reusable catalogue card; status is data supplied by the API, never inferred here. */
export function CarCard({ car }: CarCardProps) {
  const statusLabel = car.status === "sold" ? "Vendu" : car.status === "reserved" ? "Réservé" : null;
  return (
    <article className="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
      <Link href={`/voitures/${car.slug}`} className="block">
        <div className="relative aspect-[4/3] overflow-hidden bg-slate-100">
          <Image src={car.image} alt={`${car.brand} ${car.model} ${car.year}`} fill sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw" className="object-cover transition duration-500 group-hover:scale-105" />
          {statusLabel && <span className="absolute left-4 top-4 rounded-full bg-slate-950 px-3 py-1 text-xs font-bold text-white">{statusLabel}</span>}
          {car.featured && <span className="absolute right-4 top-4 rounded-full bg-amber-300 px-3 py-1 text-xs font-bold text-slate-950">Sélection Mecano</span>}
        </div>
        <div className="p-5">
          <p className="text-sm font-semibold text-emerald-700">{car.brand} · {car.year}</p>
          <h3 className="mt-1 text-xl font-extrabold tracking-tight text-slate-950">{car.model}</h3>
          <div className="mt-4 flex flex-wrap gap-x-3 gap-y-1 text-sm text-slate-500"><span>{formatMileage(car.mileageKm)}</span><span>·</span><span>{car.transmission}</span><span>·</span><span>{car.fuel}</span></div>
          <p className="mt-5 text-lg font-extrabold text-slate-950">{formatPriceXaf(car.priceXaf)}</p>
        </div>
      </Link>
    </article>
  );
}
