import Link from "next/link";
import { JsonLd, graph, breadcrumbLd, Breadcrumbs, meta, faqLd, FaqSection, type Faq, ORG_ID } from "@/lib/seo";
import { SITE, url } from "@/lib/site";

export const metadata = meta({
  title: "About ImmigrationCRM.shop — independent immigration software reviews",
  description: "Who runs ImmigrationCRM.shop, how listings and verified reviews work, how we make money, and how to contact us.",
  path: "/about",
  absoluteTitle: true,
});

const faqs: Faq[] = [
  { q: "Is ImmigrationCRM.shop independent?", a: "Yes. We're not owned by any software vendor. Vendors can pay for clearly labelled featured placement, but payment never changes ratings, review order within a listing, or which reviews are published." },
  { q: "How does ImmigrationCRM.shop make money?", a: "From optional featured placement for vendors. Listing a product and writing reviews are free." },
  { q: "Who can write a review?", a: "Anyone who uses the software at work: immigration lawyers, RCICs, migration agents, paralegals, case managers and corporate mobility teams. Reviewers verify a work email plus LinkedIn or proof of use." },
  { q: "How do I correct a listing?", a: `If you work for the vendor, claim the listing and edit it. Otherwise, email ${SITE.contactEmail}.` },
];

export default function About() {
  const crumbs = [{ name: "Home", path: "/" }, { name: "About", path: "/about" }];
  return (
    <div className="wrap">
      <JsonLd data={graph({ "@type": "AboutPage", "@id": url("/about"), url: url("/about"), name: "About ImmigrationCRM.shop", about: { "@id": ORG_ID() }, isPartOf: { "@id": url("/#website") } }, breadcrumbLd(crumbs), faqLd(faqs))} />
      <Breadcrumbs crumbs={crumbs} />
      <div className="prose">
        <h1>About {SITE.name}</h1>
        <p style={{ fontSize: "var(--step-1)", color: "var(--ink-2)" }}>
          {SITE.name} helps immigration professionals choose software using reviews from people who actually use it at work — not anonymous ratings or vendor marketing.
        </p>
        <h2>What we cover</h2>
        <ul>
          <li><Link href="/category/immigration-crm">Immigration CRMs</Link> for RCICs, migration agents and visa consultancies</li>
          <li><Link href="/category/immigration-case-management">Immigration case management</Link> for law firms</li>
          <li><Link href="/category/law-practice-management">Law practice management</Link> suites used by immigration practices</li>
          <li><Link href="/category/ai-immigration">AI tools for immigration law</Link>: petition drafting, research and RFE responses</li>
          <li><Link href="/category/corporate-immigration">Corporate immigration and global mobility</Link> platforms</li>
        </ul>
        <h2>Editorial policy</h2>
        <p>
          Starter listings are compiled by our editors from vendors' public information and independent directories, and are marked “unclaimed” until the vendor verifies them.
          Ratings come only from verified reviews — see <Link href="/how-we-verify">how we verify</Link>. We never write, buy or edit reviews, and we publish negative reviews that meet our guidelines.
        </p>
        <h2>Contact</h2>
        <p>Email <a href={`mailto:${SITE.contactEmail}`}>{SITE.contactEmail}</a>.</p>
        <FaqSection faqs={faqs} />
      </div>
    </div>
  );
}
