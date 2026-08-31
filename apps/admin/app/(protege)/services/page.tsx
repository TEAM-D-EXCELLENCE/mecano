import type { Metadata } from "next";

import { ServiceDialog } from "@/components/forms/ServiceDialog";
import { Badge } from "@/components/ui/badge";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { listServices } from "@/lib/api/content";
import { formatPriceXaf } from "@/lib/format";

export const metadata: Metadata = {
  title: "Forfaits atelier",
};

export default async function ServicesPage() {
  const services = await listServices();
  const active = services.filter((s) => s.is_active).length;
  const nextPosition = services.reduce((max, s) => Math.max(max, s.position ?? 0), -1) + 1;

  return (
    <div className="flex flex-col gap-6">
      <header className="flex flex-wrap items-start justify-between gap-4">
        <div className="flex flex-col gap-1">
          <h1 className="text-2xl font-semibold tracking-tight">Forfaits atelier</h1>
          <p className="text-muted-foreground text-sm">
            {services.length} forfait{services.length > 1 ? "s" : ""}, dont {active} visible
            {active > 1 ? "s" : ""} sur la vitrine.
          </p>
        </div>
        <ServiceDialog nextPosition={nextPosition} />
      </header>

      {services.length === 0 ? (
        <div className="flex flex-col items-center gap-3 border border-dashed px-6 py-16 text-center">
          <p className="font-medium">Aucun forfait pour le moment</p>
          <p className="text-muted-foreground max-w-sm text-sm">
            Les forfaits décrivent les prestations de l&apos;atelier : vidange, freins,
            diagnostic. Ils apparaissent sur la vitrine et peuvent être cités par un
            article du blog.
          </p>
          <ServiceDialog nextPosition={0} />
        </div>
      ) : (
        <div className="overflow-x-auto border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead className="w-14">#</TableHead>
                <TableHead>Forfait</TableHead>
                <TableHead>Visibilité</TableHead>
                <TableHead className="text-right">À partir de</TableHead>
                <TableHead className="text-right">Articles</TableHead>
                <TableHead className="w-24" />
              </TableRow>
            </TableHeader>
            <TableBody>
              {services.map((service) => (
                <TableRow key={service.id}>
                  <TableCell className="text-muted-foreground">{service.position}</TableCell>
                  <TableCell>
                    <span className="font-medium">{service.title}</span>
                    {service.excerpt ? (
                      <span className="text-muted-foreground block max-w-md truncate text-xs">
                        {service.excerpt}
                      </span>
                    ) : null}
                  </TableCell>
                  <TableCell>
                    {service.is_active ? (
                      <Badge variant="outline" className="bg-success text-success-foreground border-transparent">
                        Visible
                      </Badge>
                    ) : (
                      <Badge variant="outline" className="text-muted-foreground border-transparent bg-muted">
                        Masqué
                      </Badge>
                    )}
                  </TableCell>
                  <TableCell className="text-right">
                    {service.price_from_xaf != null ? (
                      formatPriceXaf(service.price_from_xaf)
                    ) : (
                      <span className="text-muted-foreground">Sur devis</span>
                    )}
                  </TableCell>
                  <TableCell className="text-muted-foreground text-right">
                    {service.posts_count ?? 0}
                  </TableCell>
                  <TableCell className="text-right">
                    <ServiceDialog service={service} />
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </div>
      )}
    </div>
  );
}
