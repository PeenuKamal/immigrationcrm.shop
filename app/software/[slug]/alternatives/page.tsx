import Link from "next/link";
import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { and, eq } from "drizzle-orm";
import { db, software } from "@/db";
import { competitorsOf, comparePath } from "@/lib/compare";
import { categoryNamesFor } from "@/lib/queries";
import { JsonLd, graph, breadcrumbLd, Breadcrumbs, faqLd, FaqSection, itemListLd, meta, year, type Faq } from "@/lib/seo";
import { RatingInline } from "@/components/Stars";
import { LogoBox } from "@/components/Logo";
import { url } from "@/lib/site";

export const dynamic = "force-dynamic";
type P = Promise<{ slug: string }>;

const load = (slug: string) => db.query.software.findFirst({ where: and(eq(software.slug, slug), eq(software.status, "PUBLISHED")) });

export async function generateMetadata({ params }: { params: P }): Promise<Metadata> {
  const s = await load((await params).slug);
  if (!s) return {};
  return meta({
    title: `Best ${s.name} alternatives & competitors (${year()})`,
    description: `Compare the top alternatives to ${s.name} with verified reviews from immigration practitioners: ratings, features, markets served and side-by-side comparisons.`,
    path: `/software/${s.slug}/alternatives`,
    image: url(`/software/${s.slug}/opengraph-image`),
  });
}

export default async function Alternatives({ params }: { params: P }) {
  const { slug } = await params;
  const s = await load(slug);
  if (!s) notFound();
  const alts = await competitorsOf(s.id, 12);
  const cats = await categoryNamesFor([s.id, ...alts.map((a) => a.id)]);
  const mainCat = cats.get(s.id)?.[0] ?? "immigration software";
  const crumbs = [
    { name: "Home", path: "/" },
    { name: s.name, path: `/software/${s.slug}` },
    { name: "Alternatives", path: `/software/${s.slug}/alternatives` },
  ];
  const rated = alts.filter((a) => a.reviewCount > 0);
  const faqs: Faq[] = [
    { q: `What are the best alternatives to ${s.name}?`, a: `The closest alternatives to ${s.name} in ${mainCat.toLowerCase()} are ${alts.slice(0, 5).map((a) => a.name).join(", ")}.${rated.length ? ` The highest rated by verified users is ${rated[0].name} (${rated[0].ratingAvg.toFixed(1)}/5).` : ""}` },
    { q: `Why do firms look for ${s.name} alternatives?`, a: `Common reasons are pricing, support for a specific country's immigration forms (for example IRCC, USCIS or Home Affairs), client portal needs, AI drafting and integrations. Compare features side by side before switching.` },
    { q: `How are these alternatives ranked?`, a: `Alternatives that share the most categories with ${s.name} appear first, then by number of verified reviews and average rating. Featured placement does not change this list.` },
  ];

  return (
    <div className="wrap">
      <JsonLd data={graph(breadcrumbLd(crumbs), itemListLd(`${s.name} alternatives`, alts.map((a) => ({ name: a.name, path: `/software/${a.slug}` }))), faqLd(faqs))} />
      <Breadcrumbs crumbs={crumbs} />
      <div className="page-head">
        <h1>{s.name} alternatives</h1>
        <p>{alts.length} tools that do the same job as {s.name}, with verified reviews from immigration practitioners.</p>
      </div>
      <div className="summary-box">
        <p><strong>{s.name}</strong> is {s.tagline.charAt(0).toLowerCase() + s.tagline.slice(1)} The top alternatives are {alts.slice(0, 5).map((a) => a.name).join(", ")}.</p>
      </div>

      <ol className="listing-rows" style={{ listStyle: "none" }}>
        {alts.map((a) => (
          <li key={a.id} className="listing">
            <LogoBox name={a.name} url={a.logoUrl} />
            <div>
              <h2 style={{ fontSize: "var(--step-1)", margin: 0 }}><Link href={`/software/${a.slug}`}>{a.name}</Link></h2>
              <p className="tagline">{a.tagline}</p>
              <div className="meta">
                <RatingInline avg={a.ratingAvg} count={a.reviewCount} />
                <span>{(cats.get(a.id) ?? []).join(", ")}</span>
                <Link className="compare-tag" href={comparePath([s.slug, a.slug])}>Compare with {s.name}</Link>
              </div>
            </div>
          </li>
        ))}
      </ol>

      <section className="section"><FaqSection faqs={faqs} /></section>
    </div>
  );
}
