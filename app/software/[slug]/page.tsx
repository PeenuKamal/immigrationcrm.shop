import Link from "next/link";
import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { and, desc, eq, sql } from "drizzle-orm";
import { db, software, reviews, users, softwareCategories, categories } from "@/db";
import { LogoBox } from "@/components/Logo";
import { Stars } from "@/components/Stars";
import { ReviewCard } from "@/components/ReviewCard";
import { isFeatured, formatDate } from "@/lib/util";
import { currentUser } from "@/lib/session";
import { competitorsOf, comparePath, profileFaqs } from "@/lib/compare";
import { JsonLd, graph, breadcrumbLd, Breadcrumbs, faqLd, FaqSection, meta, softwareLd, year } from "@/lib/seo";
import { url } from "@/lib/site";

export const dynamic = "force-dynamic";
type P = Promise<{ slug: string }>;

async function load(slug: string) {
  return db.query.software.findFirst({ where: and(eq(software.slug, slug), eq(software.status, "PUBLISHED")) });
}

export async function generateMetadata({ params }: { params: P }): Promise<Metadata> {
  const s = await load((await params).slug);
  if (!s) return {};
  const rating = s.reviewCount ? ` Rated ${s.ratingAvg.toFixed(1)}/5 from ${s.reviewCount} verified reviews.` : "";
  return {
    ...meta({
      title: `${s.name} reviews, features & pricing (${year()})`,
      description: `${s.tagline}${rating} Verified pros and cons from immigration practitioners, features, markets served and alternatives.`.slice(0, 300),
      path: `/software/${s.slug}`,
      image: url(`/software/${s.slug}/opengraph-image`),
      modified: s.updatedAt,
    }),
    alternates: { canonical: `/software/${s.slug}`, types: { "text/markdown": `/software/${s.slug}.md` } },
  };
}

