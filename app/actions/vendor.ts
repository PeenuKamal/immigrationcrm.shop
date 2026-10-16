"use server";
import { and, eq, inArray, ne, or } from "drizzle-orm";
import { redirect } from "next/navigation";
import { revalidatePath } from "next/cache";
import { db, software, softwareCategories, categories, listingClaims, reviews, payments } from "@/db";
import { requireUser } from "@/lib/session";
import { slugify, domainFromUrl, sameOrganisation, appUrl } from "@/lib/util";
import { stripe, featuredPrice, FEATURED_PLANS } from "@/lib/stripe";
import { pingSoftware } from "@/lib/reviews";

export type ListingState = { error?: string; fields?: Record<string, string> } | undefined;

const list = (v: FormDataEntryValue | null, sep: RegExp) =>
  String(v ?? "").split(sep).map((x) => x.trim()).filter(Boolean).slice(0, 20);

function readListing(form: FormData) {
  const fields: Record<string, string> = {};
  for (const k of ["name", "website", "vendorName", "tagline", "description", "deployment", "regions", "audiences", "features", "pricingNote", "logoUrl"])
    fields[k] = String(form.get(k) ?? "").trim();
  const cats = form.getAll("categories").map(String);
  return { fields, cats };
}

function validate(fields: Record<string, string>, cats: string[]) {
  if (fields.name.length < 2) return "Add the product name.";
  if (!domainFromUrl(fields.website)) return "Add the product's website, like https://yourproduct.com.";
  if (fields.tagline.length < 10 || fields.tagline.length > 120) return "Write a one-line summary between 10 and 120 characters.";
  if (fields.description.length < 80) return "Describe the product in at least 80 characters: who it's for and what it does.";
  if (!cats.length) return "Pick at least one category.";
  if (fields.logoUrl && !/^https:\/\//.test(fields.logoUrl)) return "The logo link must start with https://.";
  return null;
}

async function saveCategories(softwareId: string, slugs: string[]) {
  const rows = await db.select().from(categories).where(inArray(categories.slug, slugs));
  await db.delete(softwareCategories).where(eq(softwareCategories.softwareId, softwareId));
  if (rows.length) await db.insert(softwareCategories).values(rows.map((c) => ({ softwareId, categoryId: c.id })));
}

export async function submitListing(_: ListingState, form: FormData): Promise<ListingState> {
  const user = await requireUser("/list-your-software");
  if (!user.isWorkEmail) return { error: "Sign in with your company email to list software." };
  const { fields, cats } = readListing(form);
  const err = validate(fields, cats);
  if (err) return { error: err, fields };

  const website = fields.website.startsWith("http") ? fields.website : `https://${fields.website}`;
  const domain = domainFromUrl(website)!;
  let slug = slugify(fields.name);
  const dupe = await db.query.software.findFirst({ where: or(eq(software.slug, slug), eq(software.domain, domain)) });
  if (dupe && dupe.status !== "REJECTED") {
    if (dupe.domain === domain || dupe.slug === slug) {
      return { error: `${dupe.name} is already listed. Open its page and choose “Claim this listing” to manage it.`, fields };
    }
  }
  if (dupe) slug = `${slug}-${Date.now().toString(36)}`;

  const isVendorStaff = sameOrganisation(user.emailDomain, domain);
  const [row] = await db
    .insert(software)
    .values({
      slug,
      name: fields.name.slice(0, 80),
      website,
      domain,
      vendorName: fields.vendorName || null,
      tagline: fields.tagline,
      description: fields.description.slice(0, 4000),
      deployment: fields.deployment || null,
      regions: list(fields.regions, /,/),
      audiences: list(fields.audiences, /,/),
      features: list(fields.features, /\r?\n|,/),
      pricingNote: fields.pricingNote || null,
      logoUrl: fields.logoUrl || null,
      // Listings from someone whose work email matches the product's domain publish straight away.
      status: isVendorStaff ? "PUBLISHED" : "PENDING",
      claimed: isVendorStaff,
      ownerId: isVendorStaff ? user.id : null,
      submittedBy: user.email,
    })
    .returning();
  await saveCategories(row.id, cats);
  if (isVendorStaff) await pingSoftware(row.id);
  revalidatePath("/software");
  redirect(isVendorStaff ? `/vendor/${row.id}?created=1` : `/list-your-software?queued=${encodeURIComponent(row.name)}`);
}

async function ownedListing(id: string) {
  const user = await requireUser(`/vendor/${id}`);
  const s = await db.query.software.findFirst({ where: and(eq(software.id, id), eq(software.ownerId, user.id)) });
  if (!s) redirect("/vendor");
  return { user, s };
}

export async function updateListing(_: ListingState, form: FormData): Promise<ListingState> {
  const { s } = await ownedListing(String(form.get("id")));
  const { fields, cats } = readListing(form);
  const err = validate(fields, cats);
  if (err) return { error: err, fields };
  const website = fields.website.startsWith("http") ? fields.website : `https://${fields.website}`;
  await db
    .update(software)
    .set({
      name: fields.name.slice(0, 80),
      website,
      // the verified domain only changes through an admin, to stop owners dodging the self-review check
      domain: s.domain ?? domainFromUrl(website),
      vendorName: fields.vendorName || null,
      tagline: fields.tagline,
      description: fields.description.slice(0, 4000),
      deployment: fields.deployment || null,
      regions: list(fields.regions, /,/),
      audiences: list(fields.audiences, /,/),
      features: list(fields.features, /\r?\n|,/),
      pricingNote: fields.pricingNote || null,
      logoUrl: fields.logoUrl || null,
      updatedAt: new Date(),
    })
    .where(eq(software.id, s.id));
  await saveCategories(s.id, cats);
  if (s.status === "PUBLISHED") await pingSoftware(s.id);
  revalidatePath(`/software/${s.slug}`);
  redirect(`/vendor/${s.id}?saved=1`);
}

export async function claimListing(form: FormData) {
  const slug = String(form.get("slug"));
  const user = await requireUser(`/software/${slug}/claim`);
  const s = await db.query.software.findFirst({ where: eq(software.slug, slug) });
  if (!s || s.claimed) redirect(`/software/${slug}`);
  if (!user.isWorkEmail) redirect(`/software/${slug}/claim?error=personal`);

  if (s.domain && sameOrganisation(user.emailDomain, s.domain)) {
    await db.update(software).set({ claimed: true, ownerId: user.id }).where(eq(software.id, s.id));
    await db.update(listingClaims).set({ status: "REJECTED" }).where(and(eq(listingClaims.softwareId, s.id), ne(listingClaims.userId, user.id)));
    redirect(`/vendor/${s.id}?claimed=1`);
  }
  const existing = await db.query.listingClaims.findFirst({ where: and(eq(listingClaims.softwareId, s.id), eq(listingClaims.userId, user.id)) });
  if (!existing) {
    await db.insert(listingClaims).values({ softwareId: s.id, userId: user.id, note: String(form.get("note") || "").slice(0, 1000) });
  }
  redirect(`/software/${slug}/claim?sent=1`);
}

export async function respondToReview(form: FormData) {
  const reviewId = String(form.get("reviewId"));
  const review = await db.query.reviews.findFirst({ where: eq(reviews.id, reviewId) });
  if (!review) redirect("/vendor");
  const { s } = await ownedListing(review.softwareId);
  const text = String(form.get("response") || "").trim().slice(0, 1500);
  await db.update(reviews).set({ vendorResponse: text || null }).where(eq(reviews.id, review.id));
  revalidatePath(`/software/${s.slug}`);
  redirect(`/vendor/${s.id}?replied=1#reviews`);
}

export async function startFeaturedCheckout(form: FormData) {
  const { s, user } = await ownedListing(String(form.get("id")));
  const months = Number(form.get("months"));
  if (!FEATURED_PLANS.some((p) => p.months === months)) redirect(`/vendor/${s.id}`);
  const client = stripe();
  if (!client) redirect(`/vendor/${s.id}?payments=off`);

  const amount = featuredPrice(months);
  const currency = (process.env.FEATURED_CURRENCY || "usd").toLowerCase();
  const session = await client.checkout.sessions.create({
    mode: "payment",
    customer_email: user.email,
    line_items: [
      {
        quantity: 1,
        price_data: {
          currency,
          unit_amount: amount,
          product_data: { name: `Featured listing: ${s.name}`, description: `${months} month${months > 1 ? "s" : ""} of featured placement on ImmigrationCRM.shop` },
        },
      },
    ],
    metadata: { softwareId: s.id, months: String(months) },
    success_url: `${appUrl()}/vendor/featured?session_id={CHECKOUT_SESSION_ID}`,
    cancel_url: `${appUrl()}/vendor/${s.id}?payment=cancelled`,
  });
  await db.insert(payments).values({ softwareId: s.id, stripeSessionId: session.id, amountCents: amount, currency, months });
  redirect(session.url!);
}
