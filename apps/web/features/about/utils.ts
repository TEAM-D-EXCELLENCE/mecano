export const clamp = (v: number, a: number, b: number) => (v < a ? a : v > b ? b : v);

export const smoothstep = (edge0: number, edge1: number, x: number) => {
  const t = clamp((x - edge0) / (edge1 - edge0 || 1e-6), 0, 1);
  return t * t * (3 - 2 * t);
};

export const mapRange = (v: number, inMin: number, inMax: number) =>
  clamp((v - inMin) / (inMax - inMin || 1e-6), 0, 1);

const hexToRgb = (hex: string) => {
  const h = hex.replace('#', '');
  const full = h.length === 3 ? h.split('').map((c) => c + c).join('') : h;
  const n = parseInt(full, 16);
  return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255 };
};

// Interpolates between two colors. Accepts hex ("#fff") or rgba() strings for `to`/`from`
// as long as at least one side stays hex; for two rgba inputs, pass them already resolved.
export const lerpColor = (from: string, to: string, t: number) => {
  const parse = (c: string) => {
    if (c.startsWith('#')) return { ...hexToRgb(c), a: 1 };
    const m = c.match(/rgba?\(([^)]+)\)/);
    if (!m) return { r: 255, g: 255, b: 255, a: 1 };
    const [r, g, b, a = 1] = m[1].split(',').map((n) => parseFloat(n));
    return { r, g, b, a: Number(a) };
  };
  const a = parse(from);
  const b = parse(to);
  const r = Math.round(a.r + (b.r - a.r) * t);
  const g = Math.round(a.g + (b.g - a.g) * t);
  const bl = Math.round(a.b + (b.b - a.b) * t);
  const alpha = a.a + (b.a - a.a) * t;
  return `rgba(${r}, ${g}, ${bl}, ${alpha})`;
};