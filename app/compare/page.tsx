import Link from "next/link";
import { eq } from "drizzle-orm";
import { db, software } from "@/db";
import { ComparePicker } from "@/components/ComparePicker";
import { categoryNamesFor, allCategoriesWithCounts } from "@/lib/queries";
import { popularPairs, comparePath } from "@/lib/compare";
import { JsonLd, graph, breadcrumbLd, Breadcrumbs, meta, itemListLd, year } from "@/lib/seo";

export const dynamic = "force-dynamic";

export const metadata = meta({
  title: `Compare immigration software side by side (${year()})`,
  description: "Compare any 2–4 immigration CRMs, case management, practice management or AI tools side by side: verified ratings, features, markets served, pricing and real pros and cons.",
  path: "/compare",
});

export default async function ComparePage({ searchParams }: { searchParams: Promise<{ add?: string }> }) {
  const sp = await searchParams;
  const [all, pairs, cats] = await Promise.all([
    db.select({ id: software.id, slug: software.slug, name: software.name }).from(software).where(eq(software.status, "PUBLISHED")).orderBy(software.name),
    popularPairs(),
    allCategoriesWithCounts(),
  ]);
  const catNames = await categoryNamesFor(all.map((a) => a.id));
  const options = all.map((a) => ({ slug: a.slug, name: a.name, cat: catNames.get(a.id)?.[0] ?? "" }));
  const crumbs = [{ name: "Home", path: "/" }, { name: "Compare", path: "/compare" }];

  return (
    <div className="wrap">
      <JsonLd data={graph(breadcrumbLd(crumbs), itemListLd("Popular immigration software comparisons", pairs.slice(0, 50).map((p) => ({ name: `${p.an} vs ${p.bn}`, path: comparePath([p.a, p.b]) }))))} />
      <Breadcrumbs crumbs={crumbs} />
      <div className="page-head">
        <h1>Compare immigration software</h1>
        <p>Pick any two to four tools — from any category — and see verified ratings, features, markets and real pros and cons side by side.</p>
      </div>
      <ComparePicker options={options} initial={sp.add ? [sp.add] : []} />

      {cats.map((c) => {
        const list = pairs.filter((p) => p.cat === c.slug);
        if (!list.length) return null;
        return (
          <section key={c.slug} className="section" aria-labelledby={`pairs-${c.slug}`}>
            <div className="section-head"><h2 id={`pairs-${c.slug}`} style={{ fontSize: "var(--step-2)" }}>{c.name} comparisons</h2></div>
            <ul className="link-grid">
              {list.map((p) => (
                <li key={`${p.a}-${p.b}`}><Link href={comparePath([p.a, p.b])}>{p.an} vs {p.bn}</Link></li>
              ))}
            </ul>
          </section>
        );
      })}
    </div>
  );
}
