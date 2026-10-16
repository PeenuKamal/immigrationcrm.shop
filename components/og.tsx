import { ImageResponse } from "next/og";

export const OG_SIZE = { width: 1200, height: 630 };

/** Shared Open Graph card: paper background, ink headline, teal verification stamp. */
export function ogCard({ eyebrow, title, subtitle, rating }: { eyebrow: string; title: string; subtitle?: string; rating?: string }) {
  return new ImageResponse(
    (
      <div style={{ width: "100%", height: "100%", display: "flex", flexDirection: "column", justifyContent: "space-between", background: "#edf1ec", padding: "64px 72px", fontFamily: "sans-serif", color: "#172033" }}>
        <div style={{ display: "flex", alignItems: "center", gap: 16, fontSize: 30, fontWeight: 700 }}>
          <svg width="44" height="44" viewBox="0 0 28 28"><circle cx="14" cy="14" r="12.5" fill="none" stroke="#0f6b5c" strokeWidth="2.5" /><path d="M8.5 14.5 l4 4 l7 -8.5" fill="none" stroke="#0f6b5c" strokeWidth="2.8" strokeLinecap="round" strokeLinejoin="round" /></svg>
          <span>ImmigrationCRM</span><span style={{ color: "#0f6b5c" }}>.shop</span>
        </div>
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", gap: 40 }}>
          <div style={{ display: "flex", flexDirection: "column", maxWidth: 820 }}>
            <div style={{ fontSize: 28, color: "#5d6877", marginBottom: 16 }}>{eyebrow}</div>
            <div style={{ fontSize: title.length > 40 ? 58 : 72, fontWeight: 800, lineHeight: 1.05, letterSpacing: -1 }}>{title}</div>
            {subtitle && <div style={{ fontSize: 30, color: "#3c4859", marginTop: 20, lineHeight: 1.3 }}>{subtitle}</div>}
          </div>
          <div style={{ display: "flex", flexDirection: "column", alignItems: "center", justifyContent: "center", width: 220, height: 220, borderRadius: 220, border: "8px solid #0f6b5c", color: "#0f6b5c", transform: "rotate(-9deg)", flexShrink: 0 }}>
            {rating ? (
              <>
                <div style={{ fontSize: 76, fontWeight: 800, lineHeight: 1 }}>{rating}</div>
                <div style={{ fontSize: 22, fontWeight: 700, marginTop: 6 }}>VERIFIED</div>
              </>
            ) : (
              <div style={{ fontSize: 30, fontWeight: 800, textAlign: "center" }}>VERIFIED REVIEWS</div>
            )}
          </div>
        </div>
        <div style={{ fontSize: 24, color: "#5d6877" }}>Every review from a verified immigration practitioner</div>
      </div>
    ),
    OG_SIZE
  );
}