export default async function SoftwarePage({ params }: { params: P }) {
  const { slug } = await params;
  const s = await load(slug);
  if (!s) notFound();

  const [list, cats, agg, user, competitors] = await Promise.all([
    db
      .select({ r: reviews, u: { name: users.name, company: users.company, linkedinName: users.linkedinName } })
      .from(reviews)
      .innerJoin(users, eq(users.id, reviews.userId))
      .where(and(eq(reviews.softwareId, s.id), eq(reviews.status, "PUBLISHED")))
      .orderBy(desc(reviews.publishedAt)),
    db
      .select({ name: categories.name, slug: categories.slug })
      .from(softwareCategories)
      .innerJoin(categories, eq(categories.id, softwareCategories.categoryId))
      .where(eq(softwareCategories.softwareId, s.id))
      .orderBy(categories.sortOrder),
    db
      .select({
        ease: sql<number>`coalesce(avg(${reviews.ratingEase}),0)::float`,
        support: sql<number>`coalesce(avg(${reviews.ratingSupport}),0)::float`,
        value: sql<number>`coalesce(avg(${reviews.ratingValue}),0)::float`,
        features: sql<number>`coalesce(avg(${reviews.ratingFeatures}),0)::float`,
        recommend: sql<number>`coalesce(avg(case when ${reviews.recommend} then 1 else 0 end),0)::float`,
      })
      .from(reviews)
      .where(and(eq(reviews.softwareId, s.id), eq(reviews.status, "PUBLISHED")))
      .then((r) => r[0]),
    currentUser(),
    competitorsOf(s.id, 8),
  ]);

  const featured = isFeatured(s.featuredUntil);
  const isOwner = user && s.ownerId === user.id;
  const faqs = profileFaqs(s, cats, competitors);
  const crumbs = [
    { name: "Home", path: "/" },
    ...(cats[0] ? [{ name: cats[0].name, path: `/category/${cats[0].slug}` }] : [{ name: "Software", path: "/software" }]),
    { name: s.name, path: `/software/${s.slug}` },
  ];

  return (
    <div className="wrap">
      <JsonLd
        data={graph(
          { "@type": "WebPage", "@id": url(`/software/${s.slug}`), url: url(`/software/${s.slug}`), name: `${s.name} reviews`, dateModified: s.updatedAt.toISOString(), mainEntity: { "@id": url(`/software/${s.slug}#software`) }, isPartOf: { "@id": url("/#website") } },
          softwareLd(s, { reviews: list.map((l) => l.r), categoryNames: cats.map((c) => c.name), full: true }),
          breadcrumbLd(crumbs),
          faqLd(faqs)
        )}
      />
      <Breadcrumbs crumbs={crumbs} />
      <div className="profile-head">
        <LogoBox name={s.name} url={s.logoUrl} large />
        <div>
          {featured && <span className="featured-tag">★ Featured listing</span>}
          <h1>{s.name} reviews</h1>
          <p style={{ fontSize: "var(--step-1)", color: "var(--ink-2)", marginBottom: 8 }}>{s.tagline}</p>
          <p className="small muted" style={{ margin: 0 }}>
            {s.vendorName ? `By ${s.vendorName}. ` : ""}
            {s.claimed ? "Listing managed by the vendor." : "Unclaimed listing — details compiled by our editors."} Updated {formatDate(s.updatedAt)}.
          </p>
          <div className="profile-actions">
            <Link className="btn stamp" href={`/software/${s.slug}/review`}>Write a verified review</Link>
            <Link className="btn secondary" href={`/compare?add=${s.slug}`}>Compare</Link>
            {s.website && <a className="btn secondary" href={s.website} target="_blank" rel="noopener nofollow">Visit website</a>}
            {isOwner ? (
              <Link className="btn secondary" href={`/vendor/${s.id}`}>Manage listing</Link>
            ) : (
              !s.claimed && <Link className="btn secondary" href={`/software/${s.slug}/claim`}>Claim this listing</Link>
            )}
          </div>
        </div>
      </div>

      <div className="summary-box">
        <p>
          <strong>{s.name}</strong> is {s.tagline.charAt(0).toLowerCase() + s.tagline.slice(1).replace(/\.$/, "")}
          {cats[0] ? ` in the ${cats[0].name.toLowerCase()} category` : ""}
          {s.regions.length ? `, serving ${s.regions.join(", ")}` : ""}.{" "}
          {s.reviewCount
            ? `It is rated ${s.ratingAvg.toFixed(1)} out of 5 from ${s.reviewCount} verified review${s.reviewCount > 1 ? "s" : ""}, and ${Math.round(agg.recommend * 100)}% of reviewers would recommend it.`
            : "It has no verified reviews yet."}
          {competitors.length ? ` Main alternatives: ${competitors.slice(0, 3).map((c) => c.name).join(", ")}.` : ""}
        </p>
      </div>

      <div className="profile-grid">
        <div className="stack">
          <section className="panel" aria-labelledby="rating-h">
            <h2 id="rating-h" className="sr-only">{s.name} rating</h2>
            {s.reviewCount ? (
              <>
                <div className="score">
                  <span className="big">{s.ratingAvg.toFixed(1)}</span>
                  <div>
                    <Stars value={s.ratingAvg} />
                    <p className="small muted" style={{ margin: 0 }}>
                      {s.reviewCount} verified {s.reviewCount === 1 ? "review" : "reviews"} · {Math.round(agg.recommend * 100)}% would recommend
                    </p>
                  </div>
                </div>
                <div className="bars">
                  {([["Ease of use", agg.ease], ["Support", agg.support], ["Value for money", agg.value], ["Features", agg.features]] as const).map(([label, v]) => (
                    <div className="bar" key={label}>
                      <span>{label}</span>
                      <span className="track"><span className="fill" style={{ width: `${(v / 5) * 100}%`, display: "block" }} /></span>
                      <span>{v.toFixed(1)}</span>
                    </div>
                  ))}
                </div>
              </>
            ) : (
              <>
                <h3>No verified reviews yet</h3>
                <p className="muted">If your firm uses {s.name}, yours could be the first. We'll confirm your work email and either your LinkedIn or proof that you use it.</p>
                <Link className="btn stamp" href={`/software/${s.slug}/review`}>Review {s.name}</Link>
              </>
            )}
          </section>

          <section aria-labelledby="about">
            <h2 id="about">About {s.name}</h2>
            <p>{s.description}</p>
            {s.pricingNote && <p><strong>Pricing:</strong> {s.pricingNote}</p>}
          </section>

          <section aria-labelledby="reviews-h">
            <h2 id="reviews-h">Verified {s.name} reviews</h2>
            {list.length ? (
              <div className="stack">{list.map(({ r, u }) => <ReviewCard key={r.id} r={r} author={u} />)}</div>
            ) : (
              <p className="muted">Reviews appear here once they pass verification.</p>
            )}
          </section>

          {competitors.length > 0 && (
            <section aria-labelledby="cmp-h">
              <h2 id="cmp-h">Compare {s.name}</h2>
              <ul className="link-grid">
                {competitors.map((c) => (
                  <li key={c.id}><Link href={comparePath([s.slug, c.slug])}>{s.name} vs {c.name}</Link></li>
                ))}
              </ul>
              <p style={{ marginTop: 16 }}><Link href={`/software/${s.slug}/alternatives`}>See all {s.name} alternatives</Link></p>
            </section>
          )}

          <FaqSection faqs={faqs} heading={`${s.name} FAQ`} />
        </div>

        <aside className="panel" aria-label="Details" style={{ alignSelf: "start" }}>
          <dl className="facts">
            {cats.length > 0 && (<><dt>Category</dt><dd>{cats.map((c, i) => <span key={c.slug}>{i > 0 && ", "}<Link href={`/category/${c.slug}`}>{c.name}</Link></span>)}</dd></>)}
            {s.vendorName && (<><dt>Vendor</dt><dd>{s.vendorName}</dd></>)}
            {s.deployment && (<><dt>Deployment</dt><dd>{s.deployment}</dd></>)}
            {s.regions.length > 0 && (<><dt>Markets served</dt><dd>{s.regions.join(", ")}</dd></>)}
            {s.audiences.length > 0 && (<><dt>Built for</dt><dd>{s.audiences.join(", ")}</dd></>)}
            <dt>Last updated</dt><dd><time dateTime={s.updatedAt.toISOString()}>{formatDate(s.updatedAt)}</time></dd>
          </dl>
          {s.features.length > 0 && (
            <>
              <h3 style={{ fontSize: "var(--step-0)" }}>Key features</h3>
              <ul className="tag-list">{s.features.map((f) => <li key={f}>{f}</li>)}</ul>
            </>
          )}
        </aside>
      </div>
    </div>
  );
}
