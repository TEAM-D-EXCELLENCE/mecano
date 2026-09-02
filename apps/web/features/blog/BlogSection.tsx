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
  hidden: {
    opacity: 0,
    y: 35,
    scale: 0.96,
  },
  visible: {
    opacity: 1,
    y: 0,
    scale: 1,
    transition: {
      duration: 0.7,
      ease: [0.22, 1, 0.36, 1] as const,
    },
  },
};

export default function BlogSection({ posts }: { posts: Post[] }) {
  return (
    <section className="relative overflow-hidden bg-[#f5f1e8] py-12 sm:py-16">
      <div className="mx-auto max-w-[1450px] px-4 sm:px-6 lg:px-8">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, amount: 0.25 }}
          transition={{
            duration: 0.7,
            ease: [0.22, 1, 0.36, 1] as const,
          }}
          className="relative min-h-[550px] overflow-hidden rounded-[28px] sm:min-h-[700px]"
        >
            <Image
              src="/reflexes/cover.png"
              alt=""
              fill
              priority
              className="object-cover object-center"
            />

          {/* Voiles de contraste */}
          <div className="absolute inset-0 bg-black/10" />
          <div className="absolute inset-0 bg-gradient-to-b from-black/35 via-black/5 to-black/20" />

          {/* Texte principal */}
          <div className="relative z-10 max-w-3xl px-7 pt-10 sm:px-12 sm:pt-14 lg:px-16 lg:pt-16">
            <h2 className="font-serif text-5xl font-normal leading-[0.95] tracking-[-0.04em] text-white drop-shadow-md sm:text-6xl lg:text-7xl">
              Les bons réflexes,
              <br />
              <span className="font-corinthia text-6xl sm:text-9xl font-medium">simplement</span>
            </h2>
          </div>

          {/* Bouton */}
          <Link
            href="/blog"
            className="group absolute right-5 top-60 z-20 inline-flex items-center gap-3 rounded-full bg-[#A68E37] duration-300 px-6 py-3.5 text-sm font-medium tracking-wide text-white shadow-lg transition-all hover:-translate-y-1 hover:bg-black hover:text-[#A68E37] sm:right-10 sm:top-90 sm:bottom-70 sm:px-8"
          >
            Tout nos conseils
          </Link>

          {/* Séparateur */}
          <div className="absolute bottom-[235px] left-7 right-7 z-10 h-px bg-white/45 sm:bottom-[250px] sm:left-12 sm:right-12 lg:left-16 lg:right-16" />

          {/* Articles en bas */}
          {/* Conseils en scroll horizontal */}
          <div className="absolute bottom-0 left-7 sm:left-10 right-0 z-10">
            <motion.div
              variants={containerVariants}
              initial="hidden"
              whileInView="visible"
              viewport={{ once: true, amount: 0.2 }}
              className="
      flex
      snap-x
      snap-mandatory
      gap-4
      overflow-x-auto
      px-5
      pb-5
      sm:px-8
      sm:pb-8
      lg:px-10
      lg:pb-10
      [scrollbar-width:none]
      [-ms-overflow-style:none]
      [&::-webkit-scrollbar]:hidden
    "
            >
              {posts.map((post) => (
                <motion.article
                  key={post.slug}
                  variants={cardVariants}
                  className="
          group
          w-[calc(100vw-40px)]
          min-w-[calc(100vw-40px)]
          shrink-0
          snap-start
          overflow-hidden
          rounded-[22px]
          bg-white/90
          shadow-xl
          backdrop-blur-sm
          transition-all
          duration-500
          hover:-translate-y-2
          sm:w-[420px]
          sm:min-w-[420px]
          lg:w-[390px]
          lg:min-w-[390px]
        "
                >
                  <Link
                    href={`/blog/${post.slug}`}
                    className="grid min-h-[145px] grid-cols-[110px_1fr] items-stretch sm:grid-cols-[130px_1fr]"
                  >
                    <div className="relative overflow-hidden">
                      <Image
                        src={post.image}
                        alt={post.title}
                        fill
                        className="object-cover transition-transform duration-700 group-hover:scale-110"
                      />
                    </div>

                    <div className="flex flex-col justify-between p-4 sm:p-5">
                      <div>
                        <h3 className="line-clamp-3 text-base font-semibold leading-tight text-[#183b38] sm:text-lg">
                          {post.title}
                        </h3>
                      </div>

                      <div className="mt-4 flex items-center justify-between">
                        <span className="text-xs text-slate-500">
                          Lire l’article
                        </span>

                        <span className="grid h-9 w-9 place-items-center rounded-full bg-[#183b38] text-white transition-transform duration-300 group-hover:translate-x-1">
                          →
                        </span>
                      </div>
                    </div>
                  </Link>
                </motion.article>
              ))}
            </motion.div>
          </div>
        </motion.div>
      </div>
    </section>
  );
}
