export interface ServiceItem {
  slug: string;
  title: string;
  summary: string;
  description: string;
  image: string;
  highlights: string[];
}

export const services: ServiceItem[] = [
  {
    slug: 'diagnostic-electronique',
    title: 'Diagnostic électronique',
    summary: "Un diagnostic complet, expliqué clairement, avant toute intervention.",
    description:
      "Avant de toucher à quoi que ce soit, nous passons votre véhicule au diagnostic électronique complet. Codes défaut, capteurs, calculateurs : nous identifions précisément l'origine du problème et vous expliquons ce que nous avons trouvé, en langage clair, avant de proposer une intervention.",
    image: '/services/1.jpg',
    highlights: [
      'Lecture et effacement des codes défaut',
      'Contrôle des capteurs et calculateurs',
      'Rapport détaillé remis avant intervention',
    ],
  },
  {
    slug: 'entretien-courant',
    title: 'Entretien courant',
    summary: 'Vidange, filtres, courroies : le suivi qui prolonge la vie de votre moteur.',
    description:
      "L'entretien régulier reste la meilleure garantie contre les grosses réparations. Nous suivons le carnet constructeur tout en restant transparents sur ce qui est réellement nécessaire, sans vous vendre ce qui ne l'est pas.",
    image: '/services/2.jpg',
    highlights: [
      'Vidange et remplacement des filtres',
      'Contrôle des courroies et niveaux',
      'Carnet d\'entretien numérique à jour',
    ],
  },
  {
    slug: 'reparation-carrosserie',
    title: 'Réparation carrosserie',
    summary: "Retirer les traces d'un accrochage sans compromis sur la finition.",
    description:
      "Bosses, rayures, chocs légers : notre atelier carrosserie redonne à votre véhicule son aspect d'origine. Nous travaillons avec des peintures teintées sur mesure pour un raccord invisible.",
    image: '/services/3.jpg',
    highlights: [
      'Débosselage sans peinture quand c\'est possible',
      'Peinture teintée sur mesure',
      'Devis détaillé avant intervention',
    ],
  },
  {
    slug: 'pneumatiques-freinage',
    title: 'Pneumatiques & freinage',
    summary: 'Deux systèmes qui ne pardonnent pas l\'approximation.',
    description:
      "Le freinage et les pneumatiques conditionnent directement votre sécurité. Nous contrôlons l'usure, la pression, l'équilibrage et remplaçons uniquement ce qui doit l'être, avec des pièces adaptées à votre usage.",
    image: '/services/4.png',
    highlights: [
      'Contrôle et remplacement des plaquettes/disques',
      'Montage et équilibrage pneumatiques',
      'Vérification de la géométrie',
    ],
  },
  {
    slug: 'climatisation',
    title: 'Climatisation',
    summary: "Un contrôle complet avant que la panne ne s'installe.",
    description:
      "Recharge de gaz, contrôle d'étanchéité, nettoyage du circuit : nous entretenons votre climatisation pour qu'elle reste efficace saison après saison, en détectant les fuites avant qu'elles ne deviennent un problème.",
    image: '/services/5.jpg',
    highlights: [
      'Recharge de gaz réfrigérant',
      'Contrôle d\'étanchéité du circuit',
      'Nettoyage et désinfection de l\'habitacle',
    ],
  },
];