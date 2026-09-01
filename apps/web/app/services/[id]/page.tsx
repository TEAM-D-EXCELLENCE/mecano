import { notFound } from 'next/navigation';
import Image from 'next/image';
import Link from 'next/link';
import { services } from '@/features/services/ServicesData';
import ServiceCard from '@/features/services/ServiceCard';

export function generateStaticParams() {
  return services.map((service) => ({ id: service.slug }));
}

interface ServicePageProps {
  params: Promise<{ id: string }>;
}

export async function generateMetadata({ params }: ServicePageProps) {
  const { id } = await params;
  const service = services.find((s) => s.slug === id);
  if (!service) return {};
  return {
    title: `${service.title} · L'Atelier Mecano`,
    description: service.summary,
  };
}

export default async function ServicePage({ params }: ServicePageProps) {
  const { id } = await params;
  const service = services.find((s) => s.slug === id);
  if (!service) notFound();

  const otherServices = services.filter((s) => s.slug !== service.slug).slice(0, 4);

  return (
    <>
      <section className="relative isolate overflow-hidden bg-slate-950 text-white">
        <div className="absolute inset-0">
          <Image
            src={service.image}
            alt=""
            fill
            priority
            sizes="100vw"
            className="object-cover opacity-40"
          />
          <div className="absolute inset-0 bg-gradient-to-b from-transparent via-slate-950/5 to-slate-950/30" />
        </div>

        <div className="relative mx-auto max-w-5xl px-5 pb-16 pt-28 sm:px-8 sm:pb-20 sm:pt-36">
          <Link
            href="/#services"
            className="inline-flex items-center gap-2 text-sm font-bold text-emerald-300 transition hover:text-emerald-200"
          >
            ← Retour aux services
          </Link>
          <h1 className="mt-6 max-w-2xl text-4xl font-black leading-[1.05] tracking-tight sm:text-6xl">
            {service.title}
          </h1>
          <p className="mt-6 max-w-xl text-lg leading-8 text-slate-300">{service.summary}</p>
        </div>
      </section>

      <section className="bg-white">
        <div className="mx-auto grid max-w-5xl gap-10 px-5 py-16 sm:px-8 lg:grid-cols-[1.1fr_.9fr] lg:gap-14 lg:py-24">
          <div>
            <p className="text-lg leading-8 text-slate-600">{service.description}</p>
            <Link
              href="/contact"
              className="mt-8 inline-flex rounded-full bg-slate-950 px-6 py-3.5 font-bold text-white transition hover:bg-slate-800"
            >
              Demander un devis
            </Link>
          </div>

          <div className="rounded-3xl bg-emerald-50 p-6 sm:p-8">
            <p className="text-sm font-bold uppercase tracking-[.2em] text-emerald-700">
              Ce que ça comprend
            </p>
            <ul className="mt-5 space-y-4">
              {service.highlights.map((h) => (
                <li key={h} className="flex items-start gap-3 leading-6 text-slate-700">
                  <span className="mt-2 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-emerald-500" />
                  {h}
                </li>
              ))}
            </ul>
          </div>
        </div>
      </section>

      {otherServices.length > 0 && (
        <section className="bg-emerald-50">
          <div className="mx-auto max-w-7xl px-5 py-16 sm:px-8 lg:py-20">
            <h2 className="text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
              Autres services
            </h2>
            <div className="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
              {otherServices.map((s) => (
                <ServiceCard key={s.slug} service={s} />
              ))}
            </div>
          </div>
        </section>
      )}
    </>
  );
}
