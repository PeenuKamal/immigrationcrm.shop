import { eq } from "drizzle-orm";
import { db, categories } from "@/db";
import { searchSoftware } from "@/lib/queries";
import { MD_HEADERS } from "@/lib/markdown";
import { url } from "@/lib/site";

export async function GET(_: Request, { params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const c = await db.query.categories.findFirst({ where: eq(categories.slug, slug) });
  if (!c) return new Response("Not found", { status: 404 });
  const list = await searchSoftware({ category: slug, sort: "rating" });
  const md = [
    `# Best ${c.name} software`, "", `> ${c.description}`, "",
    `Ranked by verified rating, then number of verified reviews. Source: ${url(`/category/${c.slug}`)}`, "",
    ...list.map((s, i) => `${i + 1}. [${s.name}](${url(`/software/${s.slug}`)}) — ${s.tagline} ${s.reviewCount ? `Rated ${s.ratingAvg.toFixed(1)}/5 (${s.reviewCount} verified reviews).` : "No verified reviews yet."} Markets: ${s.regions.join(", ") || "n/a"}.`),
  ].join("\n");
  return new Response(md, { headers: { ...MD_HEADERS, Link: `<${url(`/category/${c.slug}`)}>; rel="canonical"` } });
}
