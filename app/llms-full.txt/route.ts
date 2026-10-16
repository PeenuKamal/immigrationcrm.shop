import { eq } from "drizzle-orm";
import { db, software } from "@/db";
import { allCategoriesWithCounts } from "@/lib/queries";
import { softwareMd } from "@/lib/markdown";
import { SITE, url } from "@/lib/site";

export const dynamic = "force-dynamic";

export async function GET() {
  const [cats, list] = await Promise.all([allCategoriesWithCounts(), db.select().from(software).where(eq(software.status, "PUBLISHED")).orderBy(software.name)]);
  const parts = [
    `# ${SITE.name} — full content`,
    "",
    `> ${SITE.description}`,
    "",
    `Generated ${new Date().toISOString().slice(0, 10)}. Canonical site: ${url("/")}. Review verification: work email plus LinkedIn or moderator-checked proof of use; vendors cannot review their own products.`,
    "",
    "## Categories",
    ...cats.map((c) => `- ${c.name} (${c.count} tools): ${c.description} — ${url(`/category/${c.slug}`)}`),
    "",
  ];
  for (const s of list) parts.push(await softwareMd(s, { heading: "##", reviewsLimit: 10 }), "");
  return new Response(parts.join("\n"), { headers: { "Content-Type": "text/plain; charset=utf-8", "Cache-Control": "public, max-age=3600" } });
}
