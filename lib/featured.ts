import "server-only";
import { and, eq } from "drizzle-orm";
import { db, payments, software } from "@/db";

/** Marks a Stripe payment as paid and extends featured placement. Safe to call twice. */
export async function applyFeaturedPayment(sessionId: string) {
  const [p] = await db.update(payments).set({ status: "paid" }).where(and(eq(payments.stripeSessionId, sessionId), eq(payments.status, "pending"))).returning();
  if (!p) return null;
  const s = await db.query.software.findFirst({ where: eq(software.id, p.softwareId) });
  if (!s) return null;
  const start = s.featuredUntil && s.featuredUntil > new Date() ? new Date(s.featuredUntil) : new Date();
  start.setMonth(start.getMonth() + p.months);
  await db.update(software).set({ featuredUntil: start }).where(eq(software.id, s.id));
  return { software: s, until: start };
}
