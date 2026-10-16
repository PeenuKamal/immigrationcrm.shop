import { eq } from "drizzle-orm";
import { db, categories } from "@/db";
import { ogCard, OG_SIZE } from "@/components/og";
import { categorySeoName } from "@/lib/site";
export const size = OG_SIZE;
export const contentType = "image/png";
export default async function Image({ params }: { params: Promise<{ slug: string }> }) {
  const c = await db.query.categories.findFirst({ where: eq(categories.slug, (await params).slug) });
  return ogCard({ eyebrow: `Best of ${new Date().getFullYear()}`, title: c ? `Best ${categorySeoName(c.slug, c.name)}` : "Immigration software", subtitle: c?.description });
}
