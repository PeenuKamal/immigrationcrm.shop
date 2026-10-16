import type { PoolConfig } from "pg";

/**
 * Finds the Postgres connection string. Supports our own DATABASE_URL plus the names
 * that Supabase / Hostinger / Vercel integrations set automatically.
 */
export function databaseUrl() {
  const url =
    process.env.DATABASE_URL ||
    process.env.POSTGRES_URL_NON_POOLING ||
    process.env.POSTGRES_URL ||
    process.env.SUPABASE_DB_URL ||
    process.env.POSTGRES_PRISMA_URL;
  if (!url) throw new Error("No database configured. Set DATABASE_URL to your Postgres connection string.");
  return url;
}

/** Pool settings: local databases connect without SSL, hosted ones (Supabase, Neon…) with SSL. */
export function poolConfig(max = 5): PoolConfig {
  const raw = databaseUrl();
  const u = new URL(raw);
  const local = ["localhost", "127.0.0.1", "::1", "db"].includes(u.hostname);
  const sslmode = u.searchParams.get("sslmode");
  // We set SSL explicitly, so drop sslmode to stop pg from overriding it with strict verification.
  u.searchParams.delete("sslmode");
  return {
    connectionString: u.toString(),
    max,
    ssl: local || sslmode === "disable" ? undefined : { rejectUnauthorized: false },
    connectionTimeoutMillis: 15_000,
    idleTimeoutMillis: 30_000,
  };
}
