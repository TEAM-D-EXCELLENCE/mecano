import { cookies } from "next/headers";

import { COOKIE_NAME, COOKIE_OPTIONS } from "@/lib/api/config";
import { apiFetch } from "@/lib/api/server";

/**
 * Connexion — le seul endroit où le jeton Sanctum touche le BFF.
 *
 * Le jeton est immédiatement rangé dans un cookie httpOnly et **n'est jamais
 * renvoyé au navigateur** : une XSS dans le backoffice ne peut donc pas
 * l'exfiltrer pour s'en servir plus tard.
 */
export async function POST(request: Request): Promise<Response> {
  let payload: unknown;

  try {
    payload = await request.json();
  } catch {
    return Response.json(
      {
        error: {
          code: "VALIDATION_FAILED",
          message: "Requête invalide.",
          details: null,
        },
      },
      { status: 422 },
    );
  }

  let upstream: Response;

  try {
    upstream = await apiFetch("auth/login", {
      method: "POST",
      anonymous: true,
      body: payload as Record<string, unknown>,
    });
  } catch {
    return Response.json(
      {
        error: {
          code: "NETWORK_ERROR",
          message: "L'API est injoignable. Réessayez dans un instant.",
          details: null,
        },
      },
      { status: 502 },
    );
  }

  const body = await upstream.text();

  if (!upstream.ok) {
    // Relais fidèle de l'échec : 401 identifiants, 422 validation, 429 débit.
    return new Response(body === "" ? null : body, {
      status: upstream.status,
      headers: { "Content-Type": "application/json" },
    });
  }

  let parsed: { token?: string; user?: unknown };

  try {
    parsed = JSON.parse(body) as { token?: string; user?: unknown };
  } catch {
    parsed = {};
  }

  if (!parsed.token) {
    return Response.json(
      {
        error: {
          code: "SERVER_ERROR",
          message: "Réponse d'authentification inattendue.",
          details: null,
        },
      },
      { status: 502 },
    );
  }

  const store = await cookies();
  store.set(COOKIE_NAME, parsed.token, COOKIE_OPTIONS);

  // Le jeton est volontairement absent de la réponse.
  return Response.json({ user: parsed.user ?? null }, { status: 200 });
}
