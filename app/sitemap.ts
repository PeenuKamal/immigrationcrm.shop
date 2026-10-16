import type { MetadataRoute } from "next";
import { eq, desc } from "drizzle-orm";
import { db, software, categories } from "@/db";
import { popularPairs, comparePath } from "@/lib/compare";
import { url } from "@/lib/site";

export const dynamic = "force-dynamic";

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const [list, cats, pairs] = await Promise.all([
    db.select({ slug: software.slug, updatedAt: software.updatedAt, n: software.reviewCount }).from(software).where(eq(software.status, "PUBLISHED")).orderBy(desc(software.updatedAt)),
    db.select({ slug: categories.slug }).from(categories),
    popularPairs(),
  ]);
  const mod = new Map(list.map((s) => [s.slug, s.updatedAt]));
  const newest = list[0]?.updatedAt ?? new Date();
  return [
    { url: url("/"), lastModified: newest, changeFrequency: "daily", priority: 1 },
    { url: url("/software"), lastModified: newest, changeFrequency: "daily", priority: 0.9 },
    { url: url("/compare"), lastModified: newest, changeFrequency: "weekly", priority: 0.8 },
    { url: url("/how-we-verify"), changeFrequency: "monthly", priority: 0.5 },
    { url: url("/about"), changeFrequency: "monthly", priority: 0.5 },
    { url: url("/list-your-software"), changeFrequency: "monthly", priority: 0.4 },
    ...cats.map((c) => ({ url: url(`/category/${c.slug}`), lastModified: newest, changeFrequency: "daily" as const, priority: 0.9 })),
    ...list.map((s) => ({ url: url(`/software/${s.slug}`), lastModified: s.updatedAt, changeFrequency: "weekly" as const, priority: 0.8 })),
    ...list.map((s) => ({ url: url(`/software/${s.slug}/alternatives`), lastModified: s.updatedAt, changeFrequency: "weekly" as const, priority: 0.7 })),
    ...pairs.map((p) => {
      const a = mod.get(p.a), b = mod.get(p.b);
      return { url: url(comparePath([p.a, p.b])), lastModified: a && b ? (a > b ? a : b) : newest, changeFrequency: "weekly" as const, priority: 0.6 };
    }),
  ];
}
