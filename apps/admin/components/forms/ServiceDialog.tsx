"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { toast } from "sonner";
import { Loader2, Plus } from "lucide-react";

import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { bff } from "@/lib/api/client";
import { ApiError, isFieldErrors, messageForError } from "@/lib/api/errors";
import type { AdminService } from "@/lib/api/schemas";

const serviceSchema = z.object({
  title: z.string().trim().min(1, "Le titre est obligatoire.").max(150, "150 caractères maximum."),
  excerpt: z.string().trim().max(300, "300 caractères maximum.").optional(),
  description: z.string().trim().optional(),
  icon: z.string().trim().max(60, "60 caractères maximum.").optional(),
  price_from_xaf: z
    .union([z.number().int().min(0, "Le prix doit être positif."), z.nan()])
    .optional(),
  position: z.number().int().min(0, "La position doit être positive."),
  is_active: z.boolean(),
});

type ServiceValues = z.infer<typeof serviceSchema>;

const FIELDS = new Set<keyof ServiceValues>([
  "title",
  "excerpt",
  "description",
  "icon",
  "price_from_xaf",
  "position",
  "is_active",
]);

interface ServiceDialogProps {
  /** Forfait à modifier. Absent en création. */
  service?: AdminService;
  /** Position proposée pour un nouveau forfait. */
  nextPosition?: number;
}

export function ServiceDialog({ service, nextPosition = 0 }: ServiceDialogProps) {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const isEdit = service !== undefined;

  const {
    register,
    handleSubmit,
    setError,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<ServiceValues>({
    resolver: zodResolver(serviceSchema),
    defaultValues: {
      title: service?.title ?? "",
      excerpt: service?.excerpt ?? "",
      description: service?.description ?? "",
      icon: service?.icon ?? "",
      price_from_xaf: service?.price_from_xaf ?? undefined,
      position: service?.position ?? nextPosition,
      is_active: service?.is_active ?? true,
    },
  });

  const onSubmit = handleSubmit(async (values) => {
    const price = Number.isNaN(values.price_from_xaf) ? null : (values.price_from_xaf ?? null);
    const body = {
      title: values.title,
      excerpt: values.excerpt || null,
      description: values.description || null,
      icon: values.icon || null,
      price_from_xaf: price,
      position: values.position,
      is_active: values.is_active,
    };

    try {
      if (isEdit) {
        await bff(`admin/services/${service.id}`, { method: "PATCH", body });
        toast.success("Forfait enregistré");
      } else {
        await bff("admin/services", { method: "POST", body });
        toast.success("Forfait créé");
        reset();
      }
      setOpen(false);
      router.refresh();
    } catch (error) {
      if (error instanceof ApiError && error.status === 422 && isFieldErrors(error.details)) {
        for (const [field, messages] of Object.entries(error.details)) {
          if (FIELDS.has(field as keyof ServiceValues)) {
            setError(field as keyof ServiceValues, { message: messages[0] });
          }
        }
        return;
      }
      toast.error(messageForError(error));
    }
  });

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        {isEdit ? (
          <Button variant="ghost" size="sm">
            Modifier
          </Button>
        ) : (
          <Button>
            <Plus aria-hidden="true" />
            Nouveau forfait
          </Button>
        )}
      </DialogTrigger>

      <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>{isEdit ? "Modifier le forfait" : "Nouveau forfait"}</DialogTitle>
          <DialogDescription>
            Les forfaits désactivés restent enregistrés mais disparaissent de la vitrine.
          </DialogDescription>
        </DialogHeader>

        <form id="service-form" onSubmit={onSubmit} noValidate className="flex flex-col gap-5">
          <Field label="Titre" htmlFor="title" error={errors.title?.message}>
            <Input id="title" placeholder="Vidange complète" {...register("title")} />
          </Field>

          <Field
            label="Résumé"
            htmlFor="excerpt"
            hint="Une phrase, affichée sur la liste des forfaits"
            error={errors.excerpt?.message}
          >
            <Input id="excerpt" {...register("excerpt")} />
          </Field>

          <Field label="Description" htmlFor="description" error={errors.description?.message}>
            <Textarea id="description" rows={4} {...register("description")} />
          </Field>

          <div className="grid gap-5 sm:grid-cols-2">
            <Field
              label="Prix à partir de"
              htmlFor="price_from_xaf"
              hint="En FCFA. Vide si le prix varie"
              error={errors.price_from_xaf?.message}
            >
              <Input
                id="price_from_xaf"
                type="number"
                inputMode="numeric"
                {...register("price_from_xaf", { valueAsNumber: true })}
              />
            </Field>

            <Field
              label="Position"
              htmlFor="position"
              hint="Ordre d'affichage, 0 en premier"
              error={errors.position?.message}
            >
              <Input
                id="position"
                type="number"
                inputMode="numeric"
                {...register("position", { valueAsNumber: true })}
              />
            </Field>
          </div>

          <Field
            label="Icône"
            htmlFor="icon"
            hint="Nom d'icône utilisé par la vitrine, ex. wrench"
            error={errors.icon?.message}
          >
            <Input id="icon" {...register("icon")} />
          </Field>

          <label className="flex min-h-11 items-center gap-3 text-sm">
            <input
              type="checkbox"
              className="accent-primary size-4"
              {...register("is_active")}
            />
            Visible sur la vitrine
          </label>
        </form>

        <DialogFooter>
          <Button type="button" variant="outline" onClick={() => setOpen(false)} disabled={isSubmitting}>
            Annuler
          </Button>
          <Button type="submit" form="service-form" disabled={isSubmitting}>
            {isSubmitting ? (
              <>
                <Loader2 className="animate-spin" aria-hidden="true" />
                Enregistrement…
              </>
            ) : isEdit ? (
              "Enregistrer"
            ) : (
              "Créer le forfait"
            )}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
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
