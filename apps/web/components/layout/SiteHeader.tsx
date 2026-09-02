"use client";

import { usePathname } from "next/navigation";
import { Header } from "./Header";

/**
 * The home page owns its progressive header through ScrollExpand. All other
 * routes use the shared fixed header supplied by the root layout.
 */
export function SiteHeader() {
  const pathname = usePathname();

  if (pathname === "/") return null;

  return <Header isFixed />;
}
