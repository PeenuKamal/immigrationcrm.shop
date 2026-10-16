import { useId } from "react";

/** Circular ink stamp — the site's signature mark for verified reviews. */
export function Stamp({
  ring = "VERIFIED REVIEW • IMMIGRATIONCRM.SHOP • ",
  center = "VERIFIED",
  sub,
  className,
  title = "Verified review",
}: {
  ring?: string;
  center?: string;
  sub?: string;
  className?: string;
  title?: string;
}) {
  const id = useId().replace(/:/g, "");
  return (
    <svg viewBox="0 0 200 200" className={className} role="img" aria-label={title}>
      <defs>
        <path id={`ring-${id}`} d="M100,100 m-74,0 a74,74 0 1,1 148,0 a74,74 0 1,1 -148,0" />
        <filter id={`ink-${id}`}>
          <feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" seed="3" result="n" />
          <feColorMatrix in="n" type="matrix" values="0 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 -2.2 1.6" result="mask" />
          <feComposite in="SourceGraphic" in2="mask" operator="in" />
        </filter>
      </defs>
      <g filter={`url(#ink-${id})`} fill="none" stroke="currentColor">
        <circle cx="100" cy="100" r="94" strokeWidth="5" />
        <circle cx="100" cy="100" r="60" strokeWidth="2" />
        <text fill="currentColor" stroke="none" fontFamily="Archivo, Arial Narrow, sans-serif" fontWeight="800" fontSize="15">
          <textPath href={`#ring-${id}`} startOffset="0" textLength="458" lengthAdjust="spacing">{ring}</textPath>
        </text>
        <rect x="22" y="84" width="156" height="34" fill="var(--surface, #fff)" stroke="currentColor" strokeWidth="3" />
        <text x="100" y="109" textAnchor="middle" fill="currentColor" stroke="none" fontFamily="Archivo, Arial Narrow, sans-serif" fontWeight="900" fontSize="24" letterSpacing="2">
          {center}
        </text>
        {sub && (
          <text x="100" y="138" textAnchor="middle" fill="currentColor" stroke="none" fontFamily="Archivo, sans-serif" fontWeight="700" fontSize="11" letterSpacing="1">
            {sub}
          </text>
        )}
        <path d="M86 62 l9 9 l18 -20" strokeWidth="6" strokeLinecap="round" strokeLinejoin="round" />
      </g>
    </svg>
  );
}

export function BrandMark() {
  return (
    <svg viewBox="0 0 28 28" className="brand-mark" aria-hidden="true">
      <circle cx="14" cy="14" r="12.5" fill="none" stroke="#0f6b5c" strokeWidth="2.5" />
      <path d="M8.5 14.5 l4 4 l7 -8.5" fill="none" stroke="#0f6b5c" strokeWidth="2.8" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
}
