import type { Metadata } from "next";
import Link from "next/link";
import { CarCard } from "@/components/car/CarCard";
import { cars } from "@/lib/mock-data";
import Image from "next/image";

export const metadata: Metadata = {
  title: "Véhicules d'occasion",
  description: "Découvrez les véhicules d'occasion disponibles chez Mecano.",
};

export const revalidate = 3600;

interface CarsPageProps {
  searchParams: Promise<{
    marque?: string;
    prix_max?: string;
    inclure_vendus?: string;
  }>;
}

export default async function CarsPage({ searchParams }: CarsPageProps) {
  const filters = await searchParams;

  const validCars = cars.filter(
    (car): car is NonNullable<typeof car> => Boolean(car?.slug)
  );

  const normalizedBrand = filters.marque?.toLowerCase();
  const showSold = filters.inclure_vendus === "1";

  const filteredCars = validCars.filter((car) => {
    const matchesBrand =
      !normalizedBrand || car.brand.toLowerCase() === normalizedBrand;

    const matchesPrice =
      !filters.prix_max || car.priceXaf <= Number(filters.prix_max);

    const matchesStatus = showSold || car.status !== "sold";

    return matchesBrand && matchesPrice && matchesStatus;
  });

  const brands = [...new Set(validCars.map((car) => car.brand))];

  return (
    <>
      <section className="relative overflow-hidden bg-[#07110c] px-5 py-20 text-white">
        {/* Photo de fond : plein bord de la section, pas de la colonne centrée */}
        <Image
          src="/voituresHero.jpg"
          alt="Voitures d'occasion Mecano"
          fill
          priority
          className="absolute inset-0 -z-10 object-cover object-center"
        />
        <div className="absolute inset-0 -z-10 bg-gradient-to-t from-[#07110c] via-[#07110c]/70 to-[#07110c]/30" />

        {/* Contenu : centré, au-dessus de la photo */}
        <div className="relative z-10 mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-end lg:px-8">
          <div className="max-w-3xl">
            <h1 className="mt-5 text-4xl font-black tracking-tight sm:text-6xl">
              Trouvez votre prochaine voiture.
            </h1>

            <p className="mt-5 max-w-2xl text-base leading-7 text-slate-300 sm:text-lg">
              Chaque annonce présente un véhicule contrôlé, avec les
              informations utiles avant votre appel.
            </p>
          </div>

          <div className="rounded-[28px] border border-white/10 bg-white/5 p-5 shadow-[0_24px_80px_rgba(0,0,0,0.35)] backdrop-blur-xl sm:mt-5">
            <div className="flex items-center justify-between border-b border-white/10 pb-4">
              <p className="mt-2 text-sm text-slate-300">
                Affinez votre recherche en quelques clics.
              </p>
            </div>

            <form className="mt-5 grid gap-4">
              <div className="grid gap-4 sm:grid-cols-2">
                <label className="text-sm font-semibold text-slate-200">
                  Marque
                  <select
                    name="marque"
                    defaultValue={filters.marque ?? ""}
                    className="mt-2 w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 font-normal text-white outline-none transition focus:border-emerald-400/40"
                  >
                    <option value="">Toutes les marques</option>
                    {brands.map((brand) => (
                      <option key={brand} value={brand.toLowerCase()}>
                        {brand}
                      </option>
                    ))}
                  </select>
                </label>

                <label className="text-sm font-semibold text-slate-200">
                  Budget maximum
                  <select
                    name="prix_max"
                    defaultValue={filters.prix_max ?? ""}
                    className="mt-2 w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 font-normal text-white outline-none transition focus:border-emerald-400/40"
                  >
                    <option value="">Tous les budgets</option>
                    <option value="8000000">8 000 000 FCFA</option>
                    <option value="12000000">12 000 000 FCFA</option>
                    <option value="15000000">15 000 000 FCFA</option>
                  </select>
                </label>
              </div>

              <label className="flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-200">
                <input
                  type="checkbox"
                  name="inclure_vendus"
                  value="1"
                  defaultChecked={filters.inclure_vendus === "1"}
                  className="h-4 w-4 rounded border-white/20 bg-transparent text-emerald-500 focus:ring-emerald-500"
                />
                Inclure les véhicules vendus
              </label>

              <button className="inline-flex cursor-pointer items-center justify-center rounded-2xl bg-emerald-400 px-6 py-3.5 font-semibold text-[#07110c] transition hover:bg-emerald-300">
                Filtrer le catalogue
              </button>
            </form>
          </div>
        </div>
      </section>

      <main className="mx-auto max-w-7xl px-5 py-12 lg:px-8">
        <div className="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p className="text-sm font-semibold uppercase tracking-[.18em] text-slate-500">
              Résultats
            </p>
            <p className="mt-2 text-lg font-semibold text-slate-900">
              {filteredCars.length} véhicule{filteredCars.length > 1 ? "s" : ""}{" "}
              trouvé{filteredCars.length > 1 ? "s" : ""}
            </p>
          </div>

          <Link
            href="/voitures?inclure_vendus=1"
            className="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-200 hover:text-emerald-700"
          >
            Voir aussi les véhicules vendus →
          </Link>
        </div>

        {filteredCars.length ? (
          <div className="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            {filteredCars.map((car, index) => (
              <div
                key={car.slug}
                className="animate-[fadeUp_.65s_ease-out] [animation-fill-mode:both]"
                style={{ animationDelay: `${index * 80}ms` }}
              >
                <CarCard car={car} />
              </div>
            ))}
          </div>
        ) : (
          <section className="mt-10 overflow-hidden rounded-[32px] border border-slate-200 bg-gradient-to-br from-emerald-50 to-white p-10 text-center shadow-sm">
            <div className="mx-auto max-w-xl">
              <div className="mx-auto mb-5 grid h-16 w-16 place-items-center rounded-full bg-emerald-100 text-2xl">
                🚗
              </div>
              <h2 className="text-2xl font-black text-slate-950">
                Aucun véhicule pour ces critères.
              </h2>
              <p className="mt-3 text-slate-600">
                Élargissez votre recherche ou échangez avec nous pour une
                recherche personnalisée.
              </p>
              <Link
                href="/voitures"
                className="mt-7 inline-flex items-center justify-center rounded-full bg-[#006633] px-6 py-3 font-semibold text-white transition hover:bg-[#005428]"
              >
                Réinitialiser les filtres
              </Link>
            </div>
          </section>
        )}
      </main>
    </>
  );
}