import Link from "next/link";
import type { Service } from "@/lib/mock-data";
import { formatPriceXaf } from "@/lib/format";

interface ServiceCardProps { service: Service; }

export function ServiceCard({ service }: ServiceCardProps) {
  return <article className="rounded-3xl border border-slate-200 bg-white p-6 transition hover:border-emerald-300 hover:shadow-lg"><span className="grid size-12 place-items-center rounded-2xl bg-emerald-50 text-2xl text-emerald-700" aria-hidden>{service.icon}</span><h3 className="mt-6 text-xl font-extrabold text-slate-950">{service.title}</h3><p className="mt-3 leading-6 text-slate-600">{service.excerpt}</p><div className="mt-6 flex items-center justify-between gap-3"><span className="text-sm font-bold text-slate-950">{service.priceFrom ? `Dès ${formatPriceXaf(service.priceFrom)}` : "Sur devis"}</span><Link href="/contact" className="text-sm font-bold text-emerald-700">Demander →</Link></div></article>;
}
