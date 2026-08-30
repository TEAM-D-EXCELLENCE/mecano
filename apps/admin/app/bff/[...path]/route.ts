import { cookies } from "next/headers";

import { API_BASE_URL, COOKIE_NAME } from "@/lib/api/config";

/**
 * BFF — relais authentifié vers l'API Laravel.
 *
 * Le navigateur ne parle qu'à cette route ; le jeton Sanctum vit dans un cookie
 * httpOnly que le JavaScript ne peut pas lire (docs/01-architecture/06-securite.md).
 *
 * Le relais est **fidèle** : même code de statut, même corps d'erreur. Il ne
 * réinterprète rien, sinon le débogage devient impossible côté client.
 */

/** Méthodes relayées. `PUT` est inclus pour rester ouvert si le contrat évolue. */
type Relayed = "GET" | "POST" | "PATCH" | "PUT" | "DELETE";

const JSON_HEADERS = { "Content-Type": "application/json" } as const;

function unauthenticated(): Response {
  return Response.json(
    {
      error: {
        code: "UNAUTHENTICATED",
        message: "Non authentifié ou jeton invalide.",
        details: null,
      },
    },
    { status: 401, headers: JSON_HEADERS },
  );
}

/**
 * Empêche un segment de sortir de la racine de l'API.
 *
 * Sans cette vérification, un chemin comme `/bff/../../autre-hote` ferait porter
 * le jeton d'administration à une requête que l'appelant contrôle entièrement.
 */
function safePath(segments: string[]): string | null {
  if (segments.length === 0) return null;

  for (const segment of segments) {
    if (segment === "" || segment === "." || segment === ".." || segment.includes("/")) {
      return null;
    }
  }

  return segments.map(encodeURIComponent).join("/");
}

async function relay(
  request: Request,
  context: { params: Promise<{ path: string[] }> },
  method: Relayed,
): Promise<Response> {
  const store = await cookies();
  const token = store.get(COOKIE_NAME)?.value;

  if (!token) {
    return unauthenticated();
  }

  const { path } = await context.params;
  const target = safePath(path);

  if (target === null) {
    return Response.json(
      { error: { code: "NOT_FOUND", message: "Ressource introuvable.", details: null } },
      { status: 404, headers: JSON_HEADERS },
    );
  }

  const search = new URL(request.url).search;

  const headers = new Headers({
    Authorization: `Bearer ${token}`,
    Accept: "application/json",
  });

  const contentType = request.headers.get("content-type");
  if (contentType) {
    headers.set("Content-Type", contentType);
  }

  let upstream: Response;

  try {
    upstream = await fetch(`${API_BASE_URL}/${target}${search}`, {
      method,
      headers,
      body: method === "GET" ? undefined : await request.text(),
      cache: "no-store",
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
      { status: 502, headers: JSON_HEADERS },
    );
  }

  // Jeton expiré ou révoqué : on purge le cookie pour que la prochaine navigation
  // reparte proprement sur la page de connexion.
  if (upstream.status === 401) {
    store.delete(COOKIE_NAME);
  }

  const body = await upstream.text();

  return new Response(body === "" ? null : body, {
    status: upstream.status,
    headers: {
      "Content-Type": upstream.headers.get("content-type") ?? "application/json",
    },
  });
}

type Context = { params: Promise<{ path: string[] }> };

export const GET = (request: Request, context: Context) => relay(request, context, "GET");
export const POST = (request: Request, context: Context) => relay(request, context, "POST");
export const PATCH = (request: Request, context: Context) => relay(request, context, "PATCH");
export const PUT = (request: Request, context: Context) => relay(request, context, "PUT");
export const DELETE = (request: Request, context: Context) => relay(request, context, "DELETE");
