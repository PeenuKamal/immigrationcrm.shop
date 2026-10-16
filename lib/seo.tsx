import type { Metadata } from "next";
import Link from "next/link";
import type { Software, Review } from "@/db";
import { SITE, url } from "./site";
import { lines } from "./util";

/** Renders schema.org JSON-LD. `<` is escaped so user text can't break out of the script tag. */
export function JsonLd({ data }: { data: unknown }) {
  return <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(data).replace(/</g, "\\u003c") }} />;
}

export type Crumb = { name: string; path: string };

export function breadcrumbLd(crumbs: Crumb[]) {
  return {
    "@type": "BreadcrumbList",
    itemListElement: crumbs.map((c, i) => ({ "@type": "ListItem", position: i + 1, name: c.name, item: url(c.path) })),
  };
}

export function Breadcrumbs({ crumbs }: { crumbs: Crumb[] }) {
  return (
    <nav aria-label="Breadcrumb" className="crumbs">
      <ol>
        {crumbs.map((c, i) => (
          <li key={c.path}>{i < crumbs.length - 1 ? <Link href={c.path}>{c.name}</Link> : <span aria-current="page">{c.name}</span>}</li>
        ))}
      </ol>
    </nav>
  );
}

export type Faq = { q: string; a: string };

export function faqLd(faqs: Faq[]) {
  return { "@type": "FAQPage", mainEntity: faqs.map((f) => ({ "@type": "Question", name: f.q, acceptedAnswer: { "@type": "Answer", text: f.a } })) };
}

export function FaqSection({ faqs, heading = "Frequently asked questions" }: { faqs: Faq[]; heading?: string }) {
  if (!faqs.length) return null;
  return (
    <section aria-labelledby="faq-h" className="faq">
      <h2 id="faq-h">{heading}</h2>
      {faqs.map((f) => (
        <details key={f.q}>
          <summary><h3>{f.q}</h3></summary>
          <p>{f.a}</p>
        </details>
      ))}
    </section>
  );
}

export const ORG_ID = () => url("/#organization");
export const SITE_ID = () => url("/#website");

export function orgLd() {
  return {
    "@type": "Organization",
    "@id": ORG_ID(),
    name: SITE.name,
    url: url("/"),
    logo: { "@type": "ImageObject", url: url("/icon-512.png"), width: 512, height: 512 },
    description: SITE.description,
    email: SITE.contactEmail,
    knowsAbout: ["Immigration CRM software", "Immigration case management software", "Legal practice management software", "Legal AI for immigration law"],
  };
}

export function websiteLd() {
  return {
    "@type": "WebSite",
    "@id": SITE_ID(),
    name: SITE.name,
    url: url("/"),
    description: SITE.description,
    publisher: { "@id": ORG_ID() },
    inLanguage: "en",
    potentialAction: { "@type": "SearchAction", target: { "@type": "EntryPoint", urlTemplate: `${url("/software")}?q={search_term_string}` }, "query-input": "required name=search_term_string" },
  };
}

export function graph(...nodes: unknown[]) {
  return { "@context": "https://schema.org", "@graph": nodes };
}

/** schema.org SoftwareApplication, with reviews when there are any. */
export function softwareLd(s: Software, opts: { reviews?: Review[]; categoryNames?: string[]; full?: boolean } = {}) {
  const node: Record<string, unknown> = {
    "@type": "SoftwareApplication",
    "@id": url(`/software/${s.slug}#software`),
    name: s.name,
    url: url(`/software/${s.slug}`),
    description: s.description,
    applicationCategory: "BusinessApplication",
    applicationSubCategory: opts.categoryNames?.join(", ") || undefined,
    operatingSystem: s.deployment === "On-premise" ? "Windows" : "Web browser",
    ...(s.website && { sameAs: [s.website] }),
    ...(s.logoUrl && { image: s.logoUrl }),
    ...(s.vendorName && { publisher: { "@type": "Organization", name: s.vendorName } }),
    ...(s.features.length && { featureList: s.features.join(", ") }),
    ...(s.regions.length && { areaServed: s.regions }),
    ...(s.audiences.length && { audience: { "@type": "BusinessAudience", audienceType: s.audiences.join(", ") } }),
    dateModified: s.updatedAt.toISOString(),
  };
  if (s.reviewCount > 0) {
    node.aggregateRating = { "@type": "AggregateRating", ratingValue: s.ratingAvg.toFixed(1), reviewCount: s.reviewCount, bestRating: 5, worstRating: 1 };
  }
  if (opts.full && opts.reviews?.length) {
    node.review = opts.reviews.slice(0, 20).map((r) => ({
      "@type": "Review",
      name: r.title,
      reviewBody: `${r.useCase}`,
      datePublished: (r.publishedAt ?? r.createdAt).toISOString(),
      author: { "@type": "Person", name: `Verified ${r.reviewerRole}` },
      reviewRating: { "@type": "Rating", ratingValue: r.rating, bestRating: 5, worstRating: 1 },
      positiveNotes: { "@type": "ItemList", itemListElement: lines(r.pros).map((p, i) => ({ "@type": "ListItem", position: i + 1, name: p })) },
      negativeNotes: { "@type": "ItemList", itemListElement: lines(r.cons).map((p, i) => ({ "@type": "ListItem", position: i + 1, name: p })) },
      publisher: { "@id": ORG_ID() },
    }));
  }
  return node;
}

export function itemListLd(name: string, items: { name: string; path: string }[]) {
  return {
    "@type": "ItemList",
    name,
    numberOfItems: items.length,
    itemListElement: items.map((it, i) => ({ "@type": "ListItem", position: i + 1, name: it.name, url: url(it.path) })),
  };
}

/** Shared metadata builder: canonical, Open Graph, Twitter, robots. */
function clip(text: string, max = 160) {
  if (text.length <= max) return text;
  const cut = text.slice(0, max - 1);
  return `${cut.slice(0, Math.max(cut.lastIndexOf(" "), max - 20)).replace(/[,;:.\s]+$/, "")}…`;
}

export function meta(opts: { title: string; description: string; path: string; image?: string; noindex?: boolean; absoluteTitle?: boolean; type?: "website" | "article"; modified?: Date }): Metadata {
  opts = { ...opts, description: clip(opts.description) };
  const image = opts.image ?? url("/opengraph-image");
  return {
    title: opts.absoluteTitle ? { absolute: opts.title } : opts.title,
    description: opts.description,
    alternates: { canonical: opts.path },
    openGraph: {
      title: opts.title,
      description: opts.description,
      url: url(opts.path),
      siteName: SITE.name,
      type: opts.type ?? "website",
      locale: "en_US",
      images: [{ url: image, width: 1200, height: 630, alt: opts.title }],
      ...(opts.modified && { modifiedTime: opts.modified.toISOString() }),
    },
    twitter: { card: "summary_large_image", title: opts.title, description: opts.description, images: [image] },
    robots: opts.noindex ? { index: false, follow: true } : { index: true, follow: true, "max-image-preview": "large", "max-snippet": -1, "max-video-preview": -1 },
  };
}

export function year() {
  return new Date().getFullYear();
}
