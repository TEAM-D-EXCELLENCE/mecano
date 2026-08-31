'use client';

import { useEffect, useRef } from 'react';
import { cars } from './CarsData';
import { BrandTitle, CarVisual, CarInfoOverlay } from './CarLayers';

const ACCENT = '#f4c744';

// Distance, as a percentage of the stage width, between consecutive cars.
// Values below 100 keep the adjacent cars visible at the edges.
const CAR_SPACING = 68;

const clamp = (v: number, a: number, b: number) => (v < a ? a : v > b ? b : v);
const smoothstep = (edge0: number, edge1: number, x: number) => {
  const t = clamp((x - edge0) / (edge1 - edge0 || 1e-6), 0, 1);
  return t * t * (3 - 2 * t);
};

export default function CarShowcase() {
  const trackRef = useRef<HTMLDivElement | null>(null);
  const stageRef = useRef<HTMLDivElement | null>(null);

  const textRefs = useRef<(HTMLDivElement | null)[]>([]);
  const carRefs = useRef<(HTMLDivElement | null)[]>([]);
  const infoRefs = useRef<(HTMLDivElement | null)[]>([]);

  const count = cars.length;

  useEffect(() => {
    const track = trackRef.current;
    const stage = stageRef.current;
    if (!track || !stage) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let raf = 0;
    let current = 0;
    let target = 0;
    let running = false;
    let stageH = 0;

    const measure = () => {
      stageH = window.innerHeight;
      stage.style.height = `${stageH}px`;
      // Use one viewport height of scroll for every transition between cars.
      track.style.height = `${stageH * count}px`;
    };

    const applyProgress = (p: number) => {
      for (let i = 0; i < count; i++) {
        const textEl = textRefs.current[i];
        const carEl = carRefs.current[i];
        const infoEl = infoRefs.current[i];
        if (!textEl || !carEl || !infoEl) continue;

        const delta = i - p;
        const abs = Math.abs(delta);

        // Compact spacing keeps the adjacent car visible at the edge.
        const carX = delta * CAR_SPACING;
        const carScale = 1 - Math.min(abs, 1) * 0.06;
        carEl.style.transform = `translate3d(${carX}%, 0, 0) scale(${carScale})`;
        carEl.style.filter = `brightness(${clamp(1 - abs * 0.5, 0.45, 1)})`;

        // Brand and information layers are full width and only appear while crossing the center.
        // Sharing the same delta keeps them synchronized with the car movement.
        const fullX = delta * 100;
        const fadeOpacity = 1 - smoothstep(0, 0.6, abs);

        textEl.style.transform = `translate3d(${fullX}%, 0, 0)`;
        textEl.style.opacity = `${fadeOpacity}`;

        infoEl.style.transform = `translate3d(${fullX}%, 0, 0)`;
        infoEl.style.opacity = `${fadeOpacity}`;
        infoEl.style.pointerEvents = abs < 0.05 ? 'auto' : 'none';
      }
    };

    const readProgress = () => {
      const top = track.getBoundingClientRect().top;
      return clamp(-top / stageH, 0, count - 1);
    };

    const tick = () => {
      const k = 0.14;
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
      measure();
      target = readProgress();
      current = target;
      applyProgress(current);
    };

    measure();
    target = readProgress();
    current = target;
    applyProgress(current);

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onResize);
    const ro = new ResizeObserver(onResize);
    ro.observe(document.documentElement);

    return () => {
      if (raf) cancelAnimationFrame(raf);
      window.removeEventListener('scroll', onScroll);
      window.removeEventListener('resize', onResize);
      ro.disconnect();
    };
  }, [count]);

  return (
    <div ref={trackRef} className="relative w-full">
      <div ref={stageRef} className="sticky top-0 w-full overflow-hidden bg-[#07191E]">
        {/* Halo ambiant */}
        <div className="pointer-events-none absolute inset-0 z-0 bg-[radial-gradient(ellipse_at_center,rgba(255,255,255,0.06),rgba(0,0,0,0)_55%)]" />

        {/* Couche 1 : grands noms de marque (derrière les voitures) */}
        <div className="absolute inset-0 z-10">
          {cars.map((car, i) => (
            <BrandTitle key={car.id} car={car} innerRef={(el) => (textRefs.current[i] = el)} />
          ))}
        </div>

        {/* Couche 2 : voitures */}
        <div className="absolute inset-0 z-20">
          {cars.map((car, i) => (
            <CarVisual key={car.id} car={car} innerRef={(el) => (carRefs.current[i] = el)} />
          ))}
        </div>

        {/* Couche 3 : catégorie, logo, panneau bas (devant les voitures) */}
        <div className="absolute inset-0 z-30">
          {cars.map((car, i) => (
            <CarInfoOverlay
              key={car.id}
              car={car}
              accent={ACCENT}
              innerRef={(el) => (infoRefs.current[i] = el)}
            />
          ))}
        </div>

        {/* Vignette latérale : assombrit les voitures qui "peekent" sur les bords */}
        <div className="pointer-events-none absolute inset-0 z-40 bg-[linear-gradient(to_right,rgba(11,11,12,0.95),rgba(11,11,12,0)_18%,rgba(11,11,12,0)_82%,rgba(11,11,12,0.95))]" />
      </div>
    </div>
  );
}
