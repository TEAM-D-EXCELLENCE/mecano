import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { posts } from "@/lib/mock-data";

export const metadata: Metadata = {
  title: "Conseils auto",
  description: "Les conseils Mecano pour acheter et entretenir votre véhicule.",
};

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

const formatRef = (index: number) =>
  `FICHE N° ${String(index + 1).padStart(2, "0")}`;

export default function BlogPage() {
  const [featured, ...rest] = posts;

  return (
    <>
      <section className="relative isolate overflow-hidden h-auto sm:h-[80vh] border-b border-white/10 bg-[#ECFDF5] px-5 py-16 text-white sm:py-20">
        <Image
          src="/blogHero.jpg"
          alt="hero blog Mecano"
          fill
          priority
          className="absolute inset-0 -z-20 object-cover object-center"
        />

        <div className="absolute inset-0 -z-10 bg-slate-950/10" />

        {/* Remplacement du grid par un flex-col de pleine hauteur (h-full) sur desktop */}
        <div className="mx-auto flex flex-col justify-between h-full max-w-7xl lg:px-8">
          {/* Titre (reste en haut à gauche par défaut) */}
          <div>
            <h1 className="mt-4 max-w-xl text-4xl font-black leading-[1.05] tracking-tight text-white sm:text-6xl">
              Ce que nos techniciens ont appris sous le capot.
            </h1>
          </div>

          {/* Paragraphe poussé en bas (grâce à justify-between) et aligné tout à droite */}
          <div className="mt-10 lg:mt-0 lg:self-end">
            <p className="max-w-md border-r-2 border-emerald-700 pr-5 text-right leading-7 text-slate-200">
              Des conseils utiles, sans jargon superflu, écrits par les
              personnes qui interviennent réellement sur votre véhicule.
            </p>
          </div>
        </div>
      </section>

      <main className="bg-slate-950 pb-20 pt-16 sm:pb-28">
        <div className="mx-auto max-w-7xl px-5 lg:px-8">
          {/* Article vedette */}
          {featured && (
            <Link
              href={`/blog/${featured.slug}`}
              className="group grid gap-0 overflow-hidden rounded-3xl border border-white/10 bg-slate-900/60 transition hover:border-emerald-400/30 lg:grid-cols-2"
            >
              <div className="relative aspect-[16/10] overflow-hidden lg:aspect-auto">
                <Image
                  src={featured.image}
                  alt={featured.title}
                  fill
                  priority
                  sizes="(min-width: 1024px) 50vw, 100vw"
                  className="object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                />
              </div>
              <div className="flex flex-col justify-center p-6 sm:p-10">
                <h2 className="mt-5 text-2xl font-black leading-tight tracking-tight text-white sm:text-3xl">
                  {featured.title}
                </h2>
                <p className="mt-3 max-w-md leading-6 text-slate-400">
                  {featured.excerpt}
                </p>
                <span className="mt-6 inline-flex w-fit items-center gap-2 font-bold text-emerald-300">
                  Lire l&apos;article
                  <ArrowUpRightIcon className="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1 group-hover:-translate-y-1" />
                </span>
              </div>
            </Link>
          )}

          {/* Grille des autres articles, en "fiches" */}
          <div className="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {rest.map((post, i) => (
              <Link
                key={post.slug}
                href={`/blog/${post.slug}`}
                className="group flex flex-col overflow-hidden rounded-3xl border border-white/10 bg-slate-900/60 transition hover:border-emerald-400/30"
              >
                <div className="relative aspect-[16/10] overflow-hidden">
                  <Image
                    src={post.image}
                    alt={post.title}
                    fill
                    sizes="(min-width: 1024px) 33vw, (min-width: 640px) 45vw, 90vw"
                    className="object-cover grayscale transition-all duration-700 ease-out group-hover:scale-105 group-hover:grayscale-0"
                  />
                </div>
                <div className="flex flex-1 flex-col p-5 sm:p-6">
                  <h3 className="mt-3 text-lg font-bold leading-snug text-white">
                    {post.title}
                  </h3>
                  <p className="mt-2 line-clamp-2 flex-1 text-sm leading-6 text-slate-400">
                    {post.excerpt}
                  </p>
                  <span className="mt-4 inline-flex items-center gap-1.5 text-sm font-bold text-emerald-300">
                    Lire
                    <ArrowUpRightIcon className="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-1 group-hover:-translate-y-1" />
                  </span>
                </div>
              </Link>
            ))}
          </div>

          {/* CTA */}
          <div className="mt-16 flex flex-col items-start justify-between gap-6 rounded-3xl border border-white/10 bg-slate-900/60 p-8 sm:flex-row sm:items-center sm:p-10">
            <div>
              <p className="font-mono text-xs font-bold uppercase tracking-[.3em] text-emerald-300">
                Une question précise ?
              </p>
              <h2 className="mt-3 max-w-lg text-2xl font-black tracking-tight text-white sm:text-3xl">
                Le meilleur conseil commence par écouter votre situation.
              </h2>
            </div>
            <Link
              href="/contact"
              className="inline-flex flex-shrink-0 items-center gap-2 rounded-full bg-emerald-400 px-6 py-3.5 font-extrabold text-slate-950 transition hover:bg-emerald-300"
            >
              Contacter l&apos;atelier
              <ArrowUpRightIcon className="h-4 w-4" />
            </Link>
          </div>
        </div>
      </main>
    </>
  );
}
