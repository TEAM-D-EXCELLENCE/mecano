import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { ChevronLeft } from "lucide-react";

import { PostForm } from "@/components/forms/PostForm";
import { Badge } from "@/components/ui/badge";
import { listServices, getPost } from "@/lib/api/content";
import { ApiError } from "@/lib/api/errors";
import { formatDate } from "@/lib/format";

async function load(rawId: string) {
  const id = Number(rawId);
  if (!Number.isInteger(id) || id <= 0) notFound();

  try {
    const [post, services] = await Promise.all([getPost(id), listServices()]);
    return { post, services };
  } catch (error) {
    if (error instanceof ApiError && error.status === 404) notFound();
    throw error;
  }
}

export async function generateMetadata({ params }: PageProps<"/articles/[id]">): Promise<Metadata> {
  const { id } = await params;
  try {
    const post = await getPost(Number(id));
    return { title: post.title };
  } catch {
    return { title: "Article" };
  }
}

export default async function PostDetailPage({ params }: PageProps<"/articles/[id]">) {
  const { id } = await params;
  const { post, services } = await load(id);

  return (
    <div className="flex max-w-3xl flex-col gap-8">
      <header className="flex flex-col gap-4">
        <Link
          href="/articles"
          className="text-muted-foreground hover:text-foreground focus-visible:ring-ring/50 inline-flex w-fit items-center gap-1 rounded-sm text-sm outline-none focus-visible:ring-[3px]"
        >
          <ChevronLeft className="size-4" aria-hidden="true" />
          Blog
        </Link>

        <div className="flex flex-col gap-2">
          <div className="flex flex-wrap items-center gap-3">
            <h1 className="text-2xl font-semibold tracking-tight">{post.title}</h1>
            <Badge
              variant="outline"
              className={
                post.status?.value === "published"
                  ? "bg-success text-success-foreground border-transparent"
                  : "bg-muted text-muted-foreground border-transparent"
              }
            >
              {post.status?.label ?? "Brouillon"}
            </Badge>
          </div>
          <p className="text-muted-foreground font-mono text-xs">{post.slug}</p>
          <p className="text-muted-foreground text-sm">
            {post.author?.name ? `Par ${post.author.name} · ` : ""}
            {post.published_at
              ? `publié le ${formatDate(post.published_at)}`
              : "jamais publié"}
          </p>
        </div>
      </header>

      <PostForm services={services} post={post} />
    </div>
  );
}
