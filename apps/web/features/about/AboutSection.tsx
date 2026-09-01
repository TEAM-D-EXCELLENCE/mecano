'use client';

import { useEffect, useRef } from 'react';
import RevealWords, { type RevealHandle } from './RevealWords';
import RevealLine from './RevealLine';
import { clamp } from './utils';

const ACCENT = '#c7d332';

export default function AboutSection() {
  const sectionRef = useRef<HTMLDivElement | null>(null);

  const accentLineRef = useRef<RevealHandle>(null);
  const labelRef = useRef<RevealHandle>(null);
  const paragraphRef = useRef<RevealHandle>(null);
  const dividerRef = useRef<RevealHandle>(null);
  const smallParagraphRef = useRef<RevealHandle>(null);

  useEffect(() => {
    const section = sectionRef.current;
    if (!section) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let raf = 0;
    let current = 0;
    let target = 0;
    let running = false;

    const applyProgress = (p: number) => {
      accentLineRef.current?.apply(p);
      labelRef.current?.apply(p);
      paragraphRef.current?.apply(p);
      dividerRef.current?.apply(p);
      smallParagraphRef.current?.apply(p);
    };

    // Progress starts when the section reaches 90% of the viewport and ends at 30%.
    const readProgress = () => {
      const rect = section.getBoundingClientRect();
      const vh = window.innerHeight;
      const startAt = vh * 0.9;
      const endAt = vh * 0.3;
      return clamp((startAt - rect.top) / (startAt - endAt), 0, 1);
    };

    const tick = () => {
      const k = 0.16;
      current += (target - current) * k;
      if (Math.abs(target - current) < 0.0008) {
        current = target;
        running = false;
      }
      applyProgress(current);
      raf = running ? requestAnimationFrame(tick) : 0;
    };

    const kick = () => {
      if (running) return;
      running = true;
      if (!raf) raf = requestAnimationFrame(tick);
    };

    const onScroll = () => {
      target = readProgress();
      if (reduceMotion) {
        current = target;
        applyProgress(current);
        return;
      }
      kick();
    };

    const onResize = () => {
      target = readProgress();
      current = target;
      applyProgress(current);
    };

    target = readProgress();
    current = target;
    applyProgress(current);

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onResize);

    return () => {
      if (raf) cancelAnimationFrame(raf);
      window.removeEventListener('scroll', onScroll);
      window.removeEventListener('resize', onResize);
    };
  }, []);

  return (
    <section ref={sectionRef} className="bg-[#0b0b0c] px-6 py-24 sm:px-10 lg:px-16 lg:py-32">
      <div className="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[200px_1fr] lg:gap-16">
        {/* Section label */}
        <div className="flex items-center gap-3 lg:pt-3">
          <RevealLine ref={accentLineRef} start={0} end={0.12} className="w-9" color={ACCENT} />
          <RevealWords
            ref={labelRef}
            text="À PROPOS"
            start={0.02}
            end={0.18}
            className="text-sm font-semibold uppercase tracking-[0.25em] text-white"
          />
        </div>

        {/* Main paragraph */}
        <div>
          <RevealWords
            ref={paragraphRef}
            text="Des véhicules fiables aux modèles d'exception, Mecano vous propose une sélection exigeante et un accompagnement personnalisé à chaque étape."
            start={0.08}
            end={0.85}
            className="block text-3xl font-bold leading-[1.15] tracking-tight sm:text-4xl lg:text-5xl"
          />

          <div className="mt-14 grid gap-6 sm:grid-cols-[1fr_320px] sm:items-start sm:gap-10">
            <RevealLine ref={dividerRef} start={0.7} end={0.95} className="mt-3 w-full" />
            <RevealWords
              ref={smallParagraphRef}
              text="Notre équipe vous aide à trouver le véhicule qui correspond à vos besoins, puis assure son entretien avec transparence. Diagnostic, révision, carrosserie ou conseil : tout est réuni pour rouler l'esprit tranquille."
              start={0.78}
              end={1}
              className="block text-base leading-7"
              dimColor="rgba(255,255,255,0.12)"
              litColor="rgba(255,255,255,0.72)"
            />
          </div>
        </div>
      </div>
    </section>
  );
}
