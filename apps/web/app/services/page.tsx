import type { Metadata } from "next";
import Link from "next/link";
import ServiceCard from "@/features/services/ServiceCard";
import { services } from "@/features/services/ServicesData";

export const metadata: Metadata = {
  title: "Services atelier",
  description: "Les services d'entretien et de réparation proposés par Mecano.",
};

export default function ServicesPage() {
  return (
    <main>
      <section className="bg-slate-950 px-5 py-16 text-white sm:py-20">
        <div className="mx-auto max-w-7xl sm:px-3 lg:px-8">
          <p className="text-sm font-bold uppercase tracking-[.2em] text-emerald-300">
            L&apos;atelier Mecano
          </p>
          <h1 className="mt-4 max-w-3xl text-4xl font-black tracking-tight sm:text-6xl">
            Le bon service, au bon moment.
          </h1>
          <p className="mt-5 max-w-2xl text-lg leading-8 text-slate-300">
            Entretien, diagnostic et réparations : découvrez nos interventions
            et leurs détails.
          </p>
        </div>
      </section>
      <section className="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20">
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
          {services.map((service) => (
            <ServiceCard key={service.slug} service={service} />
          ))}
        </div>
        <div className="mt-14 rounded-3xl bg-emerald-50 p-7 sm:p-10">
          <h2 className="text-2xl font-black text-slate-950">
            Vous ne savez pas quel service choisir ?
          </h2>
          <p className="mt-3 max-w-2xl leading-7 text-slate-600">
            Décrivez-nous votre besoin, nous vous orienterons vers la bonne
            intervention.
          </p>
          <Link
            href="/contact"
            className="mt-6 inline-flex rounded-full bg-[#006633] px-5 py-3 font-bold text-white transition hover:bg-emerald-800"
          >
            Nous contacter
          </Link>
        </div>
      </section>
    </main>
  );
}
