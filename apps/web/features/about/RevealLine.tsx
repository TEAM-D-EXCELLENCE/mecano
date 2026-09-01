'use client';

import { forwardRef, useImperativeHandle, useRef } from 'react';
import { mapRange, smoothstep } from './utils';
import type { RevealHandle } from './RevealWords';

interface RevealLineProps {
  start: number;
  end: number;
  className?: string;
  color?: string;
}

const RevealLine = forwardRef<RevealHandle, RevealLineProps>(
  ({ start, end, className = '', color = 'rgba(255,255,255,0.25)' }, ref) => {
    const lineRef = useRef<HTMLDivElement | null>(null);

    useImperativeHandle(ref, () => ({
      apply: (globalProgress: number) => {
        const el = lineRef.current;
        if (!el) return;
        const t = smoothstep(0, 1, mapRange(globalProgress, start, end));
        el.style.transform = `scaleX(${t})`;
      },
    }));

    return (
      <div
        ref={lineRef}
        className={`h-px origin-left will-change-transform ${className}`}
        style={{ transform: 'scaleX(0)', backgroundColor: color }}
      />
    );
  }
);

RevealLine.displayName = 'RevealLine';
export default RevealLine;