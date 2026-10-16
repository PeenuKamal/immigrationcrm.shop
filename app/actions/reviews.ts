"use server";
import { and, eq } from "drizzle-orm";
import { redirect } from "next/navigation";
import { revalidatePath } from "next/cache";
import { db, reviews, software, users } from "@/db";
import { requireUser } from "@/lib/session";
import { storeProof } from "@/lib/storage";
import { refreshReviewStatus } from "@/lib/reviews";
import { sameOrganisation } from "@/lib/util";

export type ReviewState = { error?: string; fields?: Record<string, string> } | undefined;

const ROLES = ["Immigration attorney", "RCIC / licensed consultant", "Migration agent", "Paralegal / case manager", "Firm owner / partner", "Operations / IT", "HR / global mobility", "Other"];
const SIZES = ["Solo", "2–10 people", "11–50 people", "51–200 people", "200+ people"];
const DURATIONS = ["Less than 6 months", "6–12 months", "1–2 years", "2+ years"];
export async function reviewOptions() {
  return { ROLES, SIZES, DURATIONS };
}

function star(form: FormData, key: string) {
  const n = Number(form.get(key));
  return Number.isInteger(n) && n >= 1 && n <= 5 ? n : null;
}

export async function submitReview(_: ReviewState, form: FormData): Promise<ReviewState> {
  const softwareId = String(form.get("softwareId"));
  const s = await db.query.software.findFirst({ where: eq(software.id, softwareId) });
  if (!s) return { error: "This listing no longer exists." };
  const user = await requireUser(`/software/${s.slug}/review`);

  const fields: Record<string, string> = {};
  for (const k of ["title", "pros", "cons", "useCase", "reviewerRole", "firmSize", "usageDuration", "jobTitle", "company"]) fields[k] = String(form.get(k) ?? "").trim();
  const ratings = { rating: star(form, "rating"), ratingEase: star(form, "ratingEase"), ratingSupport: star(form, "ratingSupport"), ratingValue: star(form, "ratingValue"), ratingFeatures: star(form, "ratingFeatures") };

  if (!user.isWorkEmail || !user.emailVerifiedAt) return { error: "Sign in with a work email to leave a review.", fields };
  if (sameOrganisation(user.emailDomain, s.domain)) return { error: `Your email is on ${s.name}'s own domain, so you can't review it. Ask a customer to review it instead.`, fields };
  if (Object.values(ratings).some((v) => v === null)) return { error: "Give a star rating for each of the five questions.", fields };
  if (fields.title.length < 8) return { error: "Add a headline of at least a few words.", fields };
  if (fields.pros.length < 40) return { error: "Describe what works in at least 40 characters — specifics help other firms most.", fields };
  if (fields.cons.length < 40) return { error: "Describe what doesn't work in at least 40 characters. Every tool has trade-offs.", fields };
  if (fields.useCase.length < 30) return { error: "Tell readers how your firm uses it, in at least 30 characters.", fields };
  if (!ROLES.includes(fields.reviewerRole) || !SIZES.includes(fields.firmSize) || !DURATIONS.includes(fields.usageDuration)) return { error: "Choose your role, firm size and how long you've used it.", fields };
  const recommend = form.get("recommend");
  if (recommend !== "yes" && recommend !== "no") return { error: "Say whether you'd recommend it to a peer.", fields };
  if (form.get("consent") !== "on") return { error: "Confirm that the review is your own honest experience.", fields };

  const existing = await db.query.reviews.findFirst({ where: and(eq(reviews.softwareId, s.id), eq(reviews.userId, user.id)) });
  if (existing) return { error: "You've already reviewed this software. You can see it on your account page.", fields };

  let proofKey: string | null = null;
  const file = form.get("proof");
  if (file instanceof File && file.size > 0) {
    try {
      proofKey = await storeProof(file);
    } catch (e) {
      return { error: (e as Error).message, fields };
    }
  }

  if (fields.jobTitle || fields.company) {
    await db.update(users).set({ jobTitle: fields.jobTitle || user.jobTitle, company: fields.company || user.company }).where(eq(users.id, user.id));
  }

  const [review] = await db
    .insert(reviews)
    .values({
      softwareId: s.id,
      userId: user.id,
      rating: ratings.rating!,
      ratingEase: ratings.ratingEase!,
      ratingSupport: ratings.ratingSupport!,
      ratingValue: ratings.ratingValue!,
      ratingFeatures: ratings.ratingFeatures!,
      title: fields.title.slice(0, 140),
      pros: fields.pros.slice(0, 4000),
      cons: fields.cons.slice(0, 4000),
      useCase: fields.useCase.slice(0, 2000),
      reviewerRole: fields.reviewerRole,
      firmSize: fields.firmSize,
      usageDuration: fields.usageDuration,
      recommend: recommend === "yes",
      showName: form.get("showName") === "on",
      verifiedEmail: true,
      verifiedLinkedin: !!user.linkedinVerifiedAt,
      proofKey,
      proofStatus: proofKey ? "PENDING" : "NONE",
    })
    .returning();
  await refreshReviewStatus(review.id);
  revalidatePath(`/software/${s.slug}`);
  redirect(`/account?submitted=${review.id}`);
}

export async function uploadProof(form: FormData) {
  const user = await requireUser("/account");
  const reviewId = String(form.get("reviewId"));
  const review = await db.query.reviews.findFirst({ where: and(eq(reviews.id, reviewId), eq(reviews.userId, user.id)) });
  if (!review) redirect("/account");
  const file = form.get("proof");
  if (!(file instanceof File) || file.size === 0) redirect("/account?proof=missing");
  let key: string;
  try {
    key = await storeProof(file);
  } catch {
    redirect("/account?proof=invalid");
  }
  await db.update(reviews).set({ proofKey: key, proofStatus: "PENDING", updatedAt: new Date() }).where(eq(reviews.id, review.id));
  await refreshReviewStatus(review.id);
  redirect("/account?proof=uploaded");
}

export async function updateProfile(form: FormData) {
  const user = await requireUser("/account");
  await db
    .update(users)
    .set({
      name: String(form.get("name") || "").trim().slice(0, 80) || null,
      jobTitle: String(form.get("jobTitle") || "").trim().slice(0, 80) || null,
      company: String(form.get("company") || "").trim().slice(0, 80) || null,
    })
    .where(eq(users.id, user.id));
  redirect("/account?saved=1");
}
