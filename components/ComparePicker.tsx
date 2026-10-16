"use client";
import { useMemo, useState } from "react";
import { useRouter } from "next/navigation";

type Opt = { slug: string; name: string; cat: string };
const MAX = 4;

export function ComparePicker({ options, initial = [] }: { options: Opt[]; initial?: string[] }) {
  const router = useRouter();
  const [picked, setPicked] = useState<string[]>(initial.filter((s) => options.some((o) => o.slug === s)).slice(0, MAX));
  const [q, setQ] = useState("");
  const results = useMemo(() => {
    const n = q.trim().toLowerCase();
    return options.filter((o) => !n || o.name.toLowerCase().includes(n) || o.cat.toLowerCase().includes(n)).slice(0, 40);
  }, [q, options]);
  const nameOf = (slug: string) => options.find((o) => o.slug === slug)?.name ?? slug;
  const go = () => router.push(`/compare/${[...picked].sort().join("-vs-")}`);

  return (
    <div className="compare-picker panel">
      <div>
        <p className="small muted" style={{ marginBottom: 8 }} id="picked-label">
          {picked.length === 0 ? "Choose 2 to 4 products." : `${picked.length} of ${MAX} chosen.`}
        </p>
        <div className="picked" aria-labelledby="picked-label">
          {picked.map((s) => (
            <button key={s} type="button" onClick={() => setPicked(picked.filter((p) => p !== s))} aria-label={`Remove ${nameOf(s)}`}>
              {nameOf(s)} <span aria-hidden="true">×</span>
            </button>
          ))}
        </div>
      </div>
      <div className="field" style={{ margin: 0 }}>
        <label htmlFor="cmp-q">Find software</label>
        <input id="cmp-q" type="search" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Name or category, e.g. Docketwise or AI" autoComplete="off" />
      </div>
      <ul className="pick-results" aria-label="Software to compare">
        {results.map((o) => {
          const on = picked.includes(o.slug);
          return (
            <li key={o.slug}>
              <button type="button" disabled={on || picked.length >= MAX} onClick={() => setPicked([...picked, o.slug])}>
                <span>{o.name}</span>
                <span className="small muted">{on ? "Added" : o.cat}</span>
              </button>
            </li>
          );
        })}
        {results.length === 0 && <li className="small muted" style={{ padding: 12 }}>No software matches “{q}”.</li>}
      </ul>
      <div>
        <button type="button" className="btn stamp" disabled={picked.length < 2} onClick={go}>
          {picked.length < 2 ? "Choose at least 2 to compare" : `Compare ${picked.length} products`}
        </button>
      </div>
    </div>
  );
}
