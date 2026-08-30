import type { Metadata } from "next";

import { SettingsForm } from "@/components/forms/SettingsForm";
import { getSettings } from "@/lib/api/content";

export const metadata: Metadata = {
  title: "Réglages",
};

export default async function SettingsPage() {
  const settings = await getSettings();

  return (
    <div className="flex max-w-2xl flex-col gap-6">
      <header className="flex flex-col gap-1">
        <h1 className="text-2xl font-semibold tracking-tight">Réglages</h1>
        <p className="text-muted-foreground text-sm">
          Informations du garage utilisées par la vitrine. Une modification est visible
          en quelques secondes.
        </p>
      </header>

      <SettingsForm settings={settings} />
    </div>
  );
}
