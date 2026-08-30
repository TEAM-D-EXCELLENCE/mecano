import type { Metadata } from "next";
import { redirect } from "next/navigation";
import { Wrench } from "lucide-react";

import { LoginForm } from "@/components/forms/LoginForm";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { getSessionToken } from "@/lib/api/server";

export const metadata: Metadata = {
  title: "Connexion",
};

export default async function LoginPage() {
  // Session déjà ouverte : inutile de repasser par le formulaire.
  if (await getSessionToken()) {
    redirect("/tableau-de-bord");
  }

  return (
    <main className="flex flex-1 items-center justify-center p-6">
      <div className="w-full max-w-sm">
        <div className="mb-8 flex flex-col items-center gap-3 text-center">
          <span className="bg-primary text-primary-foreground flex size-12 items-center justify-center rounded-xl">
            <Wrench className="size-6" aria-hidden="true" />
          </span>
          <div>
            <h1 className="text-xl font-semibold tracking-tight">Backoffice Mecano</h1>
            <p className="text-muted-foreground text-sm">Gestion du garage</p>
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Connexion</CardTitle>
            <CardDescription>
              Entrez vos identifiants pour accéder à la gestion des annonces.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <LoginForm />
          </CardContent>
        </Card>
      </div>
    </main>
  );
}
