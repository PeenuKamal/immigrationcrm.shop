import Link from "next/link";
import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { eq } from "drizzle-orm";
import { db, categories } from "@/db";
import { getDirectory, DirectoryBody, type DirSP } from "@/components/Directory";
import { JsonLd, graph, breadcrumbLd, Breadcrumbs, itemListLd, meta, year, faqLd, FaqSection, type Faq } from "@/lib/seo";
import { popularPairs, comparePath } from "@/lib/compare";
import { url, categorySeoName, categorySeoLower } from "@/lib/site";

export const dynamic = "force-dynamic";
type P = Promise<{ slug: string }>;
type SP = Promise<DirSP>;

const loadCat = (slug: string) => db.query.categories.findFirst({ where: eq(categories.slug, slug) });

export async function generateMetadata({ params, searchParams }: { params: P; searchParams: SP }): Promise<Metadata> {
  const c = await loadCat((await params).slug);
  if (!c) return {};
  const sp = await searchParams;
  return meta({
    title: `Best ${categorySeoName(c.slug, c.name)} (${year()})`,
    description: `${c.description} Compare by verified ratings, features and markets served.`,
    path: `/category/${c.slug}`,
    image: url(`/category/${c.slug}/opengraph-image`),
    noindex: !!(sp.q || sp.region || sp.sort),
  });
}

export default async function Category({ params, searchParams }: { params: P; searchParams: SP }) {
  const { slug } = await params;
  const sp = await searchParams;
  const c = await loadCat(slug);
  if (!c) notFound();
  const [data, pairs] = await Promise.all([getDirectory(sp, slug), popularPairs()]);
  const myPairs = pairs.filter((p) => p.cat === slug).slice(0, 18);
  const rated = data.results.filter((s) => s.reviewCount > 0).sort((a, b) => b.ratingAvg - a.ratingAvg || b.reviewCount - a.reviewCount);
  const top = data.results.slice(0, 5).map((s) => s.name);
  const seo = categorySeoName(c.slug, c.name);
  const seoLower = categorySeoLower(c.slug, c.name);
  const crumbs = [{ name: "Home", path: "/" }, { name: "Software", path: "/software" }, { name: c.name, path: `/category/${c.slug}` }];
  const faqs: Faq[] = [
    {
      q: `What is the best ${seoLower} in ${year()}?`,
      a: rated.length
        ? `Based on verified reviews on ImmigrationCRM.shop, the highest-rated ${c.name.toLowerCase()} tools are ${rated.slice(0, 3).map((s) => `${s.name} (${s.ratingAvg.toFixed(1)}/5, ${s.reviewCount} reviews)`).join(", ")}. The best fit depends on your market and practice size.`
        : `Widely used ${c.name.toLowerCase()} tools include ${top.join(", ")}. Verified ratings will appear as practitioners review them; compare features and markets served in the meantime.`,
    },
    { q: `What is ${c.name.toLowerCase()} software?`, a: c.description },
    { q: `How many ${c.name.toLowerCase()} tools are listed?`, a: `ImmigrationCRM.shop lists ${data.results.length} ${c.name.toLowerCase()} tools. Every rating comes from a practitioner with a verified work email plus LinkedIn or proof of use.` },
    { q: `How do I choose ${c.name.toLowerCase()} software?`, a: `Check that it supports the government forms and programs you file (for example USCIS, IRCC or Home Affairs), the markets you serve, client portal and document collection, billing, and integrations with tools you already use. Then compare two to four options side by side and read verified reviews from firms like yours.` },
  ];

  return (
    <div className="wrap">
      <JsonLd
        data={graph(
          { "@type": "CollectionPage", "@id": url(`/category/${c.slug}`), url: url(`/category/${c.slug}`), name: `Best ${seo}`, description: c.description, isPartOf: { "@id": url("/#website") } },
          breadcrumbLd(crumbs),
          itemListLd(`Best ${seo}`, data.results.map((s) => ({ name: s.name, path: `/software/${s.slug}` }))),
          faqLd(faqs)
        )}
      />
      <Breadcrumbs crumbs={crumbs} />
      <div className="page-head">
        <h1>Best {seoLower}</h1>
        <p>{c.description}</p>
      </div>
      <div className="summary-box">
        <p>{faqs[0].a}</p>
      </div>
      <DirectoryBody data={data} sp={sp} category={slug} />

      {myPairs.length > 0 && (
        <section className="section" aria-labelledby="cat-cmp">
          <h2 id="cat-cmp" style={{ fontSize: "var(--step-2)" }}>Popular {c.name.toLowerCase()} comparisons</h2>
          <ul className="link-grid">
            {myPairs.map((p) => <li key={`${p.a}-${p.b}`}><Link href={comparePath([p.a, p.b])}>{p.an} vs {p.bn}</Link></li>)}
          </ul>
        </section>
      )}
      <section className="section"><FaqSection faqs={faqs} /></section>
    </div>
  );
}
