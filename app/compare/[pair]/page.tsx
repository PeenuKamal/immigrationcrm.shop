import Link from "next/link";
import { notFound, permanentRedirect } from "next/navigation";
import type { Metadata } from "next";
import { loadCompare, parseCompare, comparePath, compareFaqs, competitorsOf, MAX_COMPARE, type CompareItem } from "@/lib/compare";
import { JsonLd, graph, breadcrumbLd, Breadcrumbs, faqLd, FaqSection, meta, softwareLd, year } from "@/lib/seo";
import { url } from "@/lib/site";
import { LogoBox } from "@/components/Logo";
import { Stars } from "@/components/Stars";
import { lines, formatDate } from "@/lib/util";

export const dynamic = "force-dynamic";
type P = Promise<{ pair: string }>;

const title = (items: CompareItem[]) => items.map((i) => i.s.name).join(" vs ");

export async function generateMetadata({ params }: { params: P }): Promise<Metadata> {
  const slugs = parseCompare((await params).pair);
  const items = await loadCompare(slugs);
  if (items.length < 2) return {};
  const t = title(items);
  return meta({
    title: `${t}: side-by-side comparison (${year()})`,
    description: `${t} compared on verified user ratings, features, pricing, markets served and real pros and cons from immigration practitioners.`,
    path: comparePath(items.map((i) => i.s.slug)),
    image: url(`${comparePath(items.map((i) => i.s.slug))}/opengraph-image`),
  });
}

function Cell({ v }: { v: boolean }) {
  return v ? <td className="yes">✓ Yes</td> : <td className="no">—</td>;
}

