"use client";

import { useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { toast } from "sonner";
import { AlertCircle, Loader2 } from "lucide-react";

import { Alert, AlertDescription } from "@/components/ui/alert";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { bff } from "@/lib/api/client";
import { messageForError } from "@/lib/api/errors";
import type { AdminSettings } from "@/lib/api/schemas";

const settingsSchema = z.object({
  garage_name: z.string().trim().min(1, "Le nom du garage est obligatoire."),
  whatsapp_number: z
    .string()
    .trim()
    .min(1, "Le numéro WhatsApp est obligatoire.")
    .regex(/^\+?[0-9\s]{8,20}$/, "Numéro invalide. Exemple : +237699001122"),
  address: z.string().trim().optional(),
  hero_title: z.string().trim().optional(),
  hero_subtitle: z.string().trim().optional(),
  logo_url: z.string().trim().optional(),
  heures_lundi_vendredi: z.string().trim().optional(),
  heures_samedi: z.string().trim().optional(),
  heures_dimanche: z.string().trim().optional(),
});

type SettingsValues = z.infer<typeof settingsSchema>;

function readHours(settings: AdminSettings, key: string): string {
  const hours = settings.opening_hours as Record<string, unknown> | undefined;
  const value = hours?.[key];
  return typeof value === "string" ? value : "";
}

export function SettingsForm({ settings }: { settings: AdminSettings }) {
  const router = useRouter();

  const {
    register,
    handleSubmit,
    reset,
    setError,
    formState: { errors, isSubmitting, isDirty },
  } = useForm<SettingsValues>({
    resolver: zodResolver(settingsSchema),
    defaultValues: {
      garage_name: settings.garage_name ?? "",
      whatsapp_number: settings.whatsapp_number ?? "",
      address: settings.address ?? "",
      hero_title: settings.hero_title ?? "",
      hero_subtitle: settings.hero_subtitle ?? "",
      logo_url: settings.logo_url ?? "",
      heures_lundi_vendredi: readHours(settings, "lundi_vendredi"),
      heures_samedi: readHours(settings, "samedi"),
      heures_dimanche: readHours(settings, "dimanche"),
    },
  });

  const onSubmit = handleSubmit(async (values) => {
    // L'API applique les réglages clé par clé : n'envoyer que ceux que ce
    // formulaire connaît laisse intactes les clés qu'il n'affiche pas.
    const body = {
      settings: {
        garage_name: values.garage_name,
        whatsapp_number: values.whatsapp_number,
        address: values.address || null,
        hero_title: values.hero_title || null,
        hero_subtitle: values.hero_subtitle || null,
        logo_url: values.logo_url || null,
        opening_hours: {
          lundi_vendredi: values.heures_lundi_vendredi || null,
          samedi: values.heures_samedi || null,
          dimanche: values.heures_dimanche || null,
        },
      },
    };

    try {
      await bff("admin/settings", { method: "PATCH", body });
      toast.success("Réglages enregistrés");
      reset(values);
      router.refresh();
    } catch (error) {
      setError("root", { message: messageForError(error) });
    }
  });

  return (
    <form onSubmit={onSubmit} noValidate className="flex flex-col gap-8">
      {errors.root ? (
        <Alert variant="destructive">
          <AlertCircle aria-hidden="true" />
          <AlertDescription>{errors.root.message}</AlertDescription>
        </Alert>
      ) : null}

      <Section
        title="Le garage"
        description="Ces informations apparaissent partout sur la vitrine."
      >
        <Field label="Nom du garage" htmlFor="garage_name" error={errors.garage_name?.message}>
          <Input id="garage_name" {...register("garage_name")} />
        </Field>

        <Field
          label="Numéro WhatsApp"
          htmlFor="whatsapp_number"
          hint="Avec l'indicatif pays. C'est ce numéro qui reçoit les demandes des acheteurs."
          error={errors.whatsapp_number?.message}
        >
          <Input id="whatsapp_number" inputMode="tel" {...register("whatsapp_number")} />
        </Field>

        <Field label="Adresse" htmlFor="address" error={errors.address?.message}>
          <Input id="address" {...register("address")} />
        </Field>

        <Field
          label="URL du logo"
          htmlFor="logo_url"
          hint="Laissez vide si le garage n'a pas encore de logo"
          error={errors.logo_url?.message}
        >
          <Input id="logo_url" inputMode="url" {...register("logo_url")} />
        </Field>
      </Section>

      <Section
        title="Accueil de la vitrine"
        description="Le premier bloc que voit un visiteur qui arrive sur le site."
      >
        <Field label="Titre" htmlFor="hero_title" error={errors.hero_title?.message}>
          <Textarea id="hero_title" rows={2} {...register("hero_title")} />
        </Field>

        <Field label="Sous-titre" htmlFor="hero_subtitle" error={errors.hero_subtitle?.message}>
          <Textarea id="hero_subtitle" rows={2} {...register("hero_subtitle")} />
        </Field>
      </Section>

      <Section title="Horaires d'ouverture">
        <div className="grid gap-5 sm:grid-cols-3">
          <Field label="Lundi au vendredi" htmlFor="heures_lundi_vendredi">
            <Input id="heures_lundi_vendredi" placeholder="08:00 - 18:00" {...register("heures_lundi_vendredi")} />
          </Field>
          <Field label="Samedi" htmlFor="heures_samedi">
            <Input id="heures_samedi" placeholder="08:30 - 14:00" {...register("heures_samedi")} />
          </Field>
          <Field label="Dimanche" htmlFor="heures_dimanche">
            <Input id="heures_dimanche" placeholder="Fermé" {...register("heures_dimanche")} />
          </Field>
        </div>
      </Section>

      <div>
        <Button type="submit" disabled={isSubmitting || !isDirty}>
          {isSubmitting ? (
            <>
              <Loader2 className="animate-spin" aria-hidden="true" />
              Enregistrement…
            </>
          ) : (
            "Enregistrer les réglages"
          )}
        </Button>
      </div>
    </form>
  );
}

function Section({
  title,
  description,
  children,
}: {
  title: string;
  description?: string;
  children: React.ReactNode;
}) {
  return (
    <section className="flex flex-col gap-5 border-t pt-6 first:border-t-0 first:pt-0">
      <div className="flex flex-col gap-1">
        <h2 className="text-sm font-medium">{title}</h2>
        {description ? <p className="text-muted-foreground text-sm">{description}</p> : null}
      </div>
      {children}
    </section>
  );
}

function Field({
  label,
  htmlFor,
  hint,
  error,
  children,
}: {
  label: string;
  htmlFor: string;
  hint?: string;
  error?: string;
  children: React.ReactNode;
}) {
  return (
    <div className="flex flex-col gap-2">
      <Label htmlFor={htmlFor}>{label}</Label>
      {children}
      {hint && !error ? <p className="text-muted-foreground text-xs">{hint}</p> : null}
      {error ? (
        <p role="alert" className="text-destructive text-sm">
          {error}
        </p>
      ) : null}
    </div>
  );
}
