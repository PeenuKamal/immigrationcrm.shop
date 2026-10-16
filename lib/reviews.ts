import "server-only";
import { and, eq, sql } from "drizzle-orm";
import { db, reviews, software, users, type Review, type User } from "@/db";
import { pingIndexNow } from "./indexnow";

/**
 * Verification policy
 * - Every reviewer signs in with a verified work email (personal mailboxes are refused).
 * - On top of that, a review needs ONE more proof: a connected LinkedIn profile, or an
 *   uploaded proof of use (screenshot or invoice) that an admin has approved.
 * - Nobody can review software made by their own employer (email domain matches vendor domain).
 */
export function nextStatus(review: Pick<Review, "verifiedEmail" | "verifiedLinkedin" | "proofStatus" | "status">): Review["status"] {
  if (review.status === "REJECTED") return "REJECTED";
  if (!review.verifiedEmail) return "AWAITING_VERIFICATION";
  const extraProof = review.verifiedLinkedin || review.proofStatus === "APPROVED";
  if (!extraProof) return review.proofStatus === "PENDING" ? "PENDING_PROOF" : "AWAITING_VERIFICATION";
  if (process.env.REQUIRE_ADMIN_APPROVAL === "true" && review.status !== "PUBLISHED") return "PENDING_ADMIN";
  return "PUBLISHED";
}

export async function refreshReviewStatus(reviewId: string) {
  const review = await db.query.reviews.findFirst({ where: eq(reviews.id, reviewId) });
  if (!review) return;
  const status = nextStatus(review);
  if (status !== review.status) {
    await db
      .update(reviews)
      .set({ status, publishedAt: status === "PUBLISHED" ? review.publishedAt ?? new Date() : review.publishedAt, updatedAt: new Date() })
      .where(eq(reviews.id, reviewId));
  }
  await recalcRating(review.softwareId);
  if (status === "PUBLISHED" && review.status !== "PUBLISHED") await pingSoftware(review.softwareId);
}

/** Re-checks every review a user owns, e.g. after they connect LinkedIn. */
export async function refreshUserReviews(user: User) {
  await db
    .update(reviews)
    .set({ verifiedEmail: !!user.emailVerifiedAt && user.isWorkEmail, verifiedLinkedin: !!user.linkedinVerifiedAt })
    .where(eq(reviews.userId, user.id));
  const mine = await db.select({ id: reviews.id }).from(reviews).where(eq(reviews.userId, user.id));
  for (const r of mine) await refreshReviewStatus(r.id);
}

export async function recalcRating(softwareId: string) {
  const [agg] = await db
    .select({ avg: sql<number>`coalesce(avg(${reviews.rating}), 0)::float`, count: sql<number>`count(*)::int` })
    .from(reviews)
    .where(and(eq(reviews.softwareId, softwareId), eq(reviews.status, "PUBLISHED")));
  await db
    .update(software)
    .set({ ratingAvg: Math.round((agg?.avg ?? 0) * 10) / 10, reviewCount: agg?.count ?? 0 })
    .where(eq(software.id, softwareId));
}

export const STATUS_COPY: Record<Review["status"], { label: string; help: string }> = {
  AWAITING_VERIFICATION: {
    label: "Needs one more proof",
    help: "Connect LinkedIn or upload proof that you use this software. Your review goes live as soon as one is confirmed.",
  },
  PENDING_PROOF: { label: "Proof under review", help: "We're checking your proof of use. This usually takes one business day." },
  PENDING_ADMIN: { label: "Awaiting approval", help: "Your review is verified and waiting for a final editorial check." },
  PUBLISHED: { label: "Live", help: "Your verified review is public." },
  REJECTED: { label: "Not published", help: "This review didn't meet our guidelines." },
};

export async function userById(id: string) {
  return db.query.users.findFirst({ where: eq(users.id, id) });
}

export async function pingSoftware(softwareId: string) {
  const s = await db.query.software.findFirst({ where: eq(software.id, softwareId) });
  if (s) pingIndexNow([`/software/${s.slug}`, `/software/${s.slug}/alternatives`, "/"]);
}
