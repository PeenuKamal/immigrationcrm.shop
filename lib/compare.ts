import "server-only";
import { and, desc, eq, inArray, sql } from "drizzle-orm";
import { db, software, reviews, softwareCategories, categories, type Software, type Review } from "@/db";
import type { Faq } from "./seo";

export const MAX_COMPARE = 4;
export const SEP = "-vs-";

export function parseCompare(param: string) {
  return decodeURIComponent(param).toLowerCase().split(SEP).map((s) => s.trim()).filter(Boolean);
}
export function comparePath(slugs: string[]) {
  return `/compare/${[...new Set(slugs)].sort().join(SEP)}`;
}

export type CompareItem = {
  s: Software;
  cats: { name: string; slug: string }[];
  agg: { ease: number; support: number; value: number; features: number; recommend: number };
  latest: Review[];
};

export async function loadCompare(slugs: string[]): Promise<CompareItem[]> {
  const rows = await db.select().from(software).where(and(inArray(software.slug, slugs), eq(software.status, "PUBLISHED")));
  const order = new Map(slugs.map((s, i) => [s, i]));
  rows.sort((a, b) => order.get(a.slug)! - order.get(b.slug)!);
  if (!rows.length) return [];
  const ids = rows.map((r) => r.id);
  const [catRows, aggRows, revRows] = await Promise.all([
    db
      .select({ id: softwareCategories.softwareId, name: categories.name, slug: categories.slug })
      .from(softwareCategories)
      .innerJoin(categories, eq(categories.id, softwareCategories.categoryId))
      .where(inArray(softwareCategories.softwareId, ids))
      .orderBy(categories.sortOrder),
    db
      .select({
        id: reviews.softwareId,
        ease: sql<number>`avg(${reviews.ratingEase})::float`,
        support: sql<number>`avg(${reviews.ratingSupport})::float`,
        value: sql<number>`avg(${reviews.ratingValue})::float`,
        features: sql<number>`avg(${reviews.ratingFeatures})::float`,
        recommend: sql<number>`avg(case when ${reviews.recommend} then 1 else 0 end)::float`,
      })
      .from(reviews)
      .where(and(inArray(reviews.softwareId, ids), eq(reviews.status, "PUBLISHED")))
      .groupBy(reviews.softwareId),
    db.select().from(reviews).where(and(inArray(reviews.softwareId, ids), eq(reviews.status, "PUBLISHED"))).orderBy(desc(reviews.publishedAt)),
  ]);
  return rows.map((s) => ({
    s,
    cats: catRows.filter((c) => c.id === s.id).map(({ name, slug }) => ({ name, slug })),
    agg: aggRows.find((a) => a.id === s.id) ?? { ease: 0, support: 0, value: 0, features: 0, recommend: 0 },
    latest: revRows.filter((r) => r.softwareId === s.id).slice(0, 3),
  }));
}

/** Competitors: other published listings sharing a category, best-rated first. */
export async function competitorsOf(softwareId: string, limit = 12) {
  const myCats = db.select({ c: softwareCategories.categoryId }).from(softwareCategories).where(eq(softwareCategories.softwareId, softwareId));
  const ids = db.selectDistinct({ id: softwareCategories.softwareId }).from(softwareCategories).where(inArray(softwareCategories.categoryId, myCats));
  const shared = sql<number>`(select count(*) from software_categories sc where sc.software_id = ${software.id} and sc.category_id in (select category_id from software_categories where software_id = ${softwareId}))::int`;
  return db
    .select()
    .from(software)
    .where(and(inArray(software.id, ids), eq(software.status, "PUBLISHED"), sql`${software.id} <> ${softwareId}`))
    .orderBy(desc(shared), desc(software.reviewCount), desc(software.ratingAvg), software.name)
    .limit(limit);
}

/** Every pair of published products that share a category — the comparisons people actually search for. */
export async function popularPairs() {
  const res = await db.execute<{ a: string; b: string; an: string; bn: string; cat: string }>(sql`
    select distinct on (least(sa.slug, sb.slug), greatest(sa.slug, sb.slug))
      least(sa.slug, sb.slug) as a, greatest(sa.slug, sb.slug) as b,
      case when sa.slug < sb.slug then sa.name else sb.name end as an,
      case when sa.slug < sb.slug then sb.name else sa.name end as bn,
      c.slug as cat
    from software_categories x
    join software_categories y on x.category_id = y.category_id and x.software_id < y.software_id
    join software sa on sa.id = x.software_id and sa.status = 'PUBLISHED'
    join software sb on sb.id = y.software_id and sb.status = 'PUBLISHED'
    join categories c on c.id = x.category_id
    order by least(sa.slug, sb.slug), greatest(sa.slug, sb.slug), c.sort_order`);
  return res.rows;
}

const has = (arr: string[], needle: RegExp) => arr.some((x) => needle.test(x));
const names = (items: CompareItem[]) => items.map((i) => i.s.name);
const join = (xs: string[]) => (xs.length <= 1 ? xs.join("") : `${xs.slice(0, -1).join(", ")} and ${xs[xs.length - 1]}`);

