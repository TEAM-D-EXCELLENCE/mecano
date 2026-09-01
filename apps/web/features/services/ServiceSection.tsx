import Link from "next/link";
import { services } from "./ServicesData";
import ServiceCard from "./ServiceCard";
import { ArrowUpRightIcon, WrenchIcon } from "./Icon";

export default function ServicesSection() {
  return (
    <section className="bg-emerald-50">
      <div className="mx-auto max-w-7xl px-5 py-20 sm:px-8">
        <div className="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <span className="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-slate-900 shadow-sm">
              <WrenchIcon className="h-4 w-4" />
              Nos services
            </span>
            <h2 className="mt-5 max-w-lg text-4xl font-black leading-[1.05] tracking-tight text-slate-950 sm:text-5xl">
              Bien entretenir, c&apos;est rouler plus serein.
            </h2>
          </div>

          <div className="max-w-sm lg:text-right">
            <p className="leading-7 text-slate-600">
              Du diagnostic à la carrosserie, nous vous expliquons ce que nous
              faisons et pourquoi nous le faisons.
            </p>
            <Link
              href="/services"
              className="group mt-5 inline-flex items-center gap-3 rounded-full bg-white py-1.5 pl-5 pr-1.5 font-bold text-slate-900 shadow-sm transition hover:bg-slate-950 hover:text-white lg:ml-auto"
            >
              Voir nos services
              <span className="flex h-9 w-9 items-center justify-center rounded-full bg-slate-950 text-white transition group-hover:bg-white group-hover:text-slate-950">
                <ArrowUpRightIcon className="h-4 w-4" />
              </span>
            </Link>
          </div>
        </div>

        <div className="mt-14 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
          {services.map((service) => (
            <ServiceCard key={service.slug} service={service} />
          ))}
        </div>
      </div>
    </section>
  );
}
