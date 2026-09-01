import Image from 'next/image';
import Link from 'next/link';
import type { ServiceItem } from './ServicesData';
import { ArrowUpRightIcon } from './Icon';

export default function ServiceCard({ service }: { service: ServiceItem }) {
  return (
    <Link
      href={`/services/${service.slug}`}
      className="group relative isolate flex aspect-[3/4] flex-col justify-end overflow-hidden rounded-3xl bg-slate-900 shadow-sm transition-all duration-500 ease-out hover:-translate-y-2 hover:shadow-xl focus-visible:-translate-y-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400"
    >
      <Image
        src={service.image}
        alt={service.title}
        fill
        sizes="(min-width: 1280px) 20vw, (min-width: 640px) 45vw, 90vw"
        className="object-cover transition-transform duration-700 ease-out will-change-transform group-hover:scale-110"
      />

      {/* Overlay keeps the text legible and darkens slightly on hover. */}
      <div className="absolute inset-0 bg-gradient-to-t from-black/85 via-black/10 to-transparent transition-opacity duration-500 group-hover:opacity-90" />

      {/* Arrow button */}
      <span className="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-slate-900 transition-all duration-500 ease-out group-hover:rotate-45 group-hover:bg-white">
        <ArrowUpRightIcon className="h-4 w-4" />
      </span>

      <h3 className="relative z-10 p-5 text-lg font-bold leading-tight text-white sm:text-xl">
        {service.title}
      </h3>
    </Link>
  );
}
