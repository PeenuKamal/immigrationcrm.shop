import {
  pgTable,
  pgEnum,
  text,
  integer,
  boolean,
  timestamp,
  real,
  primaryKey,
  uniqueIndex,
  index,
  customType,
} from "drizzle-orm/pg-core";
import { relations } from "drizzle-orm";
import { randomUUID } from "crypto";

const id = () => text("id").primaryKey().$defaultFn(() => randomUUID());
const created = () => timestamp("created_at", { withTimezone: true }).notNull().defaultNow();

export const roleEnum = pgEnum("role", ["USER", "ADMIN"]);
export const listingStatusEnum = pgEnum("listing_status", ["PENDING", "PUBLISHED", "REJECTED"]);
export const reviewStatusEnum = pgEnum("review_status", [
  "AWAITING_VERIFICATION", // reviewer still needs LinkedIn or proof of use
  "PENDING_PROOF", // proof uploaded, waiting for an admin check
  "PENDING_ADMIN", // fully verified, waiting for manual approval (only when REQUIRE_ADMIN_APPROVAL=true)
  "PUBLISHED",
  "REJECTED",
]);
export const proofStatusEnum = pgEnum("proof_status", ["NONE", "PENDING", "APPROVED", "REJECTED"]);

export const users = pgTable("users", {
  id: id(),
  email: text("email").notNull().unique(),
  emailDomain: text("email_domain").notNull(),
  emailVerifiedAt: timestamp("email_verified_at", { withTimezone: true }),
  isWorkEmail: boolean("is_work_email").notNull().default(false),
  name: text("name"),
  jobTitle: text("job_title"),
  company: text("company"),
  linkedinSub: text("linkedin_sub").unique(),
  linkedinName: text("linkedin_name"),
  linkedinEmail: text("linkedin_email"),
  linkedinPicture: text("linkedin_picture"),
  linkedinVerifiedAt: timestamp("linkedin_verified_at", { withTimezone: true }),
  role: roleEnum("role").notNull().default("USER"),
  createdAt: created(),
});

export const otpCodes = pgTable(
  "otp_codes",
  {
    id: id(),
    email: text("email").notNull(),
    codeHash: text("code_hash").notNull(),
    expiresAt: timestamp("expires_at", { withTimezone: true }).notNull(),
    attempts: integer("attempts").notNull().default(0),
    consumed: boolean("consumed").notNull().default(false),
    createdAt: created(),
  },
  (t) => [index("otp_email_idx").on(t.email, t.createdAt)]
);

export const categories = pgTable("categories", {
  id: id(),
  slug: text("slug").notNull().unique(),
  name: text("name").notNull(),
  description: text("description").notNull(),
  sortOrder: integer("sort_order").notNull().default(0),
});

export const software = pgTable(
  "software",
  {
    id: id(),
    slug: text("slug").notNull().unique(),
    name: text("name").notNull(),
    tagline: text("tagline").notNull(),
    description: text("description").notNull(),
    website: text("website"),
    domain: text("domain"),
    vendorName: text("vendor_name"),
    logoUrl: text("logo_url"),
    pricingNote: text("pricing_note"),
    deployment: text("deployment"),
    regions: text("regions").array().notNull().default([]),
    audiences: text("audiences").array().notNull().default([]),
    features: text("features").array().notNull().default([]),
    status: listingStatusEnum("status").notNull().default("PENDING"),
    claimed: boolean("claimed").notNull().default(false),
    ownerId: text("owner_id").references(() => users.id, { onDelete: "set null" }),
    featuredUntil: timestamp("featured_until", { withTimezone: true }),
    ratingAvg: real("rating_avg").notNull().default(0),
    reviewCount: integer("review_count").notNull().default(0),
    submittedBy: text("submitted_by"),
    createdAt: created(),
    updatedAt: timestamp("updated_at", { withTimezone: true }).notNull().defaultNow(),
  },
  (t) => [index("software_status_idx").on(t.status, t.featuredUntil)]
);

export const softwareCategories = pgTable(
  "software_categories",
  {
    softwareId: text("software_id").notNull().references(() => software.id, { onDelete: "cascade" }),
    categoryId: text("category_id").notNull().references(() => categories.id, { onDelete: "cascade" }),
  },
  (t) => [primaryKey({ columns: [t.softwareId, t.categoryId] })]
);

