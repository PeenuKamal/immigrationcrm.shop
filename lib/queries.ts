import "server-only";
import { and, desc, eq, ilike, inArray, or, sql, arrayContains } from "drizzle-orm";
import { db, software, categories, softwareCategories, reviews, users } from "@/db";

export async function allCategoriesWithCounts() {
  return db
    .select({
      id: categories.id,
      slug: categories.slug,
      name: categories.name,
      description: categories.description,
      count: sql<number>`count(${software.id}) filter (where ${software.status} = 'PUBLISHED')::int`,
    })
    .from(categories)
    .leftJoin(softwareCategories, eq(softwareCategories.categoryId, categories.id))
    .leftJoin(software, eq(software.id, softwareCategories.softwareId))
    .groupBy(categories.id)
    .orderBy(categories.sortOrder);
}

export type SortKey = "featured" | "rating" | "reviews" | "name" | "newest";

export async function searchSoftware(opts: { q?: string; category?: string; region?: string; sort?: SortKey; limit?: number }) {
  const conds = [eq(software.status, "PUBLISHED")];
  if (opts.q) {
    const like = `%${opts.q.replace(/[%_]/g, "")}%`;
    conds.push(
      or(
        ilike(software.name, like),
        ilike(software.tagline, like),
        ilike(software.description, like),
        ilike(software.vendorName, like),
        sql`array_to_string(${software.features}, ' ') ilike ${like}`
      )!
    );
  }
  if (opts.category) {
    const cat = await db.query.categories.findFirst({ where: eq(categories.slug, opts.category) });
    if (!cat) return [];
    const ids = db.select({ id: softwareCategories.softwareId }).from(softwareCategories).where(eq(softwareCategories.categoryId, cat.id));
    conds.push(inArray(software.id, ids));
  }
  if (opts.region) conds.push(arrayContains(software.regions, [opts.region]));

  const featuredFirst = sql`case when ${software.featuredUntil} > now() then 0 else 1 end`;
  const order = {
    featured: [featuredFirst, desc(software.reviewCount), desc(software.ratingAvg), software.name],
    rating: [desc(software.ratingAvg), desc(software.reviewCount), software.name],
    reviews: [desc(software.reviewCount), desc(software.ratingAvg), software.name],
    name: [software.name],
    newest: [desc(software.createdAt)],
  }[opts.sort ?? "featured"];

  return db.select().from(software).where(and(...conds)).orderBy(...order).limit(opts.limit ?? 200);
}

export async function categoryNamesFor(ids: string[]) {
  if (!ids.length) return new Map<string, string[]>();
  const rows = await db
    .select({ softwareId: softwareCategories.softwareId, name: categories.name })
    .from(softwareCategories)
    .innerJoin(categories, eq(categories.id, softwareCategories.categoryId))
    .where(inArray(softwareCategories.softwareId, ids))
    .orderBy(categories.sortOrder);
  const map = new Map<string, string[]>();
  for (const r of rows) map.set(r.softwareId, [...(map.get(r.softwareId) ?? []), r.name]);
  return map;
}

export async function allRegions() {
  const rows = await db.execute<{ region: string; n: number }>(
    sql`select unnest(regions) as region, count(*)::int as n from software where status = 'PUBLISHED' group by 1 order by 2 desc, 1`
  );
  return rows.rows;
}

export async function recentReviews(limit = 4) {
  return db
    .select({ r: reviews, s: { name: software.name, slug: software.slug }, u: { name: users.name, company: users.company, linkedinName: users.linkedinName } })
    .from(reviews)
    .innerJoin(software, eq(software.id, reviews.softwareId))
    .innerJoin(users, eq(users.id, reviews.userId))
    .where(eq(reviews.status, "PUBLISHED"))
    .orderBy(desc(reviews.publishedAt))
    .limit(limit);
}

export async function publishedCount() {
  const [r] = await db.select({ n: sql<number>`count(*)::int` }).from(software).where(eq(software.status, "PUBLISHED"));
  return r?.n ?? 0;
}
