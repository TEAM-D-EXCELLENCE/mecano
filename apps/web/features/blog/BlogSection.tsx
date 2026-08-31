"use client";

import Link from "next/link";
import { motion, type Variants } from "framer-motion";
import Image from "next/image";
import type { Post } from "@/lib/mock-data";

const containerVariants: Variants = {
  hidden: {},
  visible: {
    transition: {
      staggerChildren: 0.12,
    },
  },
};

const cardVariants: Variants = {
  hidden: { opacity: 0, y: 40, scale: 0.96, filter: "blur(6px)" },
  visible: {
    opacity: 1,
    y: 0,
    scale: 1,
    filter: "blur(0px)",
    transition: {
      duration: 0.75,
      ease: [0.22, 1, 0.36, 1] as const,
    },
  },
};

export default function BlogSection({ posts }: { posts: Post[] }) {
  return (
    <section className="relative overflow-hidden py-24">
      <div className="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top,rgba(16,185,129,0.16),transparent_35%),linear-gradient(to_bottom,rgba(2,6,23,0.96),rgba(15,23,42,0.96))]" />

      <div className="mx-auto max-w-7xl px-5 lg:px-8">
        <motion.div
          initial={{ opacity: 0, y: 24 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, amount: 0.3 }}
          transition={{ duration: 0.7, ease: [0.22, 1, 0.36, 1] as const }}
          className="flex flex-col gap-6 md:flex-row md:items-end md:justify-between"
        >
          <div className="max-w-2xl">
            <h2 className="mt-5 text-4xl font-black tracking-tight text-white sm:text-5xl">
              Les bons réflexes, simplement.
            </h2>

            <p className="mt-4 max-w-xl text-sm leading-6 text-slate-300 sm:text-base">
              Des contenus clairs, utiles et pensés pour vous aider à avancer
              plus vite avec les bonnes pratiques.
            </p>
          </div>

          <Link
            href="/blog"
            className="group inline-flex w-fit items-center gap-2 rounded-full border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold text-white/80 backdrop-blur-md transition-all duration-300 hover:border-emerald-400/30 hover:bg-[#006633]/10 hover:text-white hover:shadow-[0_0_0_1px_rgba(16,185,129,0.15),0_20px_60px_rgba(16,185,129,0.12)]"
          >
            Tous nos conseils
            <span className="transition-transform duration-300 group-hover:translate-x-1">
              →
            </span>
          </Link>
        </motion.div>

        <motion.div
          variants={containerVariants}
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true, amount: 0.15 }}
          className="mt-12 grid gap-6 md:grid-cols-2 xl:grid-cols-3"
        >
          {posts.map((post) => (
            <motion.article
              key={post.slug}
              variants={cardVariants}
              className="group relative overflow-hidden rounded-[28px] border border-white/10 bg-white/5 p-4 backdrop-blur-xl transition-all duration-500 hover:-translate-y-2 hover:border-emerald-400/20 hover:bg-white/8 hover:shadow-[0_24px_80px_rgba(0,0,0,0.35)]"
            >
              <div className="absolute inset-0 bg-gradient-to-br from-emerald-400/0 via-white/0 to-emerald-400/10 opacity-0 transition-opacity duration-500 group-hover:opacity-100" />

              <Link
                href={`/blog/${post.slug}`}
                className="relative block overflow-hidden rounded-[22px] bg-slate-900/60"
              >
                <div className="relative aspect-[16/10] overflow-hidden">
                  <Image
                    src={post.image}
                    alt={post.title}
                    fill
                    className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent" />
                  <div className="absolute left-4 top-4">
                    <span className="rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-white/90 backdrop-blur-md">
                      Lecture
                    </span>
                  </div>
                </div>

                <div className="space-y-4 p-5">
                  <div className="flex items-center gap-3 text-xs text-slate-400">
                    <span className="h-1.5 w-1.5 rounded-full bg-[#006633]" />
                    <span>Conseil pratique</span>
                    <span className="h-1 w-1 rounded-full bg-slate-600" />
                    <span>5 min</span>
                  </div>

                  <h3 className="text-xl font-bold leading-tight text-white transition-colors duration-300 group-hover:text-emerald-300">
                    {post.title}
                  </h3>

                  <p className="line-clamp-3 text-sm leading-6 text-slate-300">
                    {post.excerpt}
                  </p>

                  <div className="flex items-center justify-between pt-2">
                    <span className="text-sm font-medium text-slate-400">
                      Lire l’article
                    </span>

                    <span className="grid h-11 w-11 place-items-center rounded-full border border-white/10 bg-white/5 text-white transition-all duration-300 group-hover:border-emerald-400/30 group-hover:bg-[#006633]/15 group-hover:translate-x-1">
                      →
                    </span>
                  </div>
                </div>
              </Link>
            </motion.article>
          ))}
        </motion.div>
      </div>
    </section>
  );
}
