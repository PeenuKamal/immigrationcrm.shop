import "dotenv/config";
import { drizzle } from "drizzle-orm/node-postgres";
import { Pool } from "pg";
import { eq } from "drizzle-orm";
import * as schema from "./schema";
import { CATEGORIES, SOFTWARE } from "./seed-data";

const slugify = (s: string) => s.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
const domainOf = (url?: string) => (url ? new URL(url).hostname.replace(/^www\./, "") : null);

async function main() {
  const pool = new Pool({ connectionString: process.env.DATABASE_URL });
  const db = drizzle(pool, { schema });

  const catIds = new Map<string, string>();
  for (const [i, c] of CATEGORIES.entries()) {
    const existing = await db.query.categories.findFirst({ where: eq(schema.categories.slug, c.slug) });
    if (existing) {
      catIds.set(c.slug, existing.id);
      continue;
    }
    const [row] = await db.insert(schema.categories).values({ ...c, sortOrder: i }).returning();
    catIds.set(c.slug, row.id);
  }

  let added = 0;
  for (const s of SOFTWARE) {
    const slug = slugify(s.name);
    const existing = await db.query.software.findFirst({ where: eq(schema.software.slug, slug) });
    if (existing) continue;
    const [row] = await db
      .insert(schema.software)
      .values({
        slug,
        name: s.name,
        tagline: s.tagline,
        description: s.description,
        website: s.website ?? null,
        domain: domainOf(s.website),
        vendorName: s.vendorName ?? null,
        deployment: s.deployment ?? null,
        regions: s.regions,
        audiences: s.audiences,
        features: s.features,
        status: "PUBLISHED",
      })
      .returning();
    await db.insert(schema.softwareCategories).values(s.categories.map((c) => ({ softwareId: row.id, categoryId: catIds.get(c)! })));
    added++;
  }
  console.log(`Seeded ${CATEGORIES.length} categories and ${added} new listings.`);
  await pool.end();
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
