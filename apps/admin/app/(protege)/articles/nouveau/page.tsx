import type { Metadata } from "next";
import Link from "next/link";
import { ChevronLeft } from "lucide-react";

import { PostForm } from "@/components/forms/PostForm";
import { listServices } from "@/lib/api/content";

export const metadata: Metadata = {
  title: "Nouvel article",
};

export default async function NewPostPage() {
  const services = await listServices();

  return (
    <div className="flex max-w-3xl flex-col gap-6">
      <div className="flex flex-col gap-2">
        <Link
          href="/articles"
          className="text-muted-foreground hover:text-foreground focus-visible:ring-ring/50 inline-flex w-fit items-center gap-1 rounded-sm text-sm outline-none focus-visible:ring-[3px]"
        >
          <ChevronLeft className="size-4" aria-hidden="true" />
          Blog
        </Link>
        <h1 className="text-2xl font-semibold tracking-tight">Nouvel article</h1>
        <p className="text-muted-foreground text-sm">
          Enregistrez-le en brouillon tant qu&apos;il n&apos;est pas prêt : seuls les
          articles publiés apparaissent sur la vitrine.
        </p>
      </div>

      <PostForm services={services} />
    </div>
  );
}
