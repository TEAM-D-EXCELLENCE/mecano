"use client";

import { useEffect, useRef, useState } from "react";
import type { CSSProperties, ReactNode } from "react";
import Image from "next/image";
import { Header } from "../layout/Header";

interface ScrollExpandProps {
  src: string;
  alt: string;
  title?: string;
  scrollHint?: string;
  startWidth?: number;
  startHeight?: number;
  startRadius?: number;
  endRadius?: number;
  mediaZoom?: number;
  scrollDistance?: number;
  overlayScrim?: number;
  children?: ReactNode;
  className?: string;
  style?: CSSProperties;
}

const clamp = (value: number) => Math.min(1, Math.max(0, value));

/**
 * React Bits-inspired scroll expansion, isolated as a client leaf so the page
 * around it can stay server-rendered and cacheable.
 */
export default function ScrollExpand({
  src,
  alt,
  title,
  scrollHint,
  startWidth = 52,
  startHeight = 60,
  startRadius = 24,
  endRadius = 0,
  mediaZoom = 1.35,
  scrollDistance = 1.2,
  overlayScrim = 0.45,
  children,
  className = "",
  style,
}: ScrollExpandProps) {
  const trackRef = useRef<HTMLElement>(null);
  const [progress, setProgress] = useState(0);
  const [headerBackgroundThreshold, setHeaderBackgroundThreshold] =
    useState(Infinity);

  useEffect(() => {
    let frameId = 0;
    const update = () => {
      const track = trackRef.current;
      if (!track) return;
      const range = Math.max(1, track.offsetHeight - window.innerHeight);
      // The header first appears at the end of this range. Keep it transparent
      // briefly, then start its background transition after 96 additional pixels.
      setHeaderBackgroundThreshold(track.offsetTop + range + 96);
      setProgress(clamp(-track.getBoundingClientRect().top / range));
    };
    const onScroll = () => {
      cancelAnimationFrame(frameId);
      frameId = requestAnimationFrame(update);
    };
    const motion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const onMotionChange = () => {
      if (motion.matches) setProgress(1);
      else update();
    };
    update();
    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("resize", onScroll);
    motion.addEventListener("change", onMotionChange);
    return () => {
      cancelAnimationFrame(frameId);
      window.removeEventListener("scroll", onScroll);
      window.removeEventListener("resize", onScroll);
      motion.removeEventListener("change", onMotionChange);
    };
  }, []);

  const width = startWidth + (100 - startWidth) * progress;
  const height = startHeight + (100 - startHeight) * progress;
  const radius = startRadius + (endRadius - startRadius) * progress;
  const zoom = mediaZoom + (1 - mediaZoom) * progress;
  const titleOpacity = 1 - clamp((progress - 0.38) / 0.45);
  const overlayOpacity = clamp((progress - 0.65) / 0.3);
  const hasExpanded = progress >= 0.99;

  return (
    <>
      <section
      ref={trackRef}
      className={`relative ${className}`}
      style={{ minHeight: `${(1 + scrollDistance) * 100}svh`, ...style }}
      aria-label={title ?? "Présentation"}
    >
      <div className="sticky top-0 grid h-svh place-items-center overflow-hidden bg-white">
        <div
          className="relative overflow-hidden shadow-2xl shadow-black/40"
          style={{
            width: `${width}%`,
            height: `${height}%`,
            borderRadius: `${radius}px`,
            transition:
              "width 80ms linear, height 80ms linear, border-radius 80ms linear",
          }}
        >
          <Image
            src={src}
            alt={alt}
            fill
            priority
            sizes="100vw"
            className="object-cover"
            style={{
              transform: `scale(${zoom})`,
              transition: "transform 80ms linear",
            }}
          />
          <div
            className="absolute inset-0 bg-slate-950"
            style={{ opacity: overlayScrim * progress }}
          />
          {children && (
            <div
              className="absolute inset-0 grid place-items-center p-6 text-center"
              style={{
                opacity: overlayOpacity,
                transform: `translateY(${(1 - overlayOpacity) * 16}px)`,
                transition: "opacity 120ms linear, transform 120ms linear",
              }}
            >
              {children}
            </div>
          )}
        </div>
        {title && (
          <h2
            aria-label={title}
            className="pointer-events-none absolute w-68 sm:w-full px-6 text-center text-[2.7rem] font-corinthia leading-none tracking-tight text-white drop-shadow-2xl sm:text-7xl lg:text-[10rem]"
            style={{
              opacity: titleOpacity,
              transform: `translateY(${-28 * (1 - titleOpacity)}px)`,
              transition: "opacity 100ms linear, transform 100ms linear",
            }}
          >
            {Array.from(title).map((character, index) => (
              <span
                key={`${character}-${index}`}
                aria-hidden="true"
                className="intro-title-letter"
                style={{ animationDelay: `${1.55 + index * 0.045}s` }}
              >
                {character === " " ? "\u00a0" : character}
              </span>
            ))}
          </h2>
        )}
        {scrollHint && (
          <p
            className="pointer-events-none absolute bottom-8 text-sm font-semibold tracking-wide text-white/70"
            style={{ opacity: 1 - clamp(progress / 0.15) }}
          >
            {scrollHint} ↓
          </p>
        )}
      </div>
      </section>
      <Header
        isFixed={hasExpanded}
        backgroundThreshold={headerBackgroundThreshold}
      />
    </>
  );
}
