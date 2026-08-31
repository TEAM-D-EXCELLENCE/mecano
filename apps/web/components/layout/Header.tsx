"use client";

import { useState } from "react";
import { useScrolled } from "@/hooks/useScrolled";
import Link from "next/link";

const navigation = [
  { href: "/voitures", label: "Nos véhicules" },
  { href: "/blog", label: "Conseils" },
];

interface HeaderProps {
  isFixed?: boolean;
  backgroundThreshold?: number;
}

export function Header({
  isFixed = false,
  backgroundThreshold = 500,
}: HeaderProps) {
  const [menuOpen, setMenuOpen] = useState(false);
  const scrolled = useScrolled(backgroundThreshold);

  return (
    <header
      className={[
        isFixed ? "fixed inset-x-0 top-2 sm:top-5" : "hidden",
        "z-50 h-16",
      ].join(" ")}
    >
      <div className="relative flex h-full w-full items-center justify-between px-4 sm:px-5 lg:px-8">
        <Link
          href="/"
          className={[
            "flex items-center gap-3 rounded-2xl transition-all duration-500 ease-out",
            scrolled
              ? "border border-white/20 bg-white/80 px-4 py-2 backdrop-blur-xl shadow-lg shadow-slate-950/10 sm:px-7 sm:py-3"
              : "bg-transparent p-0",
          ].join(" ")}
          aria-label="Mecano, accueil"
        >
          <span className="grid size-10 place-items-center rounded-xl bg-[#006633] text-xl font-black text-white">
            M
          </span>
          <span className="text-lg font-extrabold tracking-tight text-slate-950">
            Mecano<span className="text-emerald-700">.</span>
          </span>
        </Link>

        <div className="hidden items-center gap-7 rounded-2xl bg-white/80 px-5 py-3 backdrop-blur-xl md:flex">
          <nav
            className="flex items-center gap-2"
            aria-label="Navigation principale"
          >
            {navigation.map((item) => (
              <Link
                key={item.href}
                href={item.href}
                className="text-sm font-medium text-slate-600 px-2 py-2.5 uppercase rounded-xl transition duration-400 ease-in-out hover:bg-white hover:text-black"
              >
                {item.label}
              </Link>
            ))}
          </nav>

          {/* Ajustement ici : Retrait de w-full / truncate, et ajout de w-36 pour fixer l'espace nécessaire au texte */}
          <a
            className="group relative px-4 py-2 isolate inline-flex h-11 w-36 items-center justify-center overflow-hidden rounded-xl bg-black shadow-sm"
            href="https://wa.me/22954110930"
          >
            <span className="pointer-events-none absolute inset-0 flex items-center justify-center text-[13px] font-semibold tracking-wide text-white">
              NOUS JOINDRE
            </span>

            <span className="pointer-events-none absolute inset-0 flex translate-y-full items-center justify-center bg-white text-[13px] font-semibold tracking-wide text-black transition-transform duration-[200ms] ease-[cubic-bezier(0.65,0,0.35,1)] group-hover:translate-y-0">
              NOUS JOINDRE
            </span>
          </a>
        </div>

        <button
          type="button"
          className="grid size-11 place-items-center rounded-2xl border border-white/30 bg-white/80 text-slate-950 shadow-lg shadow-slate-950/10 backdrop-blur-xl md:hidden"
          aria-label={menuOpen ? "Fermer le menu" : "Ouvrir le menu"}
          aria-controls="mobile-navigation"
          aria-expanded={menuOpen}
          onClick={() => setMenuOpen((open) => !open)}
        >
          <span className="flex w-5 flex-col gap-1.5" aria-hidden="true">
            <span
              className={`h-0.5 w-full rounded-full bg-current transition-transform duration-300 ${menuOpen ? "translate-y-2 rotate-45" : ""}`}
            />
            <span
              className={`h-0.5 w-full rounded-full bg-current transition-opacity duration-300 ${menuOpen ? "opacity-0" : ""}`}
            />
            <span
              className={`h-0.5 w-full rounded-full bg-current transition-transform duration-300 ${menuOpen ? "-translate-y-2 -rotate-45" : ""}`}
            />
          </span>
        </button>

        <div
          id="mobile-navigation"
          className={`absolute right-4 top-[calc(100%+0.75rem)] w-[min(20rem,calc(100vw-2rem))] origin-top-right rounded-3xl border border-white/40 bg-white/95 p-3 shadow-2xl shadow-slate-950/15 backdrop-blur-xl transition duration-300 md:hidden ${menuOpen ? "scale-100 opacity-100" : "pointer-events-none scale-95 opacity-0"}`}
        >
          <nav className="grid gap-1" aria-label="Navigation mobile">
            {navigation.map((item) => (
              <Link
                key={item.href}
                href={item.href}
                onClick={() => setMenuOpen(false)}
                className="rounded-2xl px-4 py-3 text-base font-semibold text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-800"
              >
                {item.label}
              </Link>
            ))}
          </nav>
          <a
            href="https://wa.me/22954110930"
            className="mt-2 flex justify-center rounded-2xl bg-slate-950 px-4 py-3 font-bold text-white transition hover:bg-[#006633]"
          >
            Parler à l&apos;atelier
          </a>
        </div>
      </div>
    </header>
  );
}
