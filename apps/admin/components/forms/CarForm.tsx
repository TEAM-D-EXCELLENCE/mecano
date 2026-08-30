"use client";

import { useRouter } from "next/navigation";
import { useForm, Controller } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { toast } from "sonner";
import { AlertCircle, Loader2 } from "lucide-react";

import { Alert, AlertDescription } from "@/components/ui/alert";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";
import { bff } from "@/lib/api/client";
import { ApiError, isFieldErrors, messageForError } from "@/lib/api/errors";
import type { AdminBrand, AdminCar } from "@/lib/api/schemas";

/**
 * Les valeurs d'énumération viennent du contrat : `openapi.yaml` les déclare,
 * `types/api.d.ts` les propage. En ajouter une ici sans l'ajouter au contrat
 * produirait un 422 que le formulaire ne saurait pas expliquer.
 */
const FUELS = [
  { value: "essence", label: "Essence" },
  { value: "diesel", label: "Diesel" },
  { value: "hybride", label: "Hybride" },
  { value: "electrique", label: "Électrique" },
  { value: "gpl", label: "GPL" },
] as const;

const TRANSMISSIONS = [
  { value: "manuelle", label: "Manuelle" },
  { value: "automatique", label: "Automatique" },
] as const;

const CONDITIONS = [
  { value: "neuf", label: "Neuf" },
  { value: "excellent", label: "Excellent état" },
  { value: "bon", label: "Bon état" },
  { value: "moyen", label: "État moyen" },
] as const;

const NEXT_YEAR = new Date().getFullYear() + 1;

const carSchema = z.object({
  brand_id: z.number().int().positive("Choisissez une marque."),
  model: z.string().trim().min(1, "Le modèle est obligatoire.").max(120, "120 caractères maximum."),
  year: z
    .number({ error: "L'année est obligatoire." })
    .int()
    .min(1950, "L'année ne peut pas être antérieure à 1950.")
    .max(NEXT_YEAR, `L'année ne peut pas dépasser ${NEXT_YEAR}.`),
  mileage_km: z
    .number({ error: "Le kilométrage est obligatoire." })
    .int()
    .min(0, "Le kilométrage doit être positif."),
  price_xaf: z
    .number({ error: "Le prix est obligatoire." })
    .int()
    .min(0, "Le prix doit être positif."),
  fuel: z.enum(["essence", "diesel", "hybride", "electrique", "gpl"]),
  transmission: z.enum(["manuelle", "automatique"]),
  condition: z.enum(["neuf", "excellent", "bon", "moyen"]),
  color: z.string().trim().min(1, "La couleur est obligatoire.").max(40, "40 caractères maximum."),
  description: z.string().trim().max(5000, "5000 caractères maximum.").optional(),
});

type CarValues = z.infer<typeof carSchema>;

const FIELD_NAMES = new Set<keyof CarValues>([
  "brand_id",
  "model",
  "year",
  "mileage_km",
  "price_xaf",
  "fuel",
  "transmission",
  "condition",
  "color",
  "description",
]);

interface CarFormProps {
  brands: AdminBrand[];
  /** Annonce existante — absente en création. */
  car?: AdminCar;
}

