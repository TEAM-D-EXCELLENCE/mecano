import "server-only";

/**
 * Configuration serveur du BFF. Ces valeurs ne sont jamais exposées au navigateur :
 * le module est marqué `server-only`, toute importation depuis un composant client
 * échoue à la compilation.
 */

function required(name: string, value: string | undefined): string {
  if (!value) {
    throw new Error(
      `Variable d'environnement manquante : ${name}. Voir .env.example.`,
    );
  }
  return value;
}

/** Racine de l'API Laravel, sans barre oblique finale. Ex. http://localhost:8000/api/v1 */
export const API_BASE_URL = required(
  "API_BASE_URL",
  process.env.API_BASE_URL,
).replace(/\/+$/, "");

/**
 * Nom du cookie de session du BFF. Volontairement opaque (`mc_s`) : il ne doit
 * rien révéler de son contenu (06-securite.md).
 */
export const COOKIE_NAME = process.env.COOKIE_NAME ?? "mc_s";

/** 7 jours — aligné sur l'expiration du jeton Sanctum côté API. */
export const COOKIE_MAX_AGE = 60 * 60 * 24 * 7;

export const COOKIE_OPTIONS = {
  httpOnly: true,
  secure: process.env.NODE_ENV === "production",
  sameSite: "lax",
  path: "/",
  maxAge: COOKIE_MAX_AGE,
} as const;
