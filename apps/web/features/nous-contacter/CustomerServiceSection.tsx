"use client";

import { useEffect, useRef, useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { motion } from "framer-motion";
import { Space_Grotesk, Inter, IBM_Plex_Mono } from "next/font/google";

const display = Space_Grotesk({
  subsets: ["latin"],
  weight: ["500", "700"],
  variable: "--font-display",
});
const body = Inter({
  subsets: ["latin"],
  weight: ["400", "500"],
  variable: "--font-body",
});
const mono = IBM_Plex_Mono({
  subsets: ["latin"],
  weight: ["400", "500"],
  variable: "--font-mono",
});

const EASE = [0.16, 1, 0.3, 1] as const;

function useInView<T extends HTMLElement>(threshold = 0.25) {
  const ref = useRef<T | null>(null);
  const [inView, setInView] = useState(false);
  const [reduced, setReduced] = useState(false);

  useEffect(() => {
    setReduced(window.matchMedia("(prefers-reduced-motion: reduce)").matches);

    const el = ref.current;
    if (!el) return;

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setInView(true);
          observer.disconnect();
        }
      },
      { threshold },
    );

    observer.observe(el);
    return () => observer.disconnect();
  }, [threshold]);

  return { ref, active: reduced || inView, animate: !reduced };
}

const headline = ["Décrivez votre besoin,", "on s'occupe du reste."];

const textVariants = {
  hidden: { opacity: 0, y: 30 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.75, ease: EASE },
  },
};

const imageVariants = {
  hidden: { opacity: 0, y: 40, scale: 0.97 },
  visible: {
    opacity: 1,
    y: 0,
    scale: 1,
    transition: { duration: 0.9, ease: EASE },
  },
};

export default function ContactSection() {
  const { ref, active, animate } = useInView<HTMLElement>(0.25);

  return (
    <section
      ref={ref}
      className={`${display.variable} ${body.variable} ${mono.variable} relative overflow-hidden bg-[#0A0D10] px-6 py-24 text-white sm:py-32`}
    >
      <div className="pointer-events-none absolute inset-0 opacity-[0.04] bg-[#101010]" />

      <div className="relative flex flex-col mx-auto sm:grid max-w-6xl grid-cols-1 items-center gap-12 lg:grid-cols-[1.05fr_0.95fr] lg:gap-10">
        {/* BLOC TEXTE : Passe en 2ème position sur mobile, reprend sa place (1ère) dès l'écran 'sm' */}
        <motion.div
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true, amount: 0.25 }}
          variants={textVariants}
          className="order-2 sm:order-1 w-full"
        >
          <h2 className="font-corinthia text-5xl leading-[1.06] tracking-tight sm:text-6xl lg:text-8xl">
            {headline.map((line, i) => (
              <span key={line} className="block overflow-hidden">
                <span
                  className="block transition-[transform,opacity] duration-[700ms]"
                  style={{
                    transitionTimingFunction: "cubic-bezier(0.16, 1, 0.3, 1)",
                    transitionDelay: animate ? `${150 + i * 90}ms` : "0ms",
                    transform: active ? "translateY(0%)" : "translateY(105%)",
                    opacity: active ? 1 : 0,
                  }}
                >
                  {line}
                </span>
              </span>
            ))}
          </h2>

          <div className="mt-6 overflow-hidden">
            <p
              className="max-w-md font-[family-name:var(--font-body)] text-lg leading-8 text-[#90999F] transition-[transform,opacity] duration-[700ms]"
              style={{
                transitionTimingFunction: "cubic-bezier(0.16, 1, 0.3, 1)",
                transitionDelay: animate ? "480ms" : "0ms",
                transform: active ? "translateY(0%)" : "translateY(100%)",
                opacity: active ? 1 : 0,
              }}
            >
              Un modèle précis, un budget, une réparation à prévoir : commençons
              par une conversation simple.
            </p>
          </div>

          <div className="mt-9 flex flex-wrap items-center gap-5 overflow-hidden">
            <div
              className="flex flex-wrap items-center gap-5 transition-[transform,opacity] duration-[700ms]"
              style={{
                transitionTimingFunction: "cubic-bezier(0.16, 1, 0.3, 1)",
                transitionDelay: animate ? "600ms" : "0ms",
                transform: active ? "translateY(0%)" : "translateY(100%)",
                opacity: active ? 1 : 0,
              }}
            >
              <Link
                href="/contact"
                className="inline-flex rounded-full bg-emerald-400 px-7 py-3.5 font-[family-name:var(--font-body)] font-semibold text-[#0A0D10] transition-colors hover:bg-emerald-300"
              >
                Contacter Mecano
              </Link>
            </div>
          </div>
        </motion.div>

        {/* BLOC IMAGE : Passe en 1ère position sur mobile, reprend sa place (2ème) dès l'écran 'sm' */}
        <motion.div
          variants={imageVariants}
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true, amount: 0.25 }}
          className="relative order-1 sm:order-2 w-full"
        >
          <div className="absolute -inset-4 rounded-[32px] bg-emerald-400/10 blur-2xl" />

          <div className="relative overflow-hidden rounded-[30px] border border-white/10 bg-[#12161B] shadow-[0_30px_80px_-20px_rgba(0,0,0,0.7)]">
            <div className="relative aspect-[4/5] sm:aspect-[5/4] lg:aspect-[4/5]">
              <Image
                src="/car.jpeg"
                alt="Illustration de contact"
                fill
                className="object-cover"
                priority
              />

              <div className="absolute inset-0 bg-gradient-to-t from-[#0A0D10] via-[#0A0D10]/20 to-transparent" />
            </div>
          </div>
        </motion.div>
      </div>
    </section>
  );
}
