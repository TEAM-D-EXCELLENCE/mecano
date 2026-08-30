/**
 * Enveloppe d'erreur de l'API — format unique documenté dans docs/03-api/README.md.
 *
 * Règle du contrat FE/BE : le front compare sur `code`, jamais sur `message`.
 * Les messages peuvent changer sans préavis, les codes non.
 */
export interface ApiErrorBody {
  error: {
    code: string;
    message: string;
    details: unknown;
  };
}

export class ApiError extends Error {
  readonly status: number;
  readonly code: string;
  readonly details: unknown;

  constructor(status: number, code: string, message: string, details: unknown = null) {
    super(message);
    this.name = "ApiError";
    this.status = status;
    this.code = code;
    this.details = details;
  }
}

/** Erreurs de validation `422`, indexées par nom de champ. */
export type FieldErrors = Record<string, string[]>;

export function isFieldErrors(details: unknown): details is FieldErrors {
  if (typeof details !== "object" || details === null || Array.isArray(details)) {
    return false;
  }
  return Object.values(details).every(
    (v) => Array.isArray(v) && v.every((item) => typeof item === "string"),
  );
}

/**
 * Construit une `ApiError` à partir d'une réponse HTTP en échec.
 *
 * Tolère un corps qui ne respecte pas l'enveloppe : une passerelle ou un proxy
 * peut renvoyer du HTML ou du texte brut, et le front ne doit pas planter pour ça.
 */
export async function toApiError(response: Response): Promise<ApiError> {
  let code = fallbackCodeFor(response.status);
  let message = fallbackMessageFor(response.status);
  let details: unknown = null;

  try {
    const body = (await response.json()) as Partial<ApiErrorBody>;
    if (body?.error?.code) {
      code = body.error.code;
      message = body.error.message || message;
      details = body.error.details ?? null;
    }
  } catch {
    // Corps illisible : on garde les valeurs de repli déduites du statut.
  }

  return new ApiError(response.status, code, message, details);
}

function fallbackCodeFor(status: number): string {
  switch (status) {
    case 401:
      return "UNAUTHENTICATED";
    case 403:
      return "FORBIDDEN";
    case 404:
      return "NOT_FOUND";
    case 405:
      return "METHOD_NOT_ALLOWED";
    case 422:
      return "VALIDATION_FAILED";
    case 429:
      return "RATE_LIMITED";
    default:
      return "SERVER_ERROR";
  }
}

function fallbackMessageFor(status: number): string {
  switch (status) {
    case 401:
      return "Votre session a expiré.";
    case 403:
      return "Action non autorisée.";
    case 404:
      return "Ressource introuvable.";
    case 422:
      return "Certains champs sont invalides.";
    case 429:
      return "Trop de tentatives, réessayez dans un instant.";
    default:
      return "Une erreur est survenue. Réessayez.";
  }
}

/**
 * Message affichable au mécanicien, dérivé du code d'erreur.
 *
 * Les états couverts sont ceux listés comme obligatoires dans
 * docs/02-conventions/contrat-frontend-backend.md.
 */
export function messageForError(error: unknown): string {
  if (!(error instanceof ApiError)) {
    return "Impossible de joindre le serveur. Vérifiez votre connexion et réessayez.";
  }

  switch (error.code) {
    case "INVALID_CREDENTIALS":
      return "Adresse e-mail ou mot de passe incorrect.";
    case "QUOTA_EXCEEDED": {
      const d = error.details as { used?: number; limit?: number } | null;
      return d?.limit
        ? `Quota mensuel atteint (${d.used ?? d.limit}/${d.limit}), disponible le 1er du mois.`
        : "Quota mensuel atteint, disponible le 1er du mois.";
    }
    case "CAR_NOT_PUBLISHABLE":
      return "Ajoutez une photo principale avant de publier cette annonce.";
    case "INVALID_STATUS_TRANSITION":
      return "Ce changement de statut n'est pas autorisé.";
    case "VIDEO_LIMIT_EXCEEDED":
      return "Cette annonce a déjà ses deux vidéos (intérieur et extérieur).";
    case "MEDIA_NOT_FOUND_IN_STORAGE":
      return "Le fichier n'a pas été trouvé chez l'hébergeur. Relancez l'envoi.";
    case "VALIDATION_FAILED":
      return "Certains champs sont invalides. Corrigez-les et réessayez.";
    case "RATE_LIMITED":
      return "Trop de tentatives, réessayez dans un instant.";
    default:
      return error.message || "Une erreur est survenue. Réessayez.";
  }
}
