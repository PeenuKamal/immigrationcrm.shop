import { drizzle } from "drizzle-orm/node-postgres";
import { Pool } from "pg";
import * as schema from "./schema";
import { poolConfig } from "./connection";

const globalForDb = globalThis as unknown as { pool?: Pool };

function makePool() {
  try {
    return new Pool(poolConfig(5));
  } catch {
    // No database configured yet (e.g. during `next build`). Queries will fail with a clear error later.
    return new Pool({ max: 1 });
  }
}

const pool = globalForDb.pool ?? makePool();
globalForDb.pool = pool;

export const db = drizzle(pool, { schema });
export * from "./schema";
