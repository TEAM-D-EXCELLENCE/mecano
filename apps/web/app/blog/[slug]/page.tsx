import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { notFound } from "next/navigation";
import { getPost, posts } from "@/lib/mock-data";
import { formatDate } from "@/lib/format";

interface PostPageProps {
  params: Promise<{ slug: string }>;
}

const ArrowUpRightIcon = ({ className }: { className?: string }) => (
  <svg viewBox="0 0 24 24" fill="none" className={className}>
    <path
      d="M7 17L17 7M17 7H9M17 7V15"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

const ArrowLeftIcon = ({ className }: { className?: string }) => (
  <svg viewBox="0 0 24 24" fill="none" className={className}>
    <path
      d="M17 7L7 17M7 17H15M7 17V9"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export function generateStaticParams() {
  return posts.map(({ slug }) => ({ slug }));
}

export async function generateMetadata({
  params,
}: PostPageProps): Promise<Metadata> {
  const post = getPost((await params).slug);
  return post ? { title: post.title, description: post.excerpt } : {};
}

export default async function PostPage({ params }: PostPageProps) {
  const post = getPost((await params).slug);
  if (!post) notFound();

  const related = posts.filter((p) => p.slug !== post.slug).slice(0, 3);

  return (
    /* 🟢 Fond harmonisé avec la charte graphique globale de Mecano (#0A0D10) */
    <main className="min-h-screen bg-[#0A0D10] font-sans antialiased selection:bg-emerald-500/30 selection:text-emerald-300">
      {/* En-tête article */}
      <header className="border-b border-slate-800/60 px-5 pb-14 pt-10 text-white sm:pt-16">
        <div className="mx-auto max-w-3xl">
          <div className="mt-8 flex items-center gap-3 font-mono text-xs font-bold uppercase tracking-[.25em] text-slate-500">
            <span className="text-emerald-400 font-extrabold">
              {post.category}
            </span>
            <span className="h-px flex-1 bg-slate-800/60" />
            <span>{formatDate(post.publishedAt)}</span>
          </div>

          <h1 className="mt-5 text-4xl font-black leading-[1.15] tracking-tight text-white sm:text-5xl lg:text-6xl">
            {post.title}
          </h1>
          {/* 🟢 Amélioration du contraste pour le sous-titre (slate-400 -> slate-300) */}
          <p className="mt-6 max-w-2xl text-lg leading-relaxed text-slate-300 sm:text-xl">
            {post.excerpt}
          </p>
        </div>
      </header>

      {/* Image + corps de l'article */}
      <article className="px-5 py-14 sm:py-16">
        <div className="mx-auto max-w-3xl">
          <div className="relative aspect-[16/9] overflow-hidden rounded-3xl border border-slate-800/60 shadow-2xl shadow-black/40">
            <Image
              src={post.image}
              alt=""
              fill
              priority
              sizes="(max-width: 768px) 100vw, 768px"
              className="object-cover"
            />
          </div>

          {/* 🟢 Refonte complète des styles Tailwind Prose pour garantir le confort de lecture */}
          <div className="prose prose-invert prose-emerald mt-12 max-w-none text-base leading-8 text-slate-300 sm:text-lg prose-p:mb-6 prose-headings:text-white prose-headings:font-black prose-strong:text-white prose-strong:font-bold">
            {post.body.map((paragraph) => (
              <p key={paragraph}>{paragraph}</p>
            ))}
          </div>
        </div>
      </article>

      {/* Articles liés */}
      {related.length > 0 && (
        <section className="border-t border-slate-800/60 px-5 py-16 sm:py-20">
          <div className="mx-auto max-w-7xl lg:px-8">
            <h2 className="text-2xl font-black tracking-tight text-white sm:text-3xl">
              À lire aussi
            </h2>
            <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {related.map((p) => (
                <Link
                  key={p.slug}
                  href={`/blog/${p.slug}`}
                  className="group flex flex-col overflow-hidden rounded-3xl border border-slate-800/40 bg-slate-900/20 backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:border-emerald-500/30 hover:bg-slate-900/40"
                >
                  <div className="relative aspect-[16/10] overflow-hidden">
                    <Image
                      src={p.image}
                      alt={p.title}
                      fill
                      sizes="(min-width: 1024px) 33vw, (min-width: 640px) 45vw, 90vw"
                      className="object-cover opacity-80 transition-all duration-700 ease-out group-hover:scale-105 group-hover:opacity-100"
                    />
                  </div>
                  <div className="flex flex-1 flex-col p-5 sm:p-6">
                    <div className="flex items-center justify-between font-mono text-[11px] font-bold uppercase tracking-[.2em] text-slate-500">
                      <span className="text-emerald-400 font-extrabold">
                        {p.category}
                      </span>
                      <span>{formatDate(p.publishedAt)}</span>
                    </div>
                    <h3 className="mt-3 text-lg font-bold leading-snug text-white transition-colors duration-200 group-hover:text-emerald-400">
                      {p.title}
                    </h3>
                    <span className="mt-4 inline-flex items-center gap-1.5 text-sm font-bold text-emerald-400">
                      Lire l&apos;article
                      <ArrowUpRightIcon className="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                    </span>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* CTA */}
      <section className="border-t border-slate-800/60 px-5 py-16 sm:py-20">
        {/* 🟢 Encadré réajusté avec des bordures et fonds cohérents (slate-900/30) */}
        <div className="mx-auto flex max-w-7xl flex-col items-start justify-between gap-6 rounded-3xl border border-slate-800/50 bg-slate-900/30 backdrop-blur-sm p-8 sm:flex-row sm:items-center sm:p-10 lg:px-10">
          <div>
            <p className="font-mono text-xs font-bold uppercase tracking-[.3em] text-emerald-400">
              Un doute sur votre véhicule ?
            </p>
            <h2 className="mt-3 max-w-lg text-2xl font-black tracking-tight text-white sm:text-3xl">
              L&apos;atelier Mecano peut vous aider à faire le point.
            </h2>
          </div>
          <Link
            href="/contact"
            className="inline-flex flex-shrink-0 items-center gap-2 rounded-full bg-emerald-400 px-6 py-3.5 font-extrabold text-[#0A0D10] shadow-lg shadow-emerald-500/10 transition-all duration-200 hover:-translate-y-0.5 hover:bg-emerald-300 hover:shadow-emerald-500/20"
          >
            Échanger avec l&apos;atelier
            <ArrowUpRightIcon className="h-4 w-4" />
          </Link>
        </div>
      </section>
    </main>
  );
}