export default async function ComparePair({ params }: { params: P }) {
  const raw = (await params).pair;
  const slugs = [...new Set(parseCompare(raw))];
  if (slugs.length < 2) notFound();
  if (slugs.length > MAX_COMPARE) permanentRedirect(comparePath(slugs.slice(0, MAX_COMPARE)));
  const items = await loadCompare(slugs);
  if (items.length < 2) notFound();
  const canonical = comparePath(items.map((i) => i.s.slug));
  if (`/compare/${raw}` !== canonical) permanentRedirect(canonical);

  const t = title(items);
  const faqs = compareFaqs(items);
  const features = [...new Set(items.flatMap((i) => i.s.features))].sort((a, b) => a.localeCompare(b));
  const markets = [...new Set(items.flatMap((i) => i.s.regions))].sort();
  const audiences = [...new Set(items.flatMap((i) => i.s.audiences))].sort();
  const crumbs = [{ name: "Home", path: "/" }, { name: "Compare", path: "/compare" }, { name: t, path: canonical }];
  const others = (await competitorsOf(items[0].s.id, 8)).filter((c) => !slugs.includes(c.slug)).slice(0, 6);
  const updated = new Date(Math.max(...items.map((i) => i.s.updatedAt.getTime())));
  const fmt = (n: number, has: boolean) => (has ? n.toFixed(1) : "—");

  return (
    <div className="wrap">
      <JsonLd
        data={graph(
          { "@type": "WebPage", "@id": url(canonical), url: url(canonical), name: `${t} comparison`, dateModified: updated.toISOString(), about: items.map((i) => ({ "@id": url(`/software/${i.s.slug}#software`) })), isPartOf: { "@id": url("/#website") } },
          breadcrumbLd(crumbs),
          ...items.map((i) => softwareLd(i.s, { categoryNames: i.cats.map((c) => c.name) })),
          faqLd(faqs)
        )}
      />
      <Breadcrumbs crumbs={crumbs} />
      <div className="page-head">
        <h1>{t}</h1>
        <p>Side-by-side comparison using verified practitioner reviews and listing data. Updated {formatDate(updated)}.</p>
      </div>

      <div className="summary-box">
        {items.map((i) => (
          <p key={i.s.id}>
            <strong>{i.s.name}</strong> is {i.s.tagline.charAt(0).toLowerCase() + i.s.tagline.slice(1).replace(/\.$/, "")}
            {i.s.regions.length ? `, serving ${i.s.regions.slice(0, 4).join(", ")}` : ""}.{" "}
            {i.s.reviewCount ? `Rated ${i.s.ratingAvg.toFixed(1)}/5 from ${i.s.reviewCount} verified review${i.s.reviewCount > 1 ? "s" : ""}.` : "No verified reviews yet."}
          </p>
        ))}
      </div>

      <div className="compare-scroll">
        <table className="compare">
          <caption className="sr-only">{t} feature and rating comparison</caption>
          <thead>
            <tr>
              <th scope="col"><span className="sr-only">Attribute</span></th>
              {items.map((i) => (
                <th scope="col" key={i.s.id}>
                  <div style={{ display: "flex", gap: 12, alignItems: "center" }}>
                    <LogoBox name={i.s.name} url={i.s.logoUrl} />
                    <Link href={`/software/${i.s.slug}`}>{i.s.name}</Link>
                  </div>
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            <tr className="group"><th colSpan={items.length + 1} scope="colgroup">Verified ratings</th></tr>
            <tr>
              <th scope="row">Overall</th>
              {items.map((i) => (
                <td key={i.s.id}>{i.s.reviewCount ? <span className="rating"><Stars value={i.s.ratingAvg} /> {i.s.ratingAvg.toFixed(1)}</span> : <span className="no-reviews">No reviews yet</span>}</td>
              ))}
            </tr>
            <tr><th scope="row">Verified reviews</th>{items.map((i) => <td key={i.s.id}>{i.s.reviewCount}</td>)}</tr>
            <tr><th scope="row">Would recommend</th>{items.map((i) => <td key={i.s.id}>{i.s.reviewCount ? `${Math.round(i.agg.recommend * 100)}%` : "—"}</td>)}</tr>
            <tr><th scope="row">Ease of use</th>{items.map((i) => <td key={i.s.id}>{fmt(i.agg.ease, !!i.s.reviewCount)}</td>)}</tr>
            <tr><th scope="row">Customer support</th>{items.map((i) => <td key={i.s.id}>{fmt(i.agg.support, !!i.s.reviewCount)}</td>)}</tr>
            <tr><th scope="row">Value for money</th>{items.map((i) => <td key={i.s.id}>{fmt(i.agg.value, !!i.s.reviewCount)}</td>)}</tr>
            <tr><th scope="row">Immigration features</th>{items.map((i) => <td key={i.s.id}>{fmt(i.agg.features, !!i.s.reviewCount)}</td>)}</tr>

            <tr className="group"><th colSpan={items.length + 1} scope="colgroup">Basics</th></tr>
            <tr><th scope="row">Category</th>{items.map((i) => <td key={i.s.id}>{i.cats.map((c) => c.name).join(", ")}</td>)}</tr>
            <tr><th scope="row">Vendor</th>{items.map((i) => <td key={i.s.id}>{i.s.vendorName ?? i.s.name}</td>)}</tr>
            <tr><th scope="row">Deployment</th>{items.map((i) => <td key={i.s.id}>{i.s.deployment ?? "—"}</td>)}</tr>
            <tr><th scope="row">Pricing</th>{items.map((i) => <td key={i.s.id}>{i.s.pricingNote ?? "Contact vendor"}</td>)}</tr>

            {markets.length > 0 && <tr className="group"><th colSpan={items.length + 1} scope="colgroup">Markets served</th></tr>}
            {markets.map((m) => <tr key={m}><th scope="row">{m}</th>{items.map((i) => <Cell key={i.s.id} v={i.s.regions.includes(m)} />)}</tr>)}

            {audiences.length > 0 && <tr className="group"><th colSpan={items.length + 1} scope="colgroup">Built for</th></tr>}
            {audiences.map((a) => <tr key={a}><th scope="row">{a}</th>{items.map((i) => <Cell key={i.s.id} v={i.s.audiences.includes(a)} />)}</tr>)}

            {features.length > 0 && <tr className="group"><th colSpan={items.length + 1} scope="colgroup">Key features listed</th></tr>}
            {features.map((f) => <tr key={f}><th scope="row">{f}</th>{items.map((i) => <Cell key={i.s.id} v={i.s.features.includes(f)} />)}</tr>)}

            <tr>
              <th scope="row"><span className="sr-only">Actions</span></th>
              {items.map((i) => (
                <td key={i.s.id}><Link className="btn small secondary" href={`/software/${i.s.slug}/review`}>Review {i.s.name}</Link></td>
              ))}
            </tr>
          </tbody>
        </table>
      </div>
      <p className="small muted" style={{ marginTop: 8 }}>“—” means the feature isn't listed on the product's profile, not necessarily that it's missing. Vendors keep their own listings up to date.</p>

      <section className="section" aria-labelledby="what-users-say">
        <h2 id="what-users-say">What verified users say</h2>
        <div className="grid-2" style={{ gap: 24 }}>
          {items.map((i) => (
            <div key={i.s.id} className="panel">
              <h3>{i.s.name}</h3>
              {i.latest.length ? (
                <div className="pc" style={{ gridTemplateColumns: "1fr" }}>
                  <div><h4>Most recent pros</h4><ul>{i.latest.flatMap((r) => lines(r.pros).slice(0, 2)).slice(0, 4).map((l, k) => <li key={k}>{l}</li>)}</ul></div>
                  <div className="cons"><h4>Most recent cons</h4><ul>{i.latest.flatMap((r) => lines(r.cons).slice(0, 2)).slice(0, 4).map((l, k) => <li key={k}>{l}</li>)}</ul></div>
                </div>
              ) : (
                <p className="muted small">No verified reviews yet.</p>
              )}
              <Link href={`/software/${i.s.slug}#reviews-h`}>Read all {i.s.name} reviews</Link>
            </div>
          ))}
        </div>
      </section>

      <section className="section"><FaqSection faqs={faqs} /></section>

      {others.length > 0 && (
        <section className="section" aria-labelledby="more-cmp">
          <h2 id="more-cmp" style={{ fontSize: "var(--step-2)" }}>More comparisons</h2>
          <ul className="link-grid">
            {others.flatMap((o) => items.map((i) => (
              <li key={`${i.s.slug}-${o.slug}`}><Link href={comparePath([i.s.slug, o.slug])}>{i.s.name} vs {o.name}</Link></li>
            )))}
            {items.length < MAX_COMPARE && others[0] && (
              <li><Link href={comparePath([...items.map((i) => i.s.slug), others[0].slug])}>Add {others[0].name} to this comparison</Link></li>
            )}
          </ul>
          <p style={{ marginTop: 16 }}><Link href="/compare">Build your own comparison</Link></p>
        </section>
      )}
    </div>
  );
}
