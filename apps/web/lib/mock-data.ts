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


export const cars: Car[] = [
  {
    slug: "toyota-corolla-2019-01",
    brand: "Toyota",
    model: "Corolla",
    year: 2019,
    priceXaf: 7_850_000,
    mileageKm: 68_500,
    fuel: "Essence",
    transmission: "Automatique",
    color: "Gris métallisé",
    condition: "Occasion",
    status: "available",
    featured: true,
    image: '/ex/1.png',
    description:
      "Une berline fiable, confortable et entretenue avec soin. Idéale pour les trajets quotidiens comme pour les longs déplacements.",
    photos: ['/car.jpeg', '/hero.jpg'],
  },
  {
    slug: "toyota-corolla-2019-02",
    brand: "Toyota",
    model: "Corolla",
    year: 2019,
    priceXaf: 7_850_000,
    mileageKm: 68_500,
    fuel: "Essence",
    transmission: "Automatique",
    color: "Gris métallisé",
    condition: "Occasion",
    status: "available",
    featured: true,
    image: '/ex/2.png',
    description:
      "Une berline fiable, confortable et entretenue avec soin. Idéale pour les trajets quotidiens comme pour les longs déplacements.",
    photos: ['/car.jpeg', '/hero.jpg'],
  },
  {
    slug: "toyota-corolla-2019-03",
    brand: "Toyota",
    model: "Corolla",
    year: 2019,
    priceXaf: 7_850_000,
    mileageKm: 68_500,
    fuel: "Essence",
    transmission: "Automatique",
    color: "Gris métallisé",
    condition: "Occasion",
    status: "available",
    featured: true,
    image: '/ex/3.png',
    description:
      "Une berline fiable, confortable et entretenue avec soin. Idéale pour les trajets quotidiens comme pour les longs déplacements.",
    photos: ['/car.jpeg', '/hero.jpg'],
  },  
  {
    slug: "toyota-corolla-2019-04",
    brand: "Toyota",
    model: "Corolla",
    year: 2019,
    priceXaf: 7_850_000,
    mileageKm: 68_500,
    fuel: "Essence",
    transmission: "Automatique",
    color: "Gris métallisé",    
    condition: "Occasion",
    status: "available",
    featured: true,
    image: '/ex/4.png',
    description:
      "Une berline fiable, confortable et entretenue avec soin. Idéale pour les trajets quotidiens comme pour les longs déplacements.",
    photos: ['/car.jpeg', '/hero.jpg'],
  },
  {
    slug: "mercedes-corolla-2019-01",
    brand: "MERCEDES",
    model: "Corolla",
    year: 2019,
    priceXaf: 7_850_000,
    mileageKm: 68_500,
    fuel: "Essence",
    transmission: "Automatique",  
    color: "Gris métallisé",
    condition: "Occasion",
    status: "available",
    featured: true,
    image: '/ex/5.png',
    description:
      "Une berline fiable, confortable et entretenue avec soin. Idéale pour les trajets quotidiens comme pour les longs déplacements.",
    photos: ['/car.jpeg', '/hero.jpg'],  
  },
  {
    slug: "porsche-corolla-2019-01",
    brand: "PORSCHE",
    model: "Corolla",
    year: 2019,
    priceXaf: 7_850_000,
    mileageKm: 68_500,
    fuel: "Essence",
    transmission: "Automatique",  
    color: "Gris métallisé",
    condition: "Occasion",
    status: "available",
    featured: true,
    image: '/ex/6.png',
    description:
      "Une berline fiable, confortable et entretenue avec soin. Idéale pour les trajets quotidiens comme pour les longs déplacements.",
    photos: ['/car.jpeg', '/hero.jpg'],  
  },
  {
    slug: "peugeot-corolla-2019-01",
    brand: "Peugeot",
    model: "Corolla",
    year: 2019,
    priceXaf: 7_850_000,
    mileageKm: 68_500,
    fuel: "Essence",
    transmission: "Automatique",  
    color: "Gris métallisé",
    condition: "Occasion",
    status: "available",
    featured: true,
    image: '/ex/7.png',
    description:
      "Une berline fiable, confortable et entretenue avec soin. Idéale pour les trajets quotidiens comme pour les longs déplacements.",
    photos: ['/car.jpeg', '/hero.jpg']
  },
  {
    slug: "toyota-corolla-2019-05",
    brand: "Toyota",
    model: "Corolla",
    year: 2019,
    priceXaf: 7_850_000,
    mileageKm: 68_500,
    fuel: "Essence",
    transmission: "Automatique",  
    color: "Gris métallisé",
    condition: "Occasion",
    status: "available",
    featured: true,
    image: '/ex/8.png',
    description:
      "Une berline fiable, confortable et entretenue avec soin. Idéale pour les trajets quotidiens comme pour les longs déplacements.",
    photos: ['/car.jpeg', '/hero.jpg'],  
  },
  {
    slug: "cadillac-corolla-2019-01",
    brand: "Cadillac",
    model: "Corolla",
    year: 2019,
    priceXaf: 7_850_000,
    mileageKm: 68_500,
    fuel: "Essence",
    transmission: "Automatique",  
    color: "Gris métallisé",
    condition: "Occasion",
    status: "available",
    featured: true,
    image: '/ex/9.png',
    description:
      "Une berline fiable, confortable et entretenue avec soin. Idéale pour les trajets quotidiens comme pour les longs déplacements.",
    photos: ['/car.jpeg', '/hero.jpg'],  
  }, 
  {
    slug: "nissan-corolla-2019-01",
    brand: "Nissan",
    model: "Corolla",
    year: 2019,
    priceXaf: 7_850_000,
    mileageKm: 68_500,
    fuel: "Essence",
    transmission: "Automatique",  
    color: "Gris métallisé",
    condition: "Occasion",
    status: "available",
    featured: true,
    image: '/ex/10.png',
    description:
      "Une berline fiable, confortable et entretenue avec soin. Idéale pour les trajets quotidiens comme pour les longs déplacements.",
    photos: ['/car.jpeg', '/hero.jpg'],  
  },
  {
    slug: "mitsubishi-corolla-2019-01",
    brand: "Mitsubishi",
    model: "Corolla",
    year: 2019,
    priceXaf: 7_850_000,
    mileageKm: 68_500,
    fuel: "Essence",
    transmission: "Automatique",  
    color: "Gris métallisé",
    condition: "Occasion",
    status: "available",
    featured: true,
    image: '/ex/11.png',
    description:
      "Une berline fiable, confortable et entretenue avec soin. Idéale pour les trajets quotidiens comme pour les longs déplacements.",
    photos: ['/car.jpeg', '/hero.jpg'],  
  },
  {
    slug: "toyota-jeep-2019-01",
    brand: "Toyota",
    model: "Jeep",
    year: 2019,
    priceXaf: 7_850_000,
    mileageKm: 68_500,
    fuel: "Essence",
    transmission: "Automatique",  
    color: "Gris métallisé",
    condition: "Occasion",
    status: "available",
    featured: true,
    image: '/ex/12.png',
    description:
      "Une berline fiable, confortable et entretenue avec soin. Idéale pour les trajets quotidiens comme pour les longs déplacements.",
    photos: ['/car.jpeg', '/hero.jpg'],  
  },
  {
    slug: "tesla-corolla-2019-01",
    brand: "Tesla",
    model: "Corolla",
    year: 2019,
    priceXaf: 7_850_000,
    mileageKm: 68_500,
    fuel: "Essence",
    transmission: "Automatique",  
    color: "Gris métallisé",
    condition: "Occasion",
    status: "available",
    featured: true,
    image: '/ex/13.png',
    description:
      "Une berline fiable, confortable et entretenue avec soin. Idéale pour les trajets quotidiens comme pour les longs déplacements.",
    photos: ['/car.jpeg', '/hero.jpg'],  
  },
];


