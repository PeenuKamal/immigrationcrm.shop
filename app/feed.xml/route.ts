import { recentReviews } from "@/lib/queries";
import { SITE, url } from "@/lib/site";

export const dynamic = "force-dynamic";
const x = (s: string) => s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");

export async function GET() {
  const items = await recentReviews(50);
  const body = `<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title>${x(SITE.name)} — latest verified reviews</title>
<link>${url("/")}</link>
<atom:link href="${url("/feed.xml")}" rel="self" type="application/rss+xml"/>
<description>${x(SITE.description)}</description>
<language>en</language>
${items
  .map(
    ({ r, s }) => `<item>
<title>${x(`${s.name}: ${r.title} (${r.rating}/5)`)}</title>
<link>${url(`/software/${s.slug}#review-${r.id}`)}</link>
<guid isPermaLink="false">${r.id}</guid>
<pubDate>${(r.publishedAt ?? r.createdAt).toUTCString()}</pubDate>
<description>${x(`Verified ${r.reviewerRole} (${r.firmSize}). Pros: ${r.pros.replace(/\n/g, "; ")} Cons: ${r.cons.replace(/\n/g, "; ")}`)}</description>
</item>`
  )
  .join("\n")}
</channel>
</rss>`;
  return new Response(body, { headers: { "Content-Type": "application/rss+xml; charset=utf-8", "Cache-Control": "public, max-age=900" } });
}
