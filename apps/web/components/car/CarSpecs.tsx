import type { Car } from "@/lib/mock-data";
import { formatMileage } from "@/lib/format";

interface CarSpecsProps { car: Car; }

export function CarSpecs({ car }: CarSpecsProps) {
  const specs = [["Année", car.year], ["Kilométrage", formatMileage(car.mileageKm)], ["Carburant", car.fuel], ["Boîte", car.transmission], ["Couleur", car.color], ["État", car.condition]];
  return <dl className="grid grid-cols-2 overflow-hidden rounded-2xl border border-slate-200 sm:grid-cols-3">{specs.map(([label, value]) => <div key={String(label)} className="border-b border-r border-slate-200 p-4 last:border-r-0"><dt className="text-xs font-bold uppercase tracking-wider text-slate-400">{label}</dt><dd className="mt-1 font-bold text-slate-800">{value}</dd></div>)}</dl>;
}
