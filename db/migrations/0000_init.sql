CREATE TYPE "public"."listing_status" AS ENUM('PENDING', 'PUBLISHED', 'REJECTED');--> statement-breakpoint
CREATE TYPE "public"."proof_status" AS ENUM('NONE', 'PENDING', 'APPROVED', 'REJECTED');--> statement-breakpoint
CREATE TYPE "public"."review_status" AS ENUM('AWAITING_VERIFICATION', 'PENDING_PROOF', 'PENDING_ADMIN', 'PUBLISHED', 'REJECTED');--> statement-breakpoint
CREATE TYPE "public"."role" AS ENUM('USER', 'ADMIN');--> statement-breakpoint
CREATE TABLE "categories" (
	"id" text PRIMARY KEY NOT NULL,
	"slug" text NOT NULL,
	"name" text NOT NULL,
	"description" text NOT NULL,
	"sort_order" integer DEFAULT 0 NOT NULL,
	CONSTRAINT "categories_slug_unique" UNIQUE("slug")
);
--> statement-breakpoint
CREATE TABLE "listing_claims" (
	"id" text PRIMARY KEY NOT NULL,
	"software_id" text NOT NULL,
	"user_id" text NOT NULL,
	"note" text,
	"status" "listing_status" DEFAULT 'PENDING' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "otp_codes" (
	"id" text PRIMARY KEY NOT NULL,
	"email" text NOT NULL,
	"code_hash" text NOT NULL,
	"expires_at" timestamp with time zone NOT NULL,
	"attempts" integer DEFAULT 0 NOT NULL,
	"consumed" boolean DEFAULT false NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "payments" (
	"id" text PRIMARY KEY NOT NULL,
	"software_id" text NOT NULL,
	"stripe_session_id" text NOT NULL,
	"amount_cents" integer NOT NULL,
	"currency" text NOT NULL,
	"months" integer NOT NULL,
	"status" text DEFAULT 'pending' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "payments_stripe_session_id_unique" UNIQUE("stripe_session_id")
);
--> statement-breakpoint
CREATE TABLE "proof_files" (
	"id" text PRIMARY KEY NOT NULL,
	"mime" text NOT NULL,
	"size" integer NOT NULL,
	"data" "bytea" NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "reviews" (
	"id" text PRIMARY KEY NOT NULL,
	"software_id" text NOT NULL,
	"user_id" text NOT NULL,
	"rating" integer NOT NULL,
	"rating_ease" integer NOT NULL,
	"rating_support" integer NOT NULL,
	"rating_value" integer NOT NULL,
	"rating_features" integer NOT NULL,
	"title" text NOT NULL,
	"pros" text NOT NULL,
	"cons" text NOT NULL,
	"use_case" text NOT NULL,
	"reviewer_role" text NOT NULL,
	"firm_size" text NOT NULL,
	"usage_duration" text NOT NULL,
	"recommend" boolean NOT NULL,
	"show_name" boolean DEFAULT false NOT NULL,
	"verified_email" boolean DEFAULT false NOT NULL,
	"verified_linkedin" boolean DEFAULT false NOT NULL,
	"proof_key" text,
	"proof_status" "proof_status" DEFAULT 'NONE' NOT NULL,
	"status" "review_status" DEFAULT 'AWAITING_VERIFICATION' NOT NULL,
	"reject_reason" text,
	"vendor_response" text,
	"published_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "software" (
	"id" text PRIMARY KEY NOT NULL,
	"slug" text NOT NULL,
	"name" text NOT NULL,
	"tagline" text NOT NULL,
	"description" text NOT NULL,
	"website" text,
	"domain" text,
	"vendor_name" text,
	"logo_url" text,
	"pricing_note" text,
	"deployment" text,
	"regions" text[] DEFAULT '{}' NOT NULL,
	"audiences" text[] DEFAULT '{}' NOT NULL,
	"features" text[] DEFAULT '{}' NOT NULL,
	"status" "listing_status" DEFAULT 'PENDING' NOT NULL,
	"claimed" boolean DEFAULT false NOT NULL,
	"owner_id" text,
	"featured_until" timestamp with time zone,
	"rating_avg" real DEFAULT 0 NOT NULL,
	"review_count" integer DEFAULT 0 NOT NULL,
	"submitted_by" text,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "software_slug_unique" UNIQUE("slug")
);
--> statement-breakpoint
CREATE TABLE "software_categories" (
	"software_id" text NOT NULL,
	"category_id" text NOT NULL,
	CONSTRAINT "software_categories_software_id_category_id_pk" PRIMARY KEY("software_id","category_id")
);
--> statement-breakpoint
CREATE TABLE "users" (
	"id" text PRIMARY KEY NOT NULL,
	"email" text NOT NULL,
	"email_domain" text NOT NULL,
	"email_verified_at" timestamp with time zone,
	"is_work_email" boolean DEFAULT false NOT NULL,
	"name" text,
	"job_title" text,
	"company" text,
	"linkedin_sub" text,
	"linkedin_name" text,
	"linkedin_email" text,
	"linkedin_picture" text,
	"linkedin_verified_at" timestamp with time zone,
	"role" "role" DEFAULT 'USER' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "users_email_unique" UNIQUE("email"),
	CONSTRAINT "users_linkedin_sub_unique" UNIQUE("linkedin_sub")
);
--> statement-breakpoint
ALTER TABLE "listing_claims" ADD CONSTRAINT "listing_claims_software_id_software_id_fk" FOREIGN KEY ("software_id") REFERENCES "public"."software"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "listing_claims" ADD CONSTRAINT "listing_claims_user_id_users_id_fk" FOREIGN KEY ("user_id") REFERENCES "public"."users"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "payments" ADD CONSTRAINT "payments_software_id_software_id_fk" FOREIGN KEY ("software_id") REFERENCES "public"."software"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "reviews" ADD CONSTRAINT "reviews_software_id_software_id_fk" FOREIGN KEY ("software_id") REFERENCES "public"."software"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "reviews" ADD CONSTRAINT "reviews_user_id_users_id_fk" FOREIGN KEY ("user_id") REFERENCES "public"."users"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "software" ADD CONSTRAINT "software_owner_id_users_id_fk" FOREIGN KEY ("owner_id") REFERENCES "public"."users"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "software_categories" ADD CONSTRAINT "software_categories_software_id_software_id_fk" FOREIGN KEY ("software_id") REFERENCES "public"."software"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "software_categories" ADD CONSTRAINT "software_categories_category_id_categories_id_fk" FOREIGN KEY ("category_id") REFERENCES "public"."categories"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
CREATE INDEX "otp_email_idx" ON "otp_codes" USING btree ("email","created_at");--> statement-breakpoint
CREATE UNIQUE INDEX "review_one_per_user" ON "reviews" USING btree ("software_id","user_id");--> statement-breakpoint
CREATE INDEX "review_status_idx" ON "reviews" USING btree ("status","published_at");--> statement-breakpoint
CREATE INDEX "software_status_idx" ON "software" USING btree ("status","featured_until");