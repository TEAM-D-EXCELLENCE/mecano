"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { Car, LayoutDashboard, Newspaper, Settings, Wrench } from "lucide-react";

import { cn } from "@/lib/utils";

interface NavItem {
  href: string;
  label: string;
  icon: typeof Car;
}

const NAV_ITEMS: NavItem[] = [
  { href: "/tableau-de-bord", label: "Tableau de bord", icon: LayoutDashboard },
  { href: "/vehicules", label: "Véhicules", icon: Car },
  { href: "/services", label: "Forfaits atelier", icon: Wrench },
  { href: "/articles", label: "Blog", icon: Newspaper },
  { href: "/reglages", label: "Réglages", icon: Settings },
];

export function Sidebar() {
  const pathname = usePathname();

  return (
    <nav aria-label="Navigation principale" className="flex flex-col py-2">
      {NAV_ITEMS.map(({ href, label, icon: Icon }) => {
        const active = pathname === href || pathname.startsWith(`${href}/`);

        return (
          <Link
            key={href}
            href={href}
            aria-current={active ? "page" : undefined}
            className={cn(
              // Le repère de position est un trait rouge à gauche, pas une pastille :
              // il marque la ligne active sans amollir la grille.
              "relative flex min-h-11 items-center gap-3 border-l-2 pr-4 pl-4 text-sm transition-colors",
              "focus-visible:ring-sidebar-ring/60 outline-none focus-visible:ring-2 focus-visible:ring-inset",
              active
                ? "border-l-sidebar-primary bg-sidebar-accent text-sidebar-accent-foreground font-medium"
                : "border-l-transparent text-sidebar-foreground/60 hover:bg-sidebar-accent/50 hover:text-sidebar-foreground",
            )}
          >
            <Icon className="size-4 shrink-0" aria-hidden="true" />
            {label}
          </Link>
        );
      })}
    </nav>
  );
}