export function CarForm({ brands, car }: CarFormProps) {
  const router = useRouter();
  const isEdit = car !== undefined;

  const {
    register,
    control,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting, isDirty },
  } = useForm<CarValues>({
    resolver: zodResolver(carSchema),
    defaultValues: {
      brand_id: car?.brand_id ?? brands[0]?.id ?? 0,
      model: car?.model ?? "",
      year: car?.year ?? NEXT_YEAR - 1,
      mileage_km: car?.mileage_km ?? 0,
      price_xaf: car?.price_xaf ?? 0,
      fuel: (car?.fuel?.value as CarValues["fuel"]) ?? "essence",
      transmission: (car?.transmission?.value as CarValues["transmission"]) ?? "manuelle",
      condition: (car?.condition?.value as CarValues["condition"]) ?? "bon",
      color: car?.color ?? "",
      description: car?.description ?? "",
    },
  });

  const onSubmit = handleSubmit(async (values) => {
    const body = { ...values, description: values.description || null };

    try {
      if (isEdit) {
        await bff(`admin/cars/${car.id}`, { method: "PATCH", body });
        toast.success("Annonce enregistrée");
        router.refresh();
      } else {
        const created = await bff<{ data: AdminCar }>("admin/cars", {
          method: "POST",
          body,
        });
        toast.success("Annonce créée — ajoutez une photo principale pour pouvoir la publier");
        router.push(`/vehicules/${created.data.id}`);
      }
    } catch (error) {
      // L'API reste la seule autorité : ses 422 sont replacés sous chaque champ.
      if (error instanceof ApiError && error.status === 422 && isFieldErrors(error.details)) {
        for (const [field, messages] of Object.entries(error.details)) {
          if (FIELD_NAMES.has(field as keyof CarValues)) {
            setError(field as keyof CarValues, { message: messages[0] });
          }
        }
        setError("root", { message: "Certains champs sont invalides." });
        return;
      }

      setError("root", { message: messageForError(error) });
    }
  });

  return (
    <form onSubmit={onSubmit} noValidate className="flex flex-col gap-6">
      {errors.root ? (
        <Alert variant="destructive">
          <AlertCircle aria-hidden="true" />
          <AlertDescription>{errors.root.message}</AlertDescription>
        </Alert>
      ) : null}

      <div className="grid gap-5 sm:grid-cols-2">
        <Field label="Marque" htmlFor="brand_id" error={errors.brand_id?.message}>
          <Controller
            control={control}
            name="brand_id"
            render={({ field }) => (
              <Select
                value={field.value ? String(field.value) : undefined}
                onValueChange={(v) => field.onChange(Number(v))}
              >
                <SelectTrigger id="brand_id" className="h-11 w-full">
                  <SelectValue placeholder="Choisir une marque" />
                </SelectTrigger>
                <SelectContent>
                  {brands.map((brand) => (
                    <SelectItem key={brand.id} value={String(brand.id)}>
                      {brand.name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            )}
          />
        </Field>

        <Field label="Modèle" htmlFor="model" error={errors.model?.message}>
          <Input id="model" placeholder="RAV4 Limited" {...register("model")} />
        </Field>

        <Field label="Année" htmlFor="year" error={errors.year?.message}>
          <Input
            id="year"
            type="number"
            inputMode="numeric"
            {...register("year", { valueAsNumber: true })}
          />
        </Field>

        <Field label="Couleur" htmlFor="color" error={errors.color?.message}>
          <Input id="color" placeholder="Gris métallisé" {...register("color")} />
        </Field>

        <Field
          label="Prix"
          htmlFor="price_xaf"
          hint="En FCFA, sans espace ni séparateur"
          error={errors.price_xaf?.message}
        >
          <Input
            id="price_xaf"
            type="number"
            inputMode="numeric"
            {...register("price_xaf", { valueAsNumber: true })}
          />
        </Field>

        <Field label="Kilométrage" htmlFor="mileage_km" hint="En kilomètres" error={errors.mileage_km?.message}>
          <Input
            id="mileage_km"
            type="number"
            inputMode="numeric"
            {...register("mileage_km", { valueAsNumber: true })}
          />
        </Field>

        <EnumField
          control={control}
          name="fuel"
          label="Carburant"
          options={FUELS}
          error={errors.fuel?.message}
        />
        <EnumField
          control={control}
          name="transmission"
          label="Transmission"
          options={TRANSMISSIONS}
          error={errors.transmission?.message}
        />
        <EnumField
          control={control}
          name="condition"
          label="État"
          options={CONDITIONS}
          error={errors.condition?.message}
        />
      </div>

      <Field label="Description" htmlFor="description" error={errors.description?.message}>
        <Textarea
          id="description"
          rows={5}
          placeholder="Climatisation d'origine, intérieur cuir, révision complète effectuée…"
          {...register("description")}
        />
      </Field>

      <div className="flex items-center gap-3">
        <Button type="submit" disabled={isSubmitting || (isEdit && !isDirty)}>
          {isSubmitting ? (
            <>
              <Loader2 className="animate-spin" aria-hidden="true" />
              Enregistrement…
            </>
          ) : isEdit ? (
            "Enregistrer les modifications"
          ) : (
            "Créer l'annonce"
          )}
        </Button>
        <Button type="button" variant="ghost" onClick={() => router.push("/vehicules")}>
          Annuler
        </Button>
      </div>
    </form>
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

function EnumField<K extends "fuel" | "transmission" | "condition">({
  control,
  name,
  label,
  options,
  error,
}: {
  control: ReturnType<typeof useForm<CarValues>>["control"];
  name: K;
  label: string;
  options: readonly { value: string; label: string }[];
  error?: string;
}) {
  return (
    <Field label={label} htmlFor={name} error={error}>
      <Controller
        control={control}
        name={name}
        render={({ field }) => (
          <Select value={field.value} onValueChange={field.onChange}>
            <SelectTrigger id={name} className="h-11 w-full">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {options.map((option) => (
                <SelectItem key={option.value} value={option.value}>
                  {option.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        )}
      />
    </Field>
  );
}