export const listingClaims = pgTable("listing_claims", {
  id: id(),
  softwareId: text("software_id").notNull().references(() => software.id, { onDelete: "cascade" }),
  userId: text("user_id").notNull().references(() => users.id, { onDelete: "cascade" }),
  note: text("note"),
  status: listingStatusEnum("status").notNull().default("PENDING"),
  createdAt: created(),
});

export const reviews = pgTable(
  "reviews",
  {
    id: id(),
    softwareId: text("software_id").notNull().references(() => software.id, { onDelete: "cascade" }),
    userId: text("user_id").notNull().references(() => users.id, { onDelete: "cascade" }),
    rating: integer("rating").notNull(),
    ratingEase: integer("rating_ease").notNull(),
    ratingSupport: integer("rating_support").notNull(),
    ratingValue: integer("rating_value").notNull(),
    ratingFeatures: integer("rating_features").notNull(),
    title: text("title").notNull(),
    pros: text("pros").notNull(),
    cons: text("cons").notNull(),
    useCase: text("use_case").notNull(),
    reviewerRole: text("reviewer_role").notNull(),
    firmSize: text("firm_size").notNull(),
    usageDuration: text("usage_duration").notNull(),
    recommend: boolean("recommend").notNull(),
    showName: boolean("show_name").notNull().default(false),
    verifiedEmail: boolean("verified_email").notNull().default(false),
    verifiedLinkedin: boolean("verified_linkedin").notNull().default(false),
    proofKey: text("proof_key"),
    proofStatus: proofStatusEnum("proof_status").notNull().default("NONE"),
    status: reviewStatusEnum("status").notNull().default("AWAITING_VERIFICATION"),
    rejectReason: text("reject_reason"),
    vendorResponse: text("vendor_response"),
    publishedAt: timestamp("published_at", { withTimezone: true }),
    createdAt: created(),
    updatedAt: timestamp("updated_at", { withTimezone: true }).notNull().defaultNow(),
  },
  (t) => [
    uniqueIndex("review_one_per_user").on(t.softwareId, t.userId),
    index("review_status_idx").on(t.status, t.publishedAt),
  ]
);

const bytea = customType<{ data: Buffer; driverData: Buffer }>({ dataType: () => "bytea" });

// Proof-of-use files live in the database so they survive redeploys on any host. Only admins can read them.
export const proofFiles = pgTable("proof_files", {
  id: id(),
  mime: text("mime").notNull(),
  size: integer("size").notNull(),
  data: bytea("data").notNull(),
  createdAt: created(),
});

export const payments = pgTable("payments", {
  id: id(),
  softwareId: text("software_id").notNull().references(() => software.id, { onDelete: "cascade" }),
  stripeSessionId: text("stripe_session_id").notNull().unique(),
  amountCents: integer("amount_cents").notNull(),
  currency: text("currency").notNull(),
  months: integer("months").notNull(),
  status: text("status").notNull().default("pending"),
  createdAt: created(),
});

export const softwareRelations = relations(software, ({ many, one }) => ({
  categories: many(softwareCategories),
  reviews: many(reviews),
  owner: one(users, { fields: [software.ownerId], references: [users.id] }),
}));
export const categoryRelations = relations(categories, ({ many }) => ({
  software: many(softwareCategories),
}));
export const softwareCategoryRelations = relations(softwareCategories, ({ one }) => ({
  software: one(software, { fields: [softwareCategories.softwareId], references: [software.id] }),
  category: one(categories, { fields: [softwareCategories.categoryId], references: [categories.id] }),
}));
export const reviewRelations = relations(reviews, ({ one }) => ({
  software: one(software, { fields: [reviews.softwareId], references: [software.id] }),
  user: one(users, { fields: [reviews.userId], references: [users.id] }),
}));
export const userRelations = relations(users, ({ many }) => ({
  reviews: many(reviews),
  listings: many(software),
}));

export type User = typeof users.$inferSelect;
export type Software = typeof software.$inferSelect;
export type Review = typeof reviews.$inferSelect;
export type Category = typeof categories.$inferSelect;
