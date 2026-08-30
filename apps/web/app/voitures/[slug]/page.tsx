import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { CarGallery } from "@/components/car/CarGallery";
import { CarSpecs } from "@/components/car/CarSpecs";
import { cars, getCar } from "@/lib/mock-data";
import { formatPriceXaf } from "@/lib/format";

interface CarPageProps { params: Promise<{ slug: string }>; }
export const revalidate = 3600;
export function generateStaticParams() { return cars.map(({ slug }) => ({ slug })); }
export async function generateMetadata({ params }: CarPageProps): Promise<Metadata> { const car = getCar((await params).slug); return car ? { title: `${car.brand} ${car.model} ${car.year}`, description: car.description } : {}; }
export default async function CarPage({ params }: CarPageProps) {
  const car = getCar((await params).slug); if (!car) notFound();
  const isSold = car.status === "sold";
  return <main className="mx-auto max-w-7xl px-5 py-8 lg:px-8"><nav aria-label="Fil d'Ariane" className="text-sm font-semibold text-slate-500"><Link href="/">Accueil</Link> <span aria-hidden> / </span><Link href="/voitures">Véhicules</Link> <span aria-hidden> / </span><span>{car.brand} {car.model}</span></nav><div className="mt-8 grid gap-10 lg:grid-cols-[1.25fr_.75fr]"><CarGallery photos={car.photos} alt={`${car.brand} ${car.model} ${car.year}`} /><section><p className="text-sm font-bold uppercase tracking-[.2em] text-emerald-700">{car.brand} · {car.year}</p><div className="mt-3 flex items-start justify-between gap-4"><h1 className="text-4xl font-black tracking-tight text-slate-950 sm:text-5xl">{car.model}</h1>{car.status !== "available" && <span className="rounded-full bg-slate-950 px-3 py-1 text-sm font-bold text-white">{isSold ? "Vendu" : "Réservé"}</span>}</div><p className="mt-5 text-3xl font-black text-slate-950">{formatPriceXaf(car.priceXaf)}</p><p className="mt-6 leading-7 text-slate-600">{car.description}</p><div className="mt-8"><CarSpecs car={car} /></div>{isSold ? <div className="mt-8 rounded-2xl bg-emerald-50 p-5"><p className="font-extrabold text-slate-950">Ce véhicule a trouvé son nouveau propriétaire.</p><Link href="/voitures" className="mt-2 inline-block font-bold text-emerald-700">Voir des véhicules similaires →</Link></div> : <a href="https://wa.me/237600000000" className="mt-8 flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-6 py-4 font-extrabold text-white transition hover:bg-emerald-700">Je suis intéressé(e) · WhatsApp <span className="ml-2" aria-hidden>↗</span></a>}<p className="mt-3 text-center text-xs text-slate-500">Un conseiller vous répondra rapidement, sans engagement.</p></section></div><section className="mt-20 rounded-3xl bg-slate-950 p-8 text-white sm:p-12"><p className="text-sm font-bold uppercase tracking-[.2em] text-emerald-300">Besoin d&apos;un avis ?</p><h2 className="mt-3 text-3xl font-black tracking-tight">Nous pouvons vous guider avant votre décision.</h2><p className="mt-4 max-w-2xl text-slate-300">Posez vos questions sur ce véhicule, son historique ou les démarches. Une réponse claire vaut mieux qu&apos;une promesse vague.</p><Link href="/contact" className="mt-6 inline-block font-bold text-emerald-300">Nous contacter →</Link></section></main>;
}
