import "server-only";

import { cookies } from "next/headers";

import { apiBaseUrl, cookieName } from "./config";
import { ApiError, toApiError } from "./errors";

/**
 * Next signale certains changements de flux (redirection, page absente, bascule
 * en rendu dynamique) en levant une exception porteuse d'un `digest`.
 *
 * Elles ne sont pas des erreurs applicatives : les intercepter casse le
 * framework de façon silencieuse — une redirection ne redirige plus, un rendu
 * dynamique échoue au build. On les laisse toujours remonter.
 */
function isFrameworkSignal(error: unknown): boolean {
  return (
    typeof error === "object" &&
    error !== null &&
    "digest" in error &&
    typeof (error as { digest: unknown }).digest === "string"
  );
}

/** Jeton Sanctum lu dans le cookie httpOnly. `null` si la session est absente. */
export async function getSessionToken(): Promise<string | null> {
  const store = await cookies();
  return store.get(cookieName())?.value ?? null;
}

interface ApiFetchInit extends Omit<RequestInit, "body"> {
  /** Corps déjà sérialisé, ou objet qui sera converti en JSON. */
  body?: BodyInit | Record<string, unknown> | null;
  /** Jeton explicite — sinon lu dans le cookie. */
  token?: string | null;
  /** Envoie la requête sans en-tête `Authorization` (endpoints publics). */
  anonymous?: boolean;
}

/**
 * Appel serveur vers l'API Laravel.
 *
 * Les réponses du backoffice ne sont jamais mises en cache (`no-store`) : une
 * liste d'annonces périmée dans l'admin ferait croire au mécanicien que son
 * enregistrement a échoué.
 */
export async function apiFetch(path: string, init: ApiFetchInit = {}): Promise<Response> {
  const { body, token, anonymous, headers, ...rest } = init;

  const resolvedToken = anonymous ? null : (token ?? (await getSessionToken()));

  const requestHeaders = new Headers(headers);
  requestHeaders.set("Accept", "application/json");
  if (resolvedToken) {
    requestHeaders.set("Authorization", `Bearer ${resolvedToken}`);
  }

  let requestBody: BodyInit | null = null;
  if (body != null) {
    if (typeof body === "string" || body instanceof URLSearchParams || body instanceof FormData) {
      requestBody = body;
    } else {
      requestBody = JSON.stringify(body);
      requestHeaders.set("Content-Type", "application/json");
    }
  }

  return fetch(`${apiBaseUrl()}/${path.replace(/^\/+/, "")}`, {
    ...rest,
    headers: requestHeaders,
    body: requestBody,
    cache: "no-store",
  });
}

/** Comme `apiFetch`, mais lève une `ApiError` typée et renvoie le JSON décodé. */
export async function apiRequest<T>(path: string, init: ApiFetchInit = {}): Promise<T> {
  let response: Response;

  try {
    response = await apiFetch(path, init);
  } catch (cause) {
    if (isFrameworkSignal(cause)) {
      throw cause;
    }
    throw new ApiError(0, "NETWORK_ERROR", "L'API est injoignable.", {
      cause: cause instanceof Error ? cause.message : String(cause),
    });
  }

  if (!response.ok) {
    throw await toApiError(response);
  }

  if (response.status === 204) {
    return undefined as T;
  }

  return (await response.json()) as T;
}