/** Factual Q&A built only from listing data and verified reviews — nothing invented. */
export function compareFaqs(items: CompareItem[]): Faq[] {
  const faqs: Faq[] = [];
  const title = join(names(items));
  const reviewed = items.filter((i) => i.s.reviewCount > 0);

  if (reviewed.length >= 2) {
    const best = [...reviewed].sort((a, b) => b.s.ratingAvg - a.s.ratingAvg)[0];
    faqs.push({
      q: `Which is rated higher: ${title}?`,
      a: `On ImmigrationCRM.shop, ${reviewed.map((i) => `${i.s.name} averages ${i.s.ratingAvg.toFixed(1)}/5 from ${i.s.reviewCount} verified review${i.s.reviewCount > 1 ? "s" : ""}`).join("; ")}. ${best.s.name} currently has the highest average. Every review comes from a practitioner with a verified work email plus LinkedIn or proof of use.`,
    });
  } else {
    faqs.push({
      q: `Which is better, ${title}?`,
      a: `There aren't enough verified reviews yet to compare ratings. The right choice depends on your market and practice type: ${items.map((i) => `${i.s.name} is ${i.s.tagline.replace(/\.$/, "").toLowerCase()}`).join("; ")}.`,
    });
  }

  const markets = ["Canada", "USA", "Australia", "India", "UK"];
  for (const m of markets) {
    const yes = items.filter((i) => i.s.regions.includes(m)).map((i) => i.s.name);
    if (yes.length && yes.length < items.length) {
      faqs.push({ q: `Which of ${title} supports immigration work in ${m}?`, a: `${join(yes)} list${yes.length === 1 ? "s" : ""} ${m} as a market served. ${join(names(items).filter((n) => !yes.includes(n)))} ${items.length - yes.length === 1 ? "doesn't" : "don't"} list ${m}.` });
    }
  }

  const rcic = items.filter((i) => has(i.s.audiences, /RCIC|consultant|migration agent/i)).map((i) => i.s.name);
  if (rcic.length && rcic.length < items.length) {
    faqs.push({ q: `Which is built for immigration consultants and RCICs?`, a: `${join(rcic)} ${rcic.length === 1 ? "is" : "are"} built for immigration consultants, RCICs or migration agents. The others are aimed mainly at law firms or employers.` });
  }

  const ai = items.filter((i) => i.cats.some((c) => c.slug === "ai-immigration") || has(i.s.features, /\bAI\b|drafting/i)).map((i) => i.s.name);
  if (ai.length && ai.length < items.length) {
    faqs.push({ q: `Which one has AI drafting or AI features?`, a: `${join(ai)} offer${ai.length === 1 ? "s" : ""} AI drafting or AI-assisted features according to ${ai.length === 1 ? "its" : "their"} listing.` });
  }

  const portal = items.filter((i) => has(i.s.features, /portal/i)).map((i) => i.s.name);
  if (portal.length && portal.length < items.length) {
    faqs.push({ q: `Which has a client portal?`, a: `${join(portal)} list${portal.length === 1 ? "s" : ""} a client portal as a key feature.` });
  }

  faqs.push({
    q: `Can I use ${title} together?`,
    a: `Many firms combine tools — for example a practice management suite for billing with an immigration-specific forms or AI drafting tool. Check each vendor's integrations before buying.`,
  });
  return faqs.slice(0, 7);
}

export function profileFaqs(s: Software, cats: { name: string }[], competitors: Software[]): Faq[] {
  const faqs: Faq[] = [
    { q: `What is ${s.name}?`, a: s.description },
    {
      q: `Who is ${s.name} for?`,
      a: s.audiences.length ? `${s.name} is built for ${join(s.audiences.map((a) => a.toLowerCase()))}${s.regions.length ? `, serving ${join(s.regions)}` : ""}.` : `${s.name} is used by immigration practices${s.regions.length ? ` in ${join(s.regions)}` : ""}.`,
    },
  ];
  if (s.features.length) faqs.push({ q: `What are ${s.name}'s key features?`, a: `${s.name}'s key features include ${join(s.features.slice(0, 8).map((f) => f.toLowerCase()))}.` });
  faqs.push({
    q: `Is ${s.name} any good? What do users say?`,
    a: s.reviewCount
      ? `${s.name} has an average rating of ${s.ratingAvg.toFixed(1)} out of 5 from ${s.reviewCount} verified review${s.reviewCount > 1 ? "s" : ""} on ImmigrationCRM.shop. Each reviewer verified a work email plus LinkedIn or proof of use.`
      : `${s.name} has no verified reviews on ImmigrationCRM.shop yet. If your firm uses it, you can write the first one.`,
  });
  if (s.pricingNote) faqs.push({ q: `How much does ${s.name} cost?`, a: `${s.pricingNote}. Confirm current pricing with the vendor.` });
  if (s.deployment) faqs.push({ q: `Is ${s.name} cloud-based?`, a: s.deployment === "Cloud" ? `Yes. ${s.name} is cloud-based software you use in a web browser.` : `${s.name} is listed as ${s.deployment.toLowerCase()} software.` });
  if (competitors.length) faqs.push({ q: `What are the best alternatives to ${s.name}?`, a: `Popular alternatives in ${cats[0]?.name.toLowerCase() ?? "this category"} include ${join(competitors.slice(0, 5).map((c) => c.name))}.` });
  return faqs;
}
