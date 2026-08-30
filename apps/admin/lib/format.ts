/**
 * Formatage d'affichage — le seul endroit du front qui met en forme des données.
 *
 * Le contrat FE/BE est explicite : l'API renvoie des entiers et des dates ISO 8601,
 * le front décide de leur présentation. Aucune de ces fonctions ne contient de
 * règle métier ; elles ne font que traduire une valeur brute en texte lisible.
 */

const nf = new Intl.NumberFormat("fr-FR");

/** 4500000 → « 4 500 000 FCFA » */
export const formatPriceXaf = (value: number): string => `${nf.format(value)} FCFA`;

/** 85000 → « 85 000 km » */
export const formatMileage = (km: number): string => `${nf.format(km)} km`;

/** 2018 → « 2018 » (entier, jamais séparé par un espace de milliers) */
export const formatYear = (year: number): string => String(year);

/** "2026-08-25T14:30:00Z" → « 25 août 2026 » */
export const formatDate = (iso: string | null): string => {
  if (!iso) return "—";
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) return "—";
  return new Intl.DateTimeFormat("fr-FR", {
    day: "numeric",
    month: "long",
    year: "numeric",
  }).format(date);
};

/** "2026-08-25T14:30:00Z" → « 25 août 2026 à 14:30 » */
export const formatDateTime = (iso: string | null): string => {
  if (!iso) return "—";
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) return "—";
  return new Intl.DateTimeFormat("fr-FR", {
    day: "numeric",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  }).format(date);
};

/** 15728640 → « 15 Mo » */
export const formatBytes = (bytes: number): string => {
  if (bytes < 1024) return `${bytes} o`;
  if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} Ko`;
  return `${nf.format(Math.round((bytes / (1024 * 1024)) * 10) / 10)} Mo`;
};
