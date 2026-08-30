import type { Metadata } from "next";
import Link from "next/link";
import { Plus } from "lucide-react";

import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { listPosts } from "@/lib/api/content";
import { formatDate } from "@/lib/format";

export const metadata: Metadata = {
  title: "Blog",
};

export default async function PostsPage({ searchParams }: PageProps<"/articles">) {
  const params = await searchParams;
  const page = Number(typeof params.page === "string" ? params.page : "1") || 1;

  const { data: posts, meta } = await listPosts(page);

  return (
    <div className="flex flex-col gap-6">
      <header className="flex flex-wrap items-start justify-between gap-4">
        <div className="flex flex-col gap-1">
          <h1 className="text-2xl font-semibold tracking-tight">Blog</h1>
          <p className="text-muted-foreground text-sm">
            {meta.total} article{(meta.total ?? 0) > 1 ? "s" : ""}, brouillons compris.
          </p>
        </div>
        <Button asChild>
          <Link href="/articles/nouveau">
            <Plus aria-hidden="true" />
            Nouvel article
          </Link>
        </Button>
      </header>

      {posts.length === 0 ? (
        <div className="flex flex-col items-center gap-3 border border-dashed px-6 py-16 text-center">
          <p className="font-medium">Aucun article pour le moment</p>
          <p className="text-muted-foreground max-w-sm text-sm">
            Les articles crédibilisent l&apos;atelier et amènent des visiteurs depuis
            Google. Un conseil d&apos;entretien suffit pour commencer.
          </p>
          <Button asChild className="mt-1">
            <Link href="/articles/nouveau">Écrire un article</Link>
          </Button>
        </div>
      ) : (
        <>
          <div className="overflow-x-auto border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Article</TableHead>
                  <TableHead>Statut</TableHead>
                  <TableHead>Forfait</TableHead>
                  <TableHead>Publié le</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {posts.map((post) => (
                  <TableRow key={post.id}>
                    <TableCell>
                      <Link
                        href={`/articles/${post.id}`}
                        className="focus-visible:ring-ring/50 rounded-sm font-medium outline-none hover:underline focus-visible:ring-[3px]"
                      >
                        {post.title}
                      </Link>
                      {post.excerpt ? (
                        <span className="text-muted-foreground block max-w-lg truncate text-xs">
                          {post.excerpt}
                        </span>
                      ) : null}
                    </TableCell>
                    <TableCell>
                      {post.status?.value === "published" ? (
                        <Badge variant="outline" className="bg-success text-success-foreground border-transparent">
                          {post.status.label ?? "Publié"}
                        </Badge>
                      ) : (
                        <Badge variant="outline" className="bg-muted text-muted-foreground border-transparent">
                          {post.status?.label ?? "Brouillon"}
                        </Badge>
                      )}
                    </TableCell>
                    <TableCell className="text-muted-foreground">
                      {post.service?.title ?? "—"}
                    </TableCell>
                    <TableCell className="text-muted-foreground">
                      {formatDate(post.published_at ?? null)}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>

          {(meta.last_page ?? 1) > 1 ? (
            <nav aria-label="Pagination" className="flex items-center justify-between gap-4">
              <Button asChild variant="outline" size="sm" disabled={page <= 1}>
                <Link
                  href={page - 1 > 1 ? `/articles?page=${page - 1}` : "/articles"}
                  aria-disabled={page <= 1}
                  tabIndex={page <= 1 ? -1 : undefined}
                >
                  Précédent
                </Link>
              </Button>
              <p className="text-muted-foreground text-sm">
                Page {meta.current_page} sur {meta.last_page}
              </p>
              <Button asChild variant="outline" size="sm" disabled={page >= (meta.last_page ?? 1)}>
                <Link
                  href={`/articles?page=${page + 1}`}
                  aria-disabled={page >= (meta.last_page ?? 1)}
                  tabIndex={page >= (meta.last_page ?? 1) ? -1 : undefined}
                >
                  Suivant
                </Link>
              </Button>
            </nav>
          ) : null}
        </>
      )}
    </div>
  );
}
