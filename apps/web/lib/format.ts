/** Formatters stay centralized so every page presents API values consistently. */
export const formatPriceXaf = (value: number) =>
  `${new Intl.NumberFormat("fr-FR").format(value)} FCFA`;

export const formatMileage = (value: number) =>
  `${new Intl.NumberFormat("fr-FR").format(value)} km`;

export const formatDate = (value: string) =>
  new Intl.DateTimeFormat("fr-FR", {
    day: "numeric",
    month: "long",
    year: "numeric",
  }).format(new Date(value));
