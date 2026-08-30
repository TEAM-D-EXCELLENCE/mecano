import type { Metadata } from "next";
import { redirect } from "next/navigation";
import { LoginForm } from "@/components/forms/LoginForm";
import { Wordmark } from "@/components/layout/Wordmark";
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
        <div className="mb-8 flex flex-col items-center gap-4 text-center">
          <Wordmark className="text-foreground [&_span:first-child]:size-9" />
          <p className="text-muted-foreground text-sm">Gestion du garage</p>
        </div>

        <Card className="border-t-primary border-t-2">
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
