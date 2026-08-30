import { Wrench } from "lucide-react";

import { LogoutButton } from "@/components/layout/LogoutButton";
import { Sidebar } from "@/components/layout/Sidebar";
import { requireUser } from "@/lib/api/session";

/**
 * Shell du backoffice.
 *
 * La garde est **côté serveur** : `requireUser()` interroge `/auth/me` avant tout
 * rendu. Aucun contenu protégé n'atteint le navigateur avant la redirection,
 * contrairement à une garde côté client qui laisse toujours filtrer un instant.
 */
// Le backoffice dépend du cookie de session à chaque requête : rien n'y est
// prérendu, et aucune réponse n'est mise en cache.
export const dynamic = "force-dynamic";

export default async function ProtectedLayout({ children }: LayoutProps<"/">) {
  const user = await requireUser();

  return (
    <div className="flex min-h-full flex-1 flex-col md:flex-row">
      <aside className="bg-sidebar text-sidebar-foreground border-sidebar-border flex flex-col border-b md:min-h-screen md:w-60 md:shrink-0 md:border-r md:border-b-0">
        <div className="flex items-center gap-2.5 px-5 py-4">
          <span className="bg-primary text-primary-foreground flex size-8 items-center justify-center rounded-lg">
            <Wrench className="size-4" aria-hidden="true" />
          </span>
          <span className="text-sm font-semibold tracking-tight">Mecano</span>
        </div>

        <Sidebar />

        <div className="border-sidebar-border mt-auto hidden flex-col gap-2 border-t px-3 py-3 md:flex">
          <p className="text-muted-foreground truncate px-2 text-xs" title={user.email}>
            {user.name}
          </p>
          <LogoutButton />
        </div>
      </aside>

      <div className="flex flex-1 flex-col">
        <header className="flex items-center justify-end gap-3 px-6 py-3 md:hidden">
          <span className="text-muted-foreground text-xs">{user.name}</span>
          <LogoutButton />
        </header>
        <main className="flex-1 p-6 md:p-8">{children}</main>
      </div>
    </div>
  );
}
