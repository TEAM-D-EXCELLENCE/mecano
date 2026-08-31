import Image from "next/image";
import Link from "next/link";
import type { CarShowcaseItem } from "./CarsData";
import { StatIcon } from "./icons";

type LayerRef = (el: HTMLDivElement | null) => void;

export function BrandTitle({
  car,
  innerRef,
}: {
  car: CarShowcaseItem;
  innerRef: LayerRef;
}) {
  return (
    <div
      ref={innerRef}
      className="absolute inset-0 flex items-center justify-center will-change-transform"
    >
      <span
        className="select-none whitespace-nowrap font-black leading-none tracking-tight text-white/95"
        style={{ fontSize: "clamp(3rem, 17vw, 11rem)" }}
      >
        {car.brand}
      </span>
    </div>
  );
}

export function CarVisual({
  car,
  innerRef,
}: {
  car: CarShowcaseItem;
  innerRef: LayerRef;
}) {
  return (
    <div
      ref={innerRef}
      className="absolute inset-0 flex items-center justify-center pt-10 will-change-transform sm:pt-0"
    >
      <div className="relative flex h-[34svh] w-[96vw] max-w-3xl items-center justify-center sm:h-[46vh] sm:w-[58vw]">
        {/* Plateau / anneau sous la voiture */}
        <div className="pointer-events-none absolute bottom-[8%] left-1/2 h-[16%] w-[94%] -translate-x-1/2 rounded-[50%] border border-white/15" />
        <Image
          src={car.image}
          alt={car.model}
          fill
          draggable={false}
          className="relative z-10 max-h-full max-w-full object-contain drop-shadow-[0_30px_45px_rgba(0,0,0,0.6)]"
        />
      </div>
    </div>
  );
}

export function CarInfoOverlay({
  car,
  innerRef,
  accent,
}: {
  car: CarShowcaseItem;
  innerRef: LayerRef;
  accent: string;
}) {
  return (
    <div ref={innerRef} className="absolute inset-0 will-change-transform">
      <div className="absolute left-4 top-5 text-[10px] font-semibold uppercase tracking-[0.25em] text-white/70 sm:left-10 sm:top-10 sm:text-xs sm:tracking-[0.35em]">
        {car.category}
      </div>

      <div className="absolute right-4 top-4 flex items-center gap-2 sm:right-10 sm:top-10">
        <span className="flex h-8 w-8 items-center justify-center rounded-full border border-white/25 text-[11px] font-bold text-white/80">
          {car.brand.slice(0, 1)}
        </span>
        <span className="hidden text-xs font-semibold uppercase tracking-[0.25em] text-white/70 sm:inline">
          {car.brand}
        </span>
      </div>

      {/* Bandeau bas : titre, description, stats, CTA */}
      <div className="absolute inset-x-0 bottom-0 flex flex-col gap-4 border-t border-white/10 bg-gradient-to-b from-transparent via-[#07191E] to-transparent px-4 pb-4 pt-16 sm:flex-row sm:items-end sm:justify-between sm:gap-6 sm:px-10 sm:pb-10 sm:pt-10">
        <div
          className="max-w-sm border-l-2 pl-3 sm:pl-4"
          style={{ borderColor: accent }}
        >
          <h3 className="text-lg font-bold text-white sm:text-xl">
            {car.model}
          </h3>
          <p className="mt-1 line-clamp-2 text-sm leading-5 text-white/60 sm:leading-6">
            {car.description}
          </p>
        </div>

        <div className="grid grid-cols-3 gap-3 sm:flex sm:items-end sm:gap-10">
          {car.stats.map((s) => (
            <div key={s.label} className="border-l border-white/15 pl-3">
              <StatIcon kind={s.icon} className="mb-2 h-4 w-4 text-white/50" />
              <p className="text-xl font-black text-white sm:text-2xl">
                {s.value}{" "}
                <span className="text-[11px] font-semibold text-white/50">
                  {s.label}
                </span>
              </p>
            </div>
          ))}
        </div>

        <Link
          href={`/voitures/${car.slug}`}
          className="inline-flex items-center justify-center gap-2 self-stretch rounded-full px-6 py-3 text-sm font-bold text-black transition hover:brightness-95 sm:self-end"
          style={{ backgroundColor: accent }}
        >
          Voir les détails
        </Link>
      </div>
    </div>
  );
}
