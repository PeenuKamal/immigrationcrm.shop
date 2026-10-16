import Link from "next/link";
import { allCategoriesWithCounts, searchSoftware, categoryNamesFor, allRegions, type SortKey } from "@/lib/queries";
import { ListingRow } from "@/components/ListingRow";

export type DirSP = { q?: string; region?: string; sort?: string };

export const SORTS: { key: SortKey; label: string }[] = [
  { key: "featured", label: "Recommended" },
  { key: "rating", label: "Highest rated" },
  { key: "reviews", label: "Most reviewed" },
  { key: "name", label: "A–Z" },
  { key: "newest", label: "Newest" },
];

export async function getDirectory(sp: DirSP, category?: string) {
  const sort = (SORTS.find((s) => s.key === sp.sort)?.key ?? "featured") as SortKey;
  const [cats, regions, results] = await Promise.all([
    allCategoriesWithCounts(),
    allRegions(),
    searchSoftware({ q: sp.q?.trim(), category, region: sp.region, sort }),
  ]);
  const names = await categoryNamesFor(results.map((s) => s.id));
  return { cats, regions, results, names, sort };
}

export function DirectoryBody({ data, sp, category }: { data: Awaited<ReturnType<typeof getDirectory>>; sp: DirSP; category?: string }) {
  const { cats, regions, results, names, sort } = data;
  const base = category ? `/category/${category}` : "/software";
  const href = (patch: Record<string, string | undefined>, toBase = base) => {
    const p = new URLSearchParams();
    const merged = { q: sp.q, region: sp.region, sort: sp.sort, ...patch };
    for (const [k, v] of Object.entries(merged)) if (v) p.set(k, v);
    const s = p.toString();
    return `${toBase}${s ? `?${s}` : ""}`;
  };
  return (
    <div className="directory">
      <aside className="filters" aria-label="Filters">
        <h3>Category</h3>
        <ul>
          <li><Link href={href({}, "/software")} aria-current={!category}>All categories</Link></li>
          {cats.map((c) => (
            <li key={c.slug}>
              <Link href={href({}, `/category/${c.slug}`)} aria-current={category === c.slug}>
                <span>{c.name}</span><span>{c.count}</span>
              </Link>
            </li>
          ))}
        </ul>
        <h3>Market served</h3>
        <ul>
          <li><Link href={href({ region: undefined })} aria-current={!sp.region} rel="nofollow">Any market</Link></li>
          {regions.map((r) => (
            <li key={r.region}>
              <Link href={href({ region: r.region })} aria-current={sp.region === r.region} rel="nofollow">
                <span>{r.region}</span><span>{r.n}</span>
              </Link>
            </li>
          ))}
        </ul>
      </aside>

      <section aria-label="Results">
        <div className="section-head" style={{ marginBottom: 16 }}>
          <p className="muted" style={{ margin: 0 }}>{results.length} {results.length === 1 ? "tool" : "tools"}</p>
          <div className="tabs" style={{ margin: 0 }} aria-label="Sort">
            {SORTS.map((s) => (
              <Link key={s.key} className="chip" rel="nofollow" href={href({ sort: s.key === "featured" ? undefined : s.key })} aria-current={sort === s.key}>
                {s.label}
              </Link>
            ))}
          </div>
        </div>
        {results.length ? (
          <ol className="listing-rows">{results.map((s) => <ListingRow key={s.id} s={s} categoryNames={names.get(s.id)} />)}</ol>
        ) : (
          <div className="empty">
            <h3>Nothing matches yet</h3>
            <p>Try a broader search, or clear the filters. If the software you're looking for isn't listed, you can add it.</p>
            <div style={{ display: "flex", gap: 12, flexWrap: "wrap" }}>
              <Link className="btn secondary" href={base}>Clear filters</Link>
              <Link className="btn" href="/list-your-software">Add a listing</Link>
            </div>
          </div>
        )}
      </section>
    </div>
  );
}
