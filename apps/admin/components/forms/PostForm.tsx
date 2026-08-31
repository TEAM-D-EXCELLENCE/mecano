"use client";

import { useRouter } from "next/navigation";
import { Controller, useForm } from "react-hook-form";
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
import type { AdminPost, AdminService } from "@/lib/api/schemas";

/** Valeur du select « aucun forfait » — un Select ne peut pas porter `null`. */
const NO_SERVICE = "aucun";

const postSchema = z.object({
  title: z.string().trim().min(1, "Le titre est obligatoire.").max(200, "200 caractères maximum."),
  excerpt: z.string().trim().max(300, "300 caractères maximum.").optional(),
  body: z.string().trim().min(1, "Le contenu est obligatoire."),
  service_id: z.string(),
  status: z.enum(["draft", "published"]),
});

type PostValues = z.infer<typeof postSchema>;

const FIELDS = new Set<keyof PostValues>(["title", "excerpt", "body", "service_id", "status"]);

interface PostFormProps {
  services: AdminService[];
  post?: AdminPost;
}

export function PostForm({ services, post }: PostFormProps) {
  const router = useRouter();
  const isEdit = post !== undefined;

  const {
    register,
    control,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting, isDirty },
  } = useForm<PostValues>({
    resolver: zodResolver(postSchema),
    defaultValues: {
      title: post?.title ?? "",
      excerpt: post?.excerpt ?? "",
      body: post?.body ?? "",
      service_id: post?.service?.id ? String(post.service.id) : NO_SERVICE,
      status: (post?.status?.value as PostValues["status"]) ?? "draft",
    },
  });

  const onSubmit = handleSubmit(async (values) => {
    const body = {
      title: values.title,
      excerpt: values.excerpt || null,
      body: values.body,
      service_id: values.service_id === NO_SERVICE ? null : Number(values.service_id),
      status: values.status,
    };

    try {
      if (isEdit) {
        await bff(`admin/posts/${post.id}`, { method: "PATCH", body });
        toast.success("Article enregistré");
        router.refresh();
      } else {
        const created = await bff<{ data: AdminPost }>("admin/posts", { method: "POST", body });
        toast.success(
          values.status === "published" ? "Article publié" : "Article enregistré en brouillon",
        );
        router.push(`/articles/${created.data.id}`);
      }
    } catch (error) {
      if (error instanceof ApiError && error.status === 422 && isFieldErrors(error.details)) {
        for (const [field, messages] of Object.entries(error.details)) {
          if (FIELDS.has(field as keyof PostValues)) {
            setError(field as keyof PostValues, { message: messages[0] });
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

      <Field label="Titre" htmlFor="title" error={errors.title?.message}>
        <Input id="title" placeholder="Quand changer ses plaquettes de frein" {...register("title")} />
      </Field>

      <Field
        label="Résumé"
        htmlFor="excerpt"
        hint="Affiché sur la liste des articles et dans les résultats de recherche"
        error={errors.excerpt?.message}
      >
        <Input id="excerpt" {...register("excerpt")} />
      </Field>

      <Field
        label="Contenu"
        htmlFor="body"
        hint="Texte brut. La mise en forme est décidée par la vitrine"
        error={errors.body?.message}
      >
        <Textarea id="body" rows={14} className="font-mono text-sm" {...register("body")} />
      </Field>

      <div className="grid gap-5 sm:grid-cols-2">
        <Field
          label="Forfait rattaché"
          htmlFor="service_id"
          hint="Facultatif — relie l'article à une prestation de l'atelier"
          error={errors.service_id?.message}
        >
          <Controller
            control={control}
            name="service_id"
            render={({ field }) => (
              <Select value={field.value} onValueChange={field.onChange}>
                <SelectTrigger id="service_id" className="h-11 w-full">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value={NO_SERVICE}>Aucun</SelectItem>
                  {services.map((service) => (
                    <SelectItem key={service.id} value={String(service.id)}>
                      {service.title}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            )}
          />
        </Field>

        <Field label="Statut" htmlFor="status" error={errors.status?.message}>
          <Controller
            control={control}
            name="status"
            render={({ field }) => (
              <Select value={field.value} onValueChange={field.onChange}>
                <SelectTrigger id="status" className="h-11 w-full">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="draft">Brouillon</SelectItem>
                  <SelectItem value="published">Publié</SelectItem>
                </SelectContent>
              </Select>
            )}
          />
        </Field>
      </div>

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
            "Créer l'article"
          )}
        </Button>
        <Button type="button" variant="ghost" onClick={() => router.push("/articles")}>
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
