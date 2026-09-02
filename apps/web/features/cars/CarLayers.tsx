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
      className="absolute inset-0 flex items-center justify-center pb-[28svh] sm:pb-[20vh] will-change-transform"
    >
      <span
        className="select-none whitespace-nowrap font-black leading-none tracking-tight text-white/95"
        style={{ fontSize: "clamp(3.5rem, 17vw, 11rem)" }}
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
      className="absolute inset-0 flex items-center justify-center pt-4 will-change-transform sm:pt-0"
    >
      <div className="relative flex h-[31svh] w-[88vw] max-w-3xl items-center justify-center sm:h-[46vh] sm:w-[58vw]">
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
      <div className="absolute left-4 top-24 text-[10px] font-semibold uppercase tracking-[0.25em] text-white/70 sm:left-10 sm:top-28 sm:text-xs sm:tracking-[0.35em]">
        {car.category}
      </div>

      <div className="absolute right-4 top-23 flex items-center gap-2 sm:right-10 sm:top-27">
        <span className="flex h-8 w-8 items-center justify-center rounded-full border border-white/25 text-[11px] font-bold text-white/80">
          {car.brand.slice(0, 1)}
        </span>
        <span className="hidden text-xs font-semibold uppercase tracking-[0.25em] text-white/70 sm:inline">
          {car.brand}
        </span>
      </div>

      {/* Bandeau bas : titre, description, stats, CTA */}
      <div className="absolute inset-x-0 bottom-0 flex flex-col gap-3 border-t border-white/10 bg-gradient-to-b from-transparent via-[#07191E] to-[#07191E] px-4 pb-4 pt-12 sm:flex-row sm:items-end sm:justify-between sm:gap-6 sm:px-10 sm:pb-10 sm:pt-10">
        <div
          className="max-w-sm border-l-2 pl-3 sm:pl-4"
          style={{ borderColor: accent }}
        >
          <h3 className="text-lg font-bold text-white sm:text-xl">
            {car.model}
          </h3>
          <p className="mt-1 hidden line-clamp-2 text-sm leading-5 text-white/60 sm:block sm:leading-6">
            {car.description}
          </p>
        </div>

        <div className="grid grid-cols-3 gap-2 sm:flex sm:items-end sm:gap-10">
          {car.stats.map((s) => (
            <div key={s.label} className="border-l border-white/15 pl-2 sm:pl-3">
              <StatIcon kind={s.icon} className="mb-1 h-3.5 w-3.5 text-white/50 sm:mb-2 sm:h-4 sm:w-4" />
              <p className="text-lg font-black text-white sm:text-2xl">
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
          className="inline-flex items-center justify-center gap-2 self-stretch rounded-full px-5 py-2.5 text-sm font-bold bg-[#CFAC3E] text-black transition duration-300 hover:bg-black hover:text-[#CFAC3E] sm:self-end sm:px-6 sm:py-3"
        >
          Voir les détails
        </Link>
      </div>
    </div>
  );
}
