import Link from "next/link";
import { allCategoriesWithCounts, searchSoftware, categoryNamesFor, recentReviews, publishedCount } from "@/lib/queries";
import { ListingRow } from "@/components/ListingRow";
import { ReviewCard } from "@/components/ReviewCard";
import { Stamp } from "@/components/Stamp";
import { JsonLd, graph, faqLd, FaqSection, meta, year, type Faq } from "@/lib/seo";
import { popularPairs, comparePath } from "@/lib/compare";
import { url } from "@/lib/site";

export const dynamic = "force-dynamic";

export const metadata = meta({
  title: `Immigration CRM & case management software reviews (${year()}) | ImmigrationCRM.shop`,
  description: "Compare immigration CRMs, case management, law practice management and AI tools. Every review is from a verified immigration lawyer, RCIC or migration agent.",
  path: "/",
  absoluteTitle: true,
});

const HOME_FAQS: Faq[] = [
  { q: "What is an immigration CRM?", a: "An immigration CRM is software that immigration consultants, RCICs, migration agents and law firms use to manage enquiries and leads, client intake, document checklists, government forms, case stages, client communication and billing in one place." },
  { q: "What is the difference between an immigration CRM and immigration case management software?", a: "Immigration CRMs focus on the sales side — enquiries, assessments, follow-ups and retainers — plus case tracking, and are popular with consultancies. Immigration case management software focuses on filing: government forms libraries (such as USCIS or IRCC forms), deadlines, evidence and case status, and is popular with law firms. Many products now do both." },
  { q: "How are reviews on ImmigrationCRM.shop verified?", a: "Each reviewer confirms a work email with a one-time code, then either connects LinkedIn or uploads proof of use that a moderator checks. Vendors can't review their own products." },
  { q: "Can I compare immigration software side by side?", a: "Yes. Choose any two to four products on the Compare page to see verified ratings, features, markets served, pricing and real pros and cons side by side." },
  { q: "Is listing software on ImmigrationCRM.shop free?", a: "Yes. Vendors list for free and can claim their listing to keep it accurate and reply to reviews. Optional featured placement is paid and always labelled." },
];

export default async function Home() {
  const [cats, top, latest, total, pairs] = await Promise.all([
    allCategoriesWithCounts(),
    searchSoftware({ category: "immigration-crm", sort: "featured", limit: 6 }),
    recentReviews(3),
    publishedCount(),
    popularPairs(),
  ]);
  const featuredPairs = ["docketwise-vs-lollylaw", "caseeasy-vs-maple-crm", "clio-vs-mycase", "parley-ai-vs-visalaw-ai", "agentcis-vs-maple-crm", "inszoom-vs-lollylaw"]
    .map((k) => pairs.find((p) => `${p.a}-vs-${p.b}` === k))
    .filter((p): p is NonNullable<typeof p> => !!p);
  const names = await categoryNamesFor(top.map((s) => s.id));

  return (
    <div className="wrap">
      <JsonLd data={graph({ "@type": "WebPage", "@id": url("/"), url: url("/"), name: "Immigration software reviews", isPartOf: { "@id": url("/#website") }, about: "Immigration CRM and case management software" }, faqLd(HOME_FAQS))} />
      <section className="hero">
        <div>
          <h1>Immigration software, reviewed only by people who file the cases.</h1>
          <p className="hero-lede">
            Compare immigration CRMs, case management, practice management and AI tools. Every review comes from a practitioner whose work email
            and employment we've checked.
          </p>
          <form className="search" action="/software" role="search">
            <label htmlFor="q" className="sr-only">Search software</label>
            <input id="q" name="q" type="search" placeholder="Try “IRCC forms” or “RFE drafting”" />
            <button className="btn" type="submit">Search</button>
          </form>
          <div className="quick-cats">
            {cats.map((c) => (
              <Link key={c.slug} className="chip" href={`/category/${c.slug}`}>{c.name}</Link>
            ))}
          </div>
        </div>
        <div className="hero-stamp-block">
          <Stamp className="stamp-big" sub="EVERY REVIEW" title="Every review is verified" />
          <ol className="steps" aria-label="How a review gets verified">
            <li><div><strong>Work email confirmed</strong><span>Personal Gmail, Yahoo and Outlook addresses are refused.</span></div></li>
            <li><div><strong>Identity or usage proven</strong><span>A LinkedIn profile, or a screenshot or invoice we check by hand.</span></div></li>
            <li><div><strong>No self-reviews</strong><span>Staff can't review their own employer's product.</span></div></li>
          </ol>
        </div>
      </section>

      <section className="section" aria-labelledby="cats">
        <div className="section-head">
          <h2 id="cats">Browse by what you need</h2>
          <Link href="/software">All {total} listings</Link>
        </div>
        <div className="cat-list">
          {cats.map((c) => (
            <Link key={c.slug} href={`/category/${c.slug}`}>
              <strong>{c.name}</strong>
              <span className="count">{c.count} tools</span>
              <span className="desc">{c.description}</span>
            </Link>
          ))}
        </div>
      </section>

      <section className="section" aria-labelledby="top">
        <div className="section-head">
          <h2 id="top">Immigration CRMs for consultants</h2>
          <Link href="/category/immigration-crm">See all in this category</Link>
        </div>
        <ul className="listing-rows">
          {top.map((s) => <ListingRow key={s.id} s={s} categoryNames={names.get(s.id)} />)}
        </ul>
      </section>

      <section className="section" aria-labelledby="latest">
        <div className="section-head">
          <h2 id="latest">Latest verified reviews</h2>
        </div>
        {latest.length ? (
          <div className="stack">
            {latest.map(({ r, s, u }) => <ReviewCard key={r.id} r={r} author={u} softwareName={s.name} />)}
          </div>
        ) : (
          <div className="empty">
            <h3>The first verified reviews are being written now</h3>
            <p>Use one of these tools at your firm? Your review helps other practitioners choose — it takes about five minutes.</p>
            <Link className="btn stamp" href="/software">Pick the software you use</Link>
          </div>
        )}
      </section>

      <section className="section" aria-labelledby="cmp">
        <div className="section-head">
          <h2 id="cmp">Compare side by side</h2>
          <Link href="/compare">Compare any 2–4 products</Link>
        </div>
        <ul className="link-grid">
          {featuredPairs.map((p) => <li key={p.a + p.b}><Link href={comparePath([p.a, p.b])}>{p.an} vs {p.bn}</Link></li>)}
        </ul>
      </section>

      <section className="section">
        <FaqSection faqs={HOME_FAQS} />
      </section>

      <section className="section">
        <div className="panel" style={{ display: "flex", gap: 24, flexWrap: "wrap", justifyContent: "space-between", alignItems: "center" }}>
          <div>
            <h2 style={{ fontSize: "var(--step-2)" }}>Build immigration software?</h2>
            <p style={{ margin: 0 }} className="muted">Listing is free. Claim your profile to keep it accurate and reply to reviews.</p>
          </div>
          <Link className="btn" href="/list-your-software">List your software</Link>
        </div>
      </section>
    </div>
  );
}
