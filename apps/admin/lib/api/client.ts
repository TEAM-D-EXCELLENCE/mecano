"use client";

import { ApiError, toApiError } from "./errors";

/**
 * Client navigateur — ne parle **qu'au BFF**, jamais directement à l'API.
 *
 * C'est la règle 2 des conventions frontend : le jeton vit dans un cookie
 * httpOnly que ce code ne peut pas lire, et il n'a pas à le connaître.
 */

type Json = Record<string, unknown> | unknown[];

interface BffInit {
  method?: "GET" | "POST" | "PATCH" | "PUT" | "DELETE";
  body?: Json;
  signal?: AbortSignal;
}

/**
 * @param path chemin d'API sans préfixe, ex. `admin/cars` ou `auth/me`
 */
export async function bff<T>(path: string, init: BffInit = {}): Promise<T> {
  const { method = "GET", body, signal } = init;

  let response: Response;

  try {
    response = await fetch(`/bff/${path.replace(/^\/+/, "")}`, {
      method,
      headers: body === undefined ? undefined : { "Content-Type": "application/json" },
      body: body === undefined ? undefined : JSON.stringify(body),
      signal,
    });
  } catch (cause) {
    if (cause instanceof DOMException && cause.name === "AbortError") {
      throw cause;
    }
    throw new ApiError(0, "NETWORK_ERROR", "L'API est injoignable.", null);
  }

  if (!response.ok) {
    throw await toApiError(response);
  }

  if (response.status === 204) {
    return undefined as T;
  }

  return (await response.json()) as T;
}

/** Connexion. Le jeton reste côté serveur ; on ne récupère que l'utilisateur. */
export async function login(email: string, password: string): Promise<void> {
  await bff<{ user: unknown }>("auth/login", {
    method: "POST",
    body: { email, password },
  });
}

/** Déconnexion : révoque le jeton côté API et purge le cookie. */
export async function logout(): Promise<void> {
  await fetch("/bff/auth/logout", { method: "POST" });
}
