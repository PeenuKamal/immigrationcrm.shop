import "dotenv/config";
import { drizzle } from "drizzle-orm/node-postgres";
import { Pool } from "pg";
import * as schema from "./schema";
import { poolConfig } from "./connection";
import { seedDatabase } from "./seed-run";

async function main() {
  const pool = new Pool(poolConfig(1));
  const { categories, added } = await seedDatabase(drizzle(pool, { schema }));
  console.log(`Seeded ${categories} categories and ${added} new listings.`);
  await pool.end();
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
