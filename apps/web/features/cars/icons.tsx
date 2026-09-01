interface IconProps {
  className?: string;
}

export function GaugeIcon({ className }: IconProps) {
  return (
    <svg viewBox="0 0 24 24" fill="none" className={className}>
      <path d="M4 15a8 8 0 1 1 16 0" stroke="currentColor" strokeWidth="1.5" />
      <path
        d="M12 15l4-5"
        stroke="currentColor"
        strokeWidth="1.5"
        strokeLinecap="round"
      />
      <circle cx="12" cy="15" r="1.2" fill="currentColor" />
    </svg>
  );
}

export function SpeedIcon({ className }: IconProps) {
  return (
    <svg viewBox="0 0 24 24" fill="none" className={className}>
      <circle cx="12" cy="12" r="8" stroke="currentColor" strokeWidth="1.5" />
      <path
        d="M12 12l3.5-3.5"
        stroke="currentColor"
        strokeWidth="1.5"
        strokeLinecap="round"
      />
      <path
        d="M12 6v1.2M12 16.8V18M6 12h1.2M16.8 12H18"
        stroke="currentColor"
        strokeWidth="1.2"
        strokeLinecap="round"
      />
    </svg>
  );
}

export function CalendarIcon({ className }: IconProps) {
  return (
    <svg viewBox="0 0 24 24" fill="none" className={className}>
      <rect
        x="4"
        y="5.5"
        width="16"
        height="14"
        rx="2"
        stroke="currentColor"
        strokeWidth="1.5"
      />
      <path
        d="M4 9.5h16M8 3.5v3M16 3.5v3"
        stroke="currentColor"
        strokeWidth="1.5"
        strokeLinecap="round"
      />
    </svg>
  );
}

export function StatIcon({
  kind,
  className,
}: {
  kind: "gauge" | "speed" | "calendar";
  className?: string;
}) {
  if (kind === "gauge") return <GaugeIcon className={className} />;
  if (kind === "speed") return <SpeedIcon className={className} />;
  return <CalendarIcon className={className} />;
}
