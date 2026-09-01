import { cars as catalogueCars } from "@/lib/mock-data";

export interface CarStat {
  icon: "gauge" | "speed" | "calendar";
  value: string;
  label: string;
}

export interface CarShowcaseItem {
  id: string;
  slug: string;
  category: string;
  brand: string;
  model: string;
  description: string;
  image: string;
  stats: CarStat[];
}

const showcaseMeta = [
  { category: "BERLINE", speed: "190" },
  { category: "SUV", speed: "195" },
  { category: "PICK-UP", speed: "175" },
  { category: "CITADINE", speed: "180" },
];

// Reuse every catalogue entry so each vehicle can be discovered from the home-page showcase.
export const cars: CarShowcaseItem[] = catalogueCars.map(
  (car, index) => ({
    id: `car-${index + 1}`,
    slug: car.slug,
    category: showcaseMeta[index % showcaseMeta.length].category,
    brand: car.brand,
    model: car.model,
    description: car.description,
    image: car.image,
    stats: [
      {
        icon: "gauge",
        value: new Intl.NumberFormat("fr-FR").format(car.mileageKm),
        label: "KM",
      },
      {
        icon: "speed",
        value: showcaseMeta[index % showcaseMeta.length].speed,
        label: "KM/H",
      },
      { icon: "calendar", value: String(car.year), label: "ANNÉE" },
    ],
  }),
);
