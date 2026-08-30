/**
 * Temporary presentation data. When the public API is ready, this module is the
 * single replacement point for typed API calls; pages and components stay unchanged.
 */
export interface Car {
  slug: string;
  brand: string;
  model: string;
  year: number;
  priceXaf: number;
  mileageKm: number;
  fuel: string;
  transmission: string;
  color: string;
  condition: string;
  status: "available" | "reserved" | "sold";
  featured?: boolean;
  image: string;
  description: string;
  photos: string[];
}

export interface Service {
  slug: string;
  title: string;
  excerpt: string;
  description: string;
  icon: string;
  priceFrom: number | null;
}

export interface Post {
  slug: string;
  title: string;
  excerpt: string;
  body: string[];
  category: string;
  publishedAt: string;
  image: string;
}

const placeholder = (label: string, color: string) =>
  `https://placehold.co/1200x800/${color}/ffffff?text=${encodeURIComponent(label)}`;

export const cars: Car[] = [
  {
    slug: "toyota-corolla-2019-01", brand: "Toyota", model: "Corolla", year: 2019,
    priceXaf: 7_850_000, mileageKm: 68_500, fuel: "Essence", transmission: "Automatique",
    color: "Gris métallisé", condition: "Occasion", status: "available", featured: true,
    image: placeholder("Toyota Corolla 2019", "183b5b"),
    description: "Une berline fiable, confortable et entretenue avec soin. Idéale pour les trajets quotidiens comme pour les longs déplacements.",
    photos: [placeholder("Corolla - avant", "183b5b"), placeholder("Corolla - intérieur", "264f73"), placeholder("Corolla - profil", "0d2942")],
  },
  {
    slug: "hyundai-tucson-2020-02", brand: "Hyundai", model: "Tucson", year: 2020,
    priceXaf: 13_900_000, mileageKm: 44_000, fuel: "Essence", transmission: "Automatique",
    color: "Blanc", condition: "Occasion", status: "available", featured: true,
    image: placeholder("Hyundai Tucson 2020", "69543a"),
    description: "SUV spacieux et élégant, prêt à partir. Révision complète effectuée avant sa mise en vente.",
    photos: [placeholder("Tucson - avant", "69543a"), placeholder("Tucson - habitacle", "8b6d4f"), placeholder("Tucson - coffre", "4f3d2d")],
  },
  {
    slug: "mitsubishi-l200-2018-03", brand: "Mitsubishi", model: "L200", year: 2018,
    priceXaf: 11_500_000, mileageKm: 92_000, fuel: "Diesel", transmission: "Manuelle",
    color: "Noir", condition: "Occasion", status: "reserved",
    image: placeholder("Mitsubishi L200 2018", "3d4b3d"),
    description: "Pick-up robuste, adapté aux usages professionnels et aux aventures du week-end.",
    photos: [placeholder("L200 - avant", "3d4b3d"), placeholder("L200 - cabine", "566750"), placeholder("L200 - benne", "253126")],
  },
  {
    slug: "kia-rio-2017-04", brand: "Kia", model: "Rio", year: 2017,
    priceXaf: 5_900_000, mileageKm: 81_000, fuel: "Essence", transmission: "Manuelle",
    color: "Rouge", condition: "Occasion", status: "sold",
    image: placeholder("Kia Rio 2017", "7b3030"),
    description: "Citadine maniable et économique, vendue après un entretien rigoureux.",
    photos: [placeholder("Rio - avant", "7b3030"), placeholder("Rio - intérieur", "9b4949"), placeholder("Rio - profil", "4c2020")],
  },
  {
    slug: "nissan-qashqai-2019-05", brand: "Nissan", model: "Qashqai", year: 2019,
    priceXaf: 12_400_000, mileageKm: 57_000, fuel: "Diesel", transmission: "Automatique",
    color: "Bleu nuit", condition: "Occasion", status: "available",
    image: placeholder("Nissan Qashqai 2019", "243a55"),
    description: "Un SUV urbain haut de gamme, silencieux et particulièrement bien équipé.",
    photos: [placeholder("Qashqai - avant", "243a55"), placeholder("Qashqai - cockpit", "365573"), placeholder("Qashqai - arrière", "17263a")],
  },
  {
    slug: "toyota-hilux-2016-06", brand: "Toyota", model: "Hilux", year: 2016,
    priceXaf: 14_200_000, mileageKm: 118_000, fuel: "Diesel", transmission: "Manuelle",
    color: "Argent", condition: "Occasion", status: "available",
    image: placeholder("Toyota Hilux 2016", "5b5d61"),
    description: "Un véhicule de travail reconnu pour sa fiabilité, avec dossier d'entretien suivi.",
    photos: [placeholder("Hilux - avant", "5b5d61"), placeholder("Hilux - cabine", "777a80"), placeholder("Hilux - benne", "404247")],
  },
];

