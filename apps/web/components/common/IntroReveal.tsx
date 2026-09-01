"use client";

import { useEffect, useState } from "react";

const INTRO_DURATION = 2_100;

/** Blocks the page until the opening capsule has revealed the hero. */
export function IntroReveal() {
  const [complete, setComplete] = useState(false);

  useEffect(() => {
    const previousOverflow = document.body.style.overflow;
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const duration = reducedMotion.matches ? 0 : INTRO_DURATION;

    document.body.style.overflow = "hidden";
    const timer = window.setTimeout(() => {
      document.body.style.overflow = previousOverflow;
      setComplete(true);
    }, duration);

    return () => {
      window.clearTimeout(timer);
      document.body.style.overflow = previousOverflow;
    };
  }, []);

  if (complete) return null;

  return (
    <div className="intro-reveal" aria-hidden="true">
      <div className="intro-reveal__pill" />
    </div>
  );
}
