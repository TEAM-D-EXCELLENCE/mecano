'use client';

import { forwardRef, useImperativeHandle, useMemo, useRef } from 'react';
import { mapRange, smoothstep, lerpColor } from './utils';

export interface RevealHandle {
  apply: (globalProgress: number) => void;
}

interface RevealWordsProps {
  text: string;
  start: number; // point de progression globale [0,1] où ce bloc commence à se révéler
  end: number;   // point où il est entièrement révélé
  className?: string;
  dimColor?: string; // couleur des mots pas encore "lus"
  litColor?: string; // couleur des mots révélés
}

const RevealWords = forwardRef<RevealHandle, RevealWordsProps>(
  ({ text, start, end, className = '', dimColor = 'rgba(255,255,255,0.16)', litColor = '#ffffff' }, ref) => {
    const words = useMemo(() => text.split(' '), [text]);
    const wordRefs = useRef<(HTMLSpanElement | null)[]>([]);

    useImperativeHandle(ref, () => ({
      apply: (globalProgress: number) => {
        const localP = mapRange(globalProgress, start, end);
        const n = words.length;
        // fenêtre de révélation par mot : petite mais qui se chevauche pour rester fluide
        const windowSize = Math.min(0.4, 4 / n);
        for (let i = 0; i < n; i++) {
          const el = wordRefs.current[i];
          if (!el) continue;
          const wStart = (i / Math.max(1, n - 1)) * (1 - windowSize);
          const t = smoothstep(0, 1, mapRange(localP, wStart, wStart + windowSize));
          el.style.transform = `translate3d(0, ${(1 - t) * 105}%, 0)`;
          el.style.color = lerpColor(dimColor, litColor, t);
        }
      },
    }));

    return (
      <span className={className}>
        {words.map((word, i) => (
          <span key={i}>
            <span className="inline-block overflow-hidden pb-[0.12em] align-bottom">
              <span
                ref={(el) => {
                  wordRefs.current[i] = el;
                }}
                className="inline-block will-change-transform"
                style={{ transform: 'translate3d(0, 105%, 0)', color: dimColor }}
              >
                {word}
              </span>
            </span>
            {i < words.length - 1 ? ' ' : ''}
          </span>
        ))}
      </span>
    );
  }
);

RevealWords.displayName = 'RevealWords';
export default RevealWords;