export const services: Service[] = [
  {
    slug: "diagnostic",
    title: "Diagnostic électronique",
    excerpt:
      "Une lecture précise pour trouver la cause, pas seulement le symptôme.",
    description:
      "Nous contrôlons les calculateurs et interprétons les alertes pour vous proposer une intervention claire et adaptée.",
    icon: "⌁",
    priceFrom: 15_000,
  },
  {
    slug: "entretien",
    title: "Entretien & révision",
    excerpt: "Préservez la fiabilité de votre véhicule au meilleur rythme.",
    description:
      "Vidange, filtres, freins et contrôles essentiels : chaque révision est détaillée avant intervention.",
    icon: "◌",
    priceFrom: 25_000,
  },
  {
    slug: "climatisation",
    title: "Climatisation",
    excerpt: "Retrouvez un habitacle frais et un système sain.",
    description:
      "Contrôle d'étanchéité, recharge et désinfection pour une climatisation réellement efficace.",
    icon: "✦",
    priceFrom: 20_000,
  },
  {
    slug: "carrosserie",
    title: "Carrosserie & peinture",
    excerpt:
      "Des finitions soignées pour redonner de l'allure à votre voiture.",
    description:
      "Réparation des petits chocs, peinture et remise en état selon l'usage de votre véhicule.",
    icon: "◇",
    priceFrom: null,
  },
];

