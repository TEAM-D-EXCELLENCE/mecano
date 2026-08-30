"use client";

import { useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { AlertCircle, Loader2 } from "lucide-react";

import { Alert, AlertDescription } from "@/components/ui/alert";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { login } from "@/lib/api/client";
import { ApiError, isFieldErrors, messageForError } from "@/lib/api/errors";

/**
 * La validation côté client double celle de l'API, elle ne la remplace pas.
 * Elle évite un aller-retour réseau sur une faute évidente ; l'API reste la
 * seule autorité (docs/02-conventions/frontend.md).
 */
const loginSchema = z.object({
  email: z
    .string()
    .min(1, "Renseignez votre adresse e-mail.")
    .pipe(z.email("Cette adresse e-mail n'est pas valide.")),
  password: z.string().min(1, "Renseignez votre mot de passe."),
});

type LoginValues = z.infer<typeof loginSchema>;

export function LoginForm() {
  const router = useRouter();

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<LoginValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: { email: "", password: "" },
  });

  const onSubmit = handleSubmit(async (values) => {
    try {
      await login(values.email, values.password);
      router.replace("/tableau-de-bord");
      router.refresh();
    } catch (error) {
      // 422 : l'API renvoie les erreurs champ par champ, on les replace sous
      // le champ concerné plutôt que dans un bandeau générique.
      if (error instanceof ApiError && error.status === 422 && isFieldErrors(error.details)) {
        for (const [field, messages] of Object.entries(error.details)) {
          if (field === "email" || field === "password") {
            setError(field, { message: messages[0] });
          }
        }
        return;
      }

      setError("root", { message: messageForError(error) });
    }
  });

  return (
    <form onSubmit={onSubmit} noValidate className="flex flex-col gap-5">
      {errors.root ? (
        <Alert variant="destructive">
          <AlertCircle aria-hidden="true" />
          <AlertDescription>{errors.root.message}</AlertDescription>
        </Alert>
      ) : null}

      <div className="flex flex-col gap-2">
        <Label htmlFor="email">Adresse e-mail</Label>
        <Input
          id="email"
          type="email"
          autoComplete="username"
          inputMode="email"
          autoFocus
          aria-invalid={errors.email ? true : undefined}
          aria-describedby={errors.email ? "email-error" : undefined}
          {...register("email")}
        />
        {errors.email ? (
          <p id="email-error" role="alert" className="text-destructive text-sm">
            {errors.email.message}
          </p>
        ) : null}
      </div>

      <div className="flex flex-col gap-2">
        <Label htmlFor="password">Mot de passe</Label>
        <Input
          id="password"
          type="password"
          autoComplete="current-password"
          aria-invalid={errors.password ? true : undefined}
          aria-describedby={errors.password ? "password-error" : undefined}
          {...register("password")}
        />
        {errors.password ? (
          <p id="password-error" role="alert" className="text-destructive text-sm">
            {errors.password.message}
          </p>
        ) : null}
      </div>

      <Button type="submit" disabled={isSubmitting} className="mt-1">
        {isSubmitting ? (
          <>
            <Loader2 className="animate-spin" aria-hidden="true" />
            Connexion…
          </>
        ) : (
          "Se connecter"
        )}
      </Button>
    </form>
  );
}
