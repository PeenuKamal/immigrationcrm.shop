"use server";
import { eq } from "drizzle-orm";
import { revalidatePath } from "next/cache";
import { db, reviews, software, listingClaims, users } from "@/db";
import { requireAdmin } from "@/lib/session";
import { refreshReviewStatus, recalcRating, pingSoftware } from "@/lib/reviews";
import { sendEmail } from "@/lib/email";
import { appUrl, domainFromUrl } from "@/lib/util";

async function notify(userId: string, subject: string, body: string) {
  const u = await db.query.users.findFirst({ where: eq(users.id, userId) });
  if (u) await sendEmail(u.email, subject, `${body}\n\n— ImmigrationCRM.shop`).catch(() => {});
}

export async function moderateProof(form: FormData) {
  await requireAdmin();
  const id = String(form.get("id"));
  const approve = form.get("decision") === "approve";
  const r = await db.query.reviews.findFirst({ where: eq(reviews.id, id) });
  if (!r) return;
  await db.update(reviews).set({ proofStatus: approve ? "APPROVED" : "REJECTED", updatedAt: new Date() }).where(eq(reviews.id, id));
  await refreshReviewStatus(id);
  const after = await db.query.reviews.findFirst({ where: eq(reviews.id, id) });
  await notify(
    r.userId,
    approve ? "Your proof of use was accepted" : "We couldn't accept your proof of use",
    approve
      ? after?.status === "PUBLISHED" ? `Your review "${r.title}" is now live.` : `Your proof was accepted. Your review "${r.title}" is moving to the next step.`
      : `Your upload for "${r.title}" didn't show that you use the software. Upload a clearer screenshot or an invoice at ${appUrl()}/account.`
  );
  revalidatePath("/admin");
}

export async function moderateReview(form: FormData) {
  await requireAdmin();
  const id = String(form.get("id"));
  const decision = String(form.get("decision"));
  const reason = String(form.get("reason") || "").trim();
  const r = await db.query.reviews.findFirst({ where: eq(reviews.id, id) });
  if (!r) return;
  if (decision === "approve") {
    await db.update(reviews).set({ status: "PUBLISHED", publishedAt: r.publishedAt ?? new Date(), updatedAt: new Date() }).where(eq(reviews.id, id));
    await notify(r.userId, "Your review is live", `Your review "${r.title}" has been published.`);
  } else {
    await db.update(reviews).set({ status: "REJECTED", rejectReason: reason || "This review didn't meet our guidelines.", updatedAt: new Date() }).where(eq(reviews.id, id));
    await notify(r.userId, "Your review wasn't published", `Your review "${r.title}" wasn't published. Reason: ${reason || "it didn't meet our guidelines."}`);
  }
  await recalcRating(r.softwareId);
  await pingSoftware(r.softwareId);
  revalidatePath("/admin");
}

export async function moderateListing(form: FormData) {
  await requireAdmin();
  const id = String(form.get("id"));
  const approve = form.get("decision") === "approve";
  await db.update(software).set({ status: approve ? "PUBLISHED" : "REJECTED", updatedAt: new Date() }).where(eq(software.id, id));
  if (approve) await pingSoftware(id);
  revalidatePath("/admin");
  revalidatePath("/software");
}

export async function moderateClaim(form: FormData) {
  await requireAdmin();
  const id = String(form.get("id"));
  const approve = form.get("decision") === "approve";
  const c = await db.query.listingClaims.findFirst({ where: eq(listingClaims.id, id) });
  if (!c) return;
  await db.update(listingClaims).set({ status: approve ? "PUBLISHED" : "REJECTED" }).where(eq(listingClaims.id, id));
  if (approve) {
    const s = await db.query.software.findFirst({ where: eq(software.id, c.softwareId) });
    const owner = await db.query.users.findFirst({ where: eq(users.id, c.userId) });
    // If the listing had no verified domain, adopt the claimant's company domain so self-review blocking works.
    await db.update(software).set({ claimed: true, ownerId: c.userId, domain: s?.domain ?? owner?.emailDomain ?? null }).where(eq(software.id, c.softwareId));
    await notify(c.userId, "Your listing claim was approved", `You can now manage ${s?.name} at ${appUrl()}/vendor/${c.softwareId}.`);
  }
  revalidatePath("/admin");
}

export async function setListingDomain(form: FormData) {
  await requireAdmin();
  const id = String(form.get("id"));
  const domain = domainFromUrl(String(form.get("domain") || ""));
  await db.update(software).set({ domain }).where(eq(software.id, id));
  revalidatePath("/admin");
}

export async function setFeaturedManually(form: FormData) {
  await requireAdmin();
  const id = String(form.get("id"));
  const until = String(form.get("until") || "");
  await db.update(software).set({ featuredUntil: until ? new Date(`${until}T23:59:59Z`) : null }).where(eq(software.id, id));
  revalidatePath("/admin");
}
