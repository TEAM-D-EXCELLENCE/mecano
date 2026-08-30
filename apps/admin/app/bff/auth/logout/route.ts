import { cookies } from "next/headers";

import { cookieName } from "@/lib/api/config";
import { apiFetch } from "@/lib/api/server";

/**
 * Déconnexion — révoque le jeton des deux côtés.
 *
 * Le cookie est purgé même si l'API ne répond pas : laisser un cookie pointant
 * vers un jeton dont on ignore l'état enfermerait le mécanicien dans une session
 * cassée, sans moyen de se reconnecter.
 */
export async function POST(): Promise<Response> {
  const store = await cookies();
  const token = store.get(cookieName())?.value;

  if (token) {
    try {
      await apiFetch("auth/logout", { method: "POST", token });
    } catch {
      // L'API est injoignable : on purge quand même le cookie côté BFF.
    }
  }

  store.delete(cookieName());

  return new Response(null, { status: 204 });
}
