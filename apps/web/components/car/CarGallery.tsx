"use client";

import Image from "next/image";
import { useState } from "react";

interface CarGalleryProps { photos: string[]; alt: string; }

/** Deliberately isolated client leaf: only image selection needs browser state. */
export function CarGallery({ photos, alt }: CarGalleryProps) {
  const [active, setActive] = useState(0);
  return (
    <section aria-label="Galerie photos du véhicule">
      <div className="relative aspect-[4/3] overflow-hidden rounded-3xl bg-slate-200 sm:aspect-[16/10]">
        <Image src={photos[active]} alt={`${alt} — photo ${active + 1}`} fill priority sizes="(max-width: 1024px) 100vw, 60vw" className="object-cover" />
      </div>
      <div className="mt-3 flex gap-3 overflow-x-auto pb-1">{photos.map((photo, index) => <button key={photo} type="button" onClick={() => setActive(index)} aria-label={`Voir la photo ${index + 1}`} aria-pressed={active === index} className={`relative size-20 shrink-0 overflow-hidden rounded-xl border-2 ${active === index ? "border-emerald-600" : "border-transparent"}`}><Image src={photo} alt="" fill sizes="80px" className="object-cover" /></button>)}</div>
    </section>
  );
}
