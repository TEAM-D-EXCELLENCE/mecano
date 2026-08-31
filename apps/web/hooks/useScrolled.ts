import { useState, useEffect } from "react";

/**
 * Returns `true` once the page has scrolled beyond the given threshold.
 * @param threshold Number of pixels before activating the scrolled state.
 */
export function useScrolled(threshold: number = 500): boolean {
  const [scrolled, setScrolled] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > threshold);
    };

    // Read the initial state in case the page was restored at a scroll position.
    handleScroll();

    window.addEventListener("scroll", handleScroll, { passive: true });
    return () => window.removeEventListener("scroll", handleScroll);
  }, [threshold]);

  return scrolled;
}
