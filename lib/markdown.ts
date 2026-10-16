import "server-only";
import { and, desc, eq } from "drizzle-orm";
import { db, software, reviews, categories, softwareCategories, type Software } from "@/db";
import { url, SITE } from "./site";
import { lines, formatDate } from "./util";
import { competitorsOf, comparePath, profileFaqs } from "./compare";

const esc = (s: string) => s.replace(/\r?\n+/g, " ").trim();

export async function softwareMarkdown(slug: string, opts: { reviewsLimit?: number; heading?: "#" | "##" } = {}) {
  const s = await db.query.software.findFirst({ where: and(eq(software.slug, slug), eq(software.status, "PUBLISHED")) });
  if (!s) return null;
  return softwareMd(s, opts);
}

export async function softwareMd(s: Software, { reviewsLimit = 50, heading = "#" }: { reviewsLimit?: number; heading?: "#" | "##" } = {}) {
  const h2 = heading === "#" ? "##" : "###";
  const [cats, revs, comps] = await Promise.all([
    db.select({ name: categories.name, slug: categories.slug }).from(softwareCategories).innerJoin(categories, eq(categories.id, softwareCategories.categoryId)).where(eq(softwareCategories.softwareId, s.id)),
    db.select().from(reviews).where(and(eq(reviews.softwareId, s.id), eq(reviews.status, "PUBLISHED"))).orderBy(desc(reviews.publishedAt)).limit(reviewsLimit),
    competitorsOf(s.id, 6),
  ]);
  const out: string[] = [];
  out.push(`${heading} ${s.name}`, "", `> ${s.tagline}`, "");
  out.push(`- URL: ${url(`/software/${s.slug}`)}`);
  if (s.website) out.push(`- Official website: ${s.website}`);
  if (s.vendorName) out.push(`- Vendor: ${s.vendorName}`);
  if (cats.length) out.push(`- Category: ${cats.map((c) => c.name).join(", ")}`);
  if (s.deployment) out.push(`- Deployment: ${s.deployment}`);
  if (s.regions.length) out.push(`- Markets served: ${s.regions.join(", ")}`);
  if (s.audiences.length) out.push(`- Built for: ${s.audiences.join(", ")}`);
  if (s.pricingNote) out.push(`- Pricing: ${s.pricingNote}`);
  out.push(`- Verified rating: ${s.reviewCount ? `${s.ratingAvg.toFixed(1)}/5 from ${s.reviewCount} verified review${s.reviewCount > 1 ? "s" : ""}` : "no verified reviews yet"}`);
  out.push(`- Last updated: ${s.updatedAt.toISOString().slice(0, 10)}`, "");
  out.push(`${h2} About`, "", s.description, "");
  if (s.features.length) out.push(`${h2} Key features`, "", ...s.features.map((f) => `- ${f}`), "");
  if (revs.length) {
    out.push(`${h2} Verified reviews`, "");
    for (const r of revs) {
      out.push(`**${esc(r.title)}** — ${r.rating}/5 · ${r.reviewerRole}, ${r.firmSize}, used ${r.usageDuration.toLowerCase()} · ${formatDate(r.publishedAt ?? r.createdAt)}`, "");
      out.push(`Pros:`, ...lines(r.pros).map((l) => `- ${l}`), `Cons:`, ...lines(r.cons).map((l) => `- ${l}`), `How they use it: ${esc(r.useCase)}`, "");
    }
  }
  if (comps.length) {
    out.push(`${h2} Alternatives and comparisons`, "", ...comps.map((c) => `- [${s.name} vs ${c.name}](${url(comparePath([s.slug, c.slug]))})`), `- [All ${s.name} alternatives](${url(`/software/${s.slug}/alternatives`)})`, "");
  }
  if (heading === "#") {
    out.push(`${h2} FAQ`, "");
    for (const f of profileFaqs(s, cats, comps)) out.push(`**${f.q}**`, "", f.a, "");
    out.push("---", `Source: ${SITE.name} (${url("/")}). Every review is from a practitioner with a verified work email plus LinkedIn or proof of use.`);
  }
  return out.join("\n");
}

export const MD_HEADERS = { "Content-Type": "text/markdown; charset=utf-8", "Cache-Control": "public, max-age=3600, s-maxage=3600", "X-Robots-Tag": "noindex, follow" };