export const services: Service[] = [
  { slug: "diagnostic", title: "Diagnostic électronique", excerpt: "Une lecture précise pour trouver la cause, pas seulement le symptôme.", description: "Nous contrôlons les calculateurs et interprétons les alertes pour vous proposer une intervention claire et adaptée.", icon: "⌁", priceFrom: 15_000 },
  { slug: "entretien", title: "Entretien & révision", excerpt: "Préservez la fiabilité de votre véhicule au meilleur rythme.", description: "Vidange, filtres, freins et contrôles essentiels : chaque révision est détaillée avant intervention.", icon: "◌", priceFrom: 25_000 },
  { slug: "climatisation", title: "Climatisation", excerpt: "Retrouvez un habitacle frais et un système sain.", description: "Contrôle d'étanchéité, recharge et désinfection pour une climatisation réellement efficace.", icon: "✦", priceFrom: 20_000 },
  { slug: "carrosserie", title: "Carrosserie & peinture", excerpt: "Des finitions soignées pour redonner de l'allure à votre voiture.", description: "Réparation des petits chocs, peinture et remise en état selon l'usage de votre véhicule.", icon: "◇", priceFrom: null },
];

export const posts: Post[] = [
  { slug: "bien-acheter-vehicule-occasion", title: "Les 5 réflexes avant d'acheter une occasion", excerpt: "Les contrôles simples qui changent tout avant de vous engager.", category: "Conseils", publishedAt: "2026-08-12", image: placeholder("Conseils achat occasion", "304c43"), body: ["Acheter un véhicule d'occasion est une décision importante. Prenez le temps de vérifier ses documents, son historique d'entretien et l'état général de la carrosserie.", "Un essai routier est indispensable. Écoutez le moteur, testez les freins et vérifiez que les équipements fonctionnent comme prévu."] },
  { slug: "entretien-saison-pluies", title: "Préparer sa voiture pour la saison des pluies", excerpt: "Pneus, visibilité, freinage : les points à ne pas remettre à demain.", category: "Entretien", publishedAt: "2026-07-28", image: placeholder("Préparer la saison des pluies", "3a4c69"), body: ["La pluie révèle très vite les défauts d'entretien. Commencez par l'état de vos pneus et de vos essuie-glaces.", "Une révision des freins et des feux vous permet de rouler avec davantage de sérénité, même par mauvais temps."] },
  { slug: "comprendre-tableau-bord", title: "Comprendre les voyants de votre tableau de bord", excerpt: "Savoir distinguer une alerte à surveiller d'une urgence réelle.", category: "Diagnostic", publishedAt: "2026-06-18", image: placeholder("Voyants tableau de bord", "6a4c38"), body: ["Un voyant rouge demande généralement un arrêt dès que les conditions de sécurité le permettent. Un voyant orange mérite quant à lui un contrôle rapide.", "En cas de doute, un diagnostic évite de transformer un petit incident en panne coûteuse."] },
];

export const getCar = (slug: string) => cars.find((car) => car.slug === slug);
export const getPost = (slug: string) => posts.find((post) => post.slug === slug);
