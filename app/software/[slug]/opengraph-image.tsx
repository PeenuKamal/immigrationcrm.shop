import { and, eq } from "drizzle-orm";
import { db, software } from "@/db";
import { ogCard, OG_SIZE } from "@/components/og";
export const size = OG_SIZE;
export const contentType = "image/png";
export default async function Image({ params }: { params: Promise<{ slug: string }> }) {
  const s = await db.query.software.findFirst({ where: and(eq(software.slug, (await params).slug), eq(software.status, "PUBLISHED")) });
  if (!s) return ogCard({ eyebrow: "Software reviews", title: "ImmigrationCRM.shop" });
  return ogCard({
    eyebrow: s.reviewCount ? `${s.reviewCount} verified review${s.reviewCount > 1 ? "s" : ""}` : "Reviews, features & alternatives",
    title: `${s.name} reviews`,
    subtitle: s.tagline,
    rating: s.reviewCount ? s.ratingAvg.toFixed(1) : undefined,
  });
}
