import path from "path";
import { drizzle } from "drizzle-orm/node-postgres";
import { migrate } from "drizzle-orm/node-postgres/migrator";
import { db } from "./index";
import * as schema from "./schema";
import { seedDatabase } from "./seed-run";

/**
 * Runs on server start: creates or updates tables from db/migrations, then adds the starter
 * listings if the directory is empty. No terminal access needed on the host.
 */
export async function setupDatabase() {
  if (process.env.SKIP_DB_SETUP === "true") return;
  const started = Date.now();
  let client;
  try {
    client = await db.$client.connect();
    const cdb = drizzle(client, { schema });
    // Session lock on one connection so two server processes starting together don't migrate at once.
    await client.query("select pg_advisory_lock(727001)");
    try {
      await migrate(cdb, { migrationsFolder: path.join(process.cwd(), "db", "migrations") });
      const { rows } = await client.query<{ n: number }>("select count(*)::int as n from categories");
      if (rows[0].n === 0) {
        const res = await seedDatabase(cdb);
        console.log(`[setup] Added ${res.categories} categories and ${res.added} starter listings.`);
      }
    } finally {
      await client.query("select pg_advisory_unlock(727001)");
    }
    console.log(`[setup] Database ready in ${Date.now() - started} ms.`);
  } catch (e) {
    console.error("[setup] Database setup failed — check DATABASE_URL.", e);
  } finally {
    client?.release();
  }
}
