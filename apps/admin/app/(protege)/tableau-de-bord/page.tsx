import type { Metadata } from "next";

import { StatCard } from "@/components/dashboard/StatCard";
import { apiRequest } from "@/lib/api/server";
import type { DashboardMetrics, Envelope } from "@/lib/api/schemas";

export const metadata: Metadata = {
  title: "Tableau de bord",
};

/** Les compteurs sont des entiers ; l'API peut omettre une section vide. */
const count = (value: number | undefined): string =>
  new Intl.NumberFormat("fr-FR").format(value ?? 0);

const percent = (value: number | undefined): string =>
  `${new Intl.NumberFormat("fr-FR", { maximumFractionDigits: 1 }).format(value ?? 0)} %`;

const days = (value: number | undefined): string =>
  value == null ? "—" : `${new Intl.NumberFormat("fr-FR", { maximumFractionDigits: 1 }).format(value)} j`;

export default async function DashboardPage() {
  const { data } = await apiRequest<Envelope<DashboardMetrics>>("admin/dashboard");

  const { overview, engagement, workshop_and_content: content, quotas } = data;
  const removebg = quotas?.removebg;

  return (
    <div className="flex flex-col gap-8">
      <header className="flex flex-col gap-1">
        <h1 className="text-2xl font-semibold tracking-tight">Tableau de bord</h1>
        <p className="text-muted-foreground text-sm">
          Vue d&apos;ensemble du catalogue, de l&apos;engagement et de l&apos;atelier.
        </p>
      </header>

      <section aria-labelledby="catalogue" className="flex flex-col gap-3">
        <h2 id="catalogue" className="text-sm font-medium">
          Catalogue
        </h2>
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <StatCard label="Annonces en ligne" value={count(overview?.available_cars)} />
          <StatCard label="Réservées" value={count(overview?.reserved_cars)} />
          <StatCard label="Vendues" value={count(overview?.sold_cars)} />
          <StatCard
            label="Brouillons"
            value={count(overview?.draft_cars)}
            hint="Non visibles du public"
          />
        </div>
      </section>

      <section aria-labelledby="engagement" className="flex flex-col gap-3">
        <h2 id="engagement" className="text-sm font-medium">
          Engagement
        </h2>
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <StatCard label="Vues des fiches" value={count(engagement?.total_views)} />
          <StatCard
            label="Clics WhatsApp"
            value={count(engagement?.total_whatsapp_clicks)}
          />
          <StatCard
            label="Taux de conversion"
            value={percent(engagement?.conversion_rate_percentage)}
            hint="Clics WhatsApp rapportés aux vues"
          />
          <StatCard
            label="Délai moyen de vente"
            value={days(engagement?.average_days_to_sell)}
            hint="De la publication à la vente"
          />
        </div>
      </section>

      <section aria-labelledby="atelier" className="flex flex-col gap-3">
        <h2 id="atelier" className="text-sm font-medium">
          Atelier et blog
        </h2>
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <StatCard
            label="Forfaits actifs"
            value={count(content?.active_services)}
            hint={`${count(content?.total_services)} au total`}
          />
          <StatCard
            label="Articles publiés"
            value={count(content?.published_posts)}
            hint={`${count(content?.total_posts)} au total`}
          />
          {removebg ? (
            <StatCard
              label="Quota détourage"
              value={`${count(removebg.used)} / ${count(removebg.limit)}`}
              hint={`Période ${removebg.period ?? "en cours"} — remise à zéro le 1er du mois`}
              className="sm:col-span-2"
            />
          ) : null}
        </div>
      </section>
    </div>
  );
}
