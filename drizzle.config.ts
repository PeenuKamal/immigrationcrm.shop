import "dotenv/config";
import { defineConfig } from "drizzle-kit";
import { databaseUrl } from "./db/connection";

export default defineConfig({
  schema: "./db/schema.ts",
  out: "./db/migrations",
  dialect: "postgresql",
  dbCredentials: { url: process.env.DATABASE_URL || (() => { try { return databaseUrl(); } catch { return "postgres://localhost/placeholder"; } })() },
});