export const posts: Post[] = [
  {
    slug: "bien-acheter-vehicule-occasion",
    title: "Les 5 réflexes avant d'acheter une occasion",
    excerpt: "Les contrôles simples qui changent tout avant de vous engager.",
    category: "Conseils",
    publishedAt: "2026-08-12",
    image : "/hero.jpg",
    body: [
      "Acheter un véhicule d'occasion est une décision importante. Prenez le temps de vérifier ses documents, son historique d'entretien et l'état général de la carrosserie.",
      "Un essai routier est indispensable. Écoutez le moteur, testez les freins et vérifiez que les équipements fonctionnent comme prévu.",
    ],
  },
  {
    slug: "entretien-saison-pluies",
    title: "Préparer sa voiture pour la saison des pluies",
    excerpt:
      "Pneus, visibilité, freinage : les points à ne pas remettre à demain.",
    category: "Entretien",
    publishedAt: "2026-07-28",
    image : "/hero.jpg",
    body: [
      "La pluie révèle très vite les défauts d'entretien. Commencez par l'état de vos pneus et de vos essuie-glaces.",
      "Une révision des freins et des feux vous permet de rouler avec davantage de sérénité, même par mauvais temps.",
    ],
  },
  {
    slug: "comprendre-tableau-bord",
    title: "Comprendre les voyants de votre tableau de bord",
    excerpt: "Savoir distinguer une alerte à surveiller d'une urgence réelle.",
    category: "Diagnostic",
    publishedAt: "2026-06-18",
    image : "/hero.jpg",
    body: [
      "Un voyant rouge demande généralement un arrêt dès que les conditions de sécurité le permettent. Un voyant orange mérite quant à lui un contrôle rapide.",
      "En cas de doute, un diagnostic évite de transformer un petit incident en panne coûteuse.",
    ],
  },
  {
    slug: "comprendre-tableau-bord",
    title: "Comprendre les voyants de votre tableau de bord",
    excerpt: "Savoir distinguer une alerte à surveiller d'une urgence réelle.",
    category: "Diagnostic",
    publishedAt: "2026-06-18",
    image : "/hero.jpg",
    body: [
      "Un voyant rouge demande généralement un arrêt dès que les conditions de sécurité le permettent. Un voyant orange mérite quant à lui un contrôle rapide.",
      "En cas de doute, un diagnostic évite de transformer un petit incident en panne coûteuse.",
    ],
  },
];

export const getCar = (slug: string) => cars.find((car) => car.slug === slug);
export const getPost = (slug: string) =>
  posts.find((post) => post.slug === slug);
