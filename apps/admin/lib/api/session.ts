import "server-only";

import { redirect } from "next/navigation";

import { ApiError } from "./errors";
import { apiRequest } from "./server";
import type { Envelope, User } from "./schemas";

/**
 * Garde d'authentification côté serveur.
 *
 * Elle vit dans le layout protégé : aucun contenu du backoffice n'est rendu
 * avant que l'API ait confirmé le jeton. Un `401` purge la session et renvoie
 * vers la connexion (docs/01-architecture/06-securite.md).
 */
export async function requireUser(): Promise<User> {
  try {
    const { data } = await apiRequest<Envelope<User>>("auth/me");
    return data;
  } catch (error) {
    // Seul un 401 est une question d'authentification. Une API injoignable est
    // une panne : elle doit remonter à la frontière d'erreur, pas déguiser le
    // problème en session expirée.
    if (error instanceof ApiError && error.status === 401) {
      redirect("/connexion");
    }
    throw error;
  }
}
