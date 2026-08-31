import Link from "next/link";

export function Footer() {
  return (
    <footer className="mt-auto overflow-hidden bg-[#101010] text-white">
      <div className="mx-auto max-w-[1440px] px-6 py-12 sm:px-10 lg:py-14">
        {/* Partie supérieure */}
        <div className="grid gap-12 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
          {/* Marque et coordonnées */}
          <div>
            <Link href="/" className="text-2xl font-medium tracking-[-0.04em]">
              Mecano<span className="text-emerald-400">.</span>
            </Link>

            <div className="mt-10 space-y-5 text-sm leading-6 text-white/75">
              <p>
                Véhicules d’occasion sélectionnés
                <br />
                et atelier de confiance.
              </p>

              <p>
                Bonabéri, Douala
                <br />
                Cameroun
              </p>

              <a
                href="tel:+237600000000"
                className="block transition-colors hover:text-emerald-400"
              >
                +237 600 000 000
              </a>
            </div>

            {/* Réseaux sociaux */}
            <div className="mt-8 flex gap-3">
              <a
                href="#"
                aria-label="Instagram"
                className="flex h-10 w-10 items-center justify-center rounded-full border border-white/40 text-sm transition-all hover:border-emerald-400 hover:text-emerald-400"
              >
                ig
              </a>

              <a
                href="#"
                aria-label="Facebook"
                className="flex h-10 w-10 items-center justify-center rounded-full border border-white/40 text-sm transition-all hover:border-emerald-400 hover:text-emerald-400"
              >
                f
              </a>

              <a
                href="#"
                aria-label="WhatsApp"
                className="flex h-10 w-10 items-center justify-center rounded-full border border-white/40 text-sm transition-all hover:border-emerald-400 hover:text-emerald-400"
              >
                wa
              </a>
            </div>
          </div>

          {/* Navigation */}
          <div>
            <h2 className="text-sm font-medium uppercase tracking-wide text-white">
              Explorer
            </h2>

            <nav className="mt-6 flex flex-col gap-4 text-sm text-white/65">
              <Link
                className="transition-colors hover:text-white"
                href="/voitures"
              >
                Véhicules
              </Link>
              <Link
                className="transition-colors hover:text-white"
                href="/services"
              >
                Services atelier
              </Link>
              <Link className="transition-colors hover:text-white" href="/blog">
                Nos conseils
              </Link>
              <Link
                className="transition-colors hover:text-white"
                href="/a-propos"
              >
                À propos
              </Link>
            </nav>
          </div>

          {/* Informations */}
          <div>
            <h2 className="text-sm font-medium uppercase tracking-wide text-white">
              Nous trouver
            </h2>

            <div className="mt-6 space-y-4 text-sm leading-6 text-white/65">
              <p>
                Lundi — Samedi
                <br />
                8h00 — 18h00
              </p>

              <p>
                Bonabéri
                <br />
                Douala, Cameroun
              </p>
            </div>
          </div>

          {/* Contact */}
          <div>
            <h2 className="text-sm font-medium uppercase tracking-wide text-white">
              Un projet ?
            </h2>

            <p className="mt-6 max-w-xs text-sm leading-6 text-white/65">
              Besoin d’un véhicule ou d’un entretien ? Notre équipe est là pour
              vous accompagner.
            </p>
          </div>
        </div>

        {/* Séparateur et liens secondaires */}
        <div className="relative mt-16 border-t border-white/30 pt-8">
          <Link
            href="https://wa.me/237600000000"
            className="absolute right-0 top-0 -translate-y-1/2 rounded-full bg-white px-6 py-3 text-sm font-medium text-black transition-colors hover:bg-[#006633]"
          >
            Nous joindre
          </Link>

          <div className="flex flex-col gap-5 text-xs text-white/55 sm:flex-row sm:items-center sm:justify-between">
            <p>© {new Date().getFullYear()} Mecano. Tous droits réservés.</p>

            <div className="flex gap-6">
              <Link
                href="/mentions-legales"
                className="transition-colors hover:text-white"
              >
                Mentions légales
              </Link>

              <Link
                href="/confidentialite"
                className="transition-colors hover:text-white"
              >
                Confidentialité
              </Link>
            </div>
          </div>
        </div>

        {/* Grande signature visuelle */}
        <div className="mt-12 select-none whitespace-nowrap text-[clamp(5rem,17vw,15rem)] font-black leading-[0.7] tracking-[-0.09em] text-white/[0.28]">
          mecano.
        </div>
      </div>
    </footer>
  );
}
