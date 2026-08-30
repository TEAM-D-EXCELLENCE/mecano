import "server-only";

/**
 * Configuration serveur du BFF. Ces valeurs ne sont jamais exposées au
 * navigateur : le module est marqué `server-only`, toute importation depuis un
 * composant client échoue à la compilation.
 */

/**
 * Racine de l'API Laravel, sans barre oblique finale.
 *
 * Résolue **à la requête**, jamais au chargement du module : Next évalue les
 * modules pendant le build, et une variable absente à ce moment-là ferait
 * échouer la compilation d'un backoffice qui, lui, n'a besoin de l'API qu'une
 * fois en ligne. Le contrôle reste utile — il donne un message clair au lieu
 * d'un `fetch` vers `undefined` — mais il appartient à l'exécution.
 */
export function apiBaseUrl(): string {
  const value = process.env.API_BASE_URL;

  if (!value) {
    throw new Error(
      "Variable d'environnement manquante : API_BASE_URL. Voir .env.example.",
    );
  }

  return value.replace(/\/+$/, "");
}

/**
 * Nom du cookie de session du BFF. Volontairement opaque (`mc_s`) : il ne doit
 * rien révéler de son contenu (06-securite.md).
 */
export function cookieName(): string {
  return process.env.COOKIE_NAME ?? "mc_s";
}

/** 7 jours — aligné sur l'expiration du jeton Sanctum côté API. */
export const COOKIE_MAX_AGE = 60 * 60 * 24 * 7;

export const COOKIE_OPTIONS = {
  httpOnly: true,
  secure: process.env.NODE_ENV === "production",
  sameSite: "lax",
  path: "/",
  maxAge: COOKIE_MAX_AGE,
} as const;
