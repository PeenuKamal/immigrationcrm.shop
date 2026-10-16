import { appUrl } from "./util";

export const SITE = {
  name: "ImmigrationCRM.shop",
  shortName: "ImmigrationCRM",
  tagline: "Verified reviews of immigration software",
  description:
    "Independent directory and verified-review platform for immigration CRMs, immigration case management, law practice management and AI tools for immigration lawyers, RCICs and migration agents.",
  contactEmail: process.env.CONTACT_EMAIL || "kamal@thedigisparrow.com",
  year: new Date().getFullYear(),
};

export const url = (path = "/") => `${appUrl()}${path.startsWith("/") ? path : `/${path}`}`;

/** Search-friendly names for category pages (what people actually type into Google and AI assistants). */
export const CATEGORY_SEO: Record<string, string> = {
  "immigration-case-management": "Immigration Case Management Software",
  "immigration-crm": "Immigration CRM Software",
  "law-practice-management": "Law Practice Management Software for Immigration Firms",
  "ai-immigration": "AI Tools for Immigration Lawyers",
  "corporate-immigration": "Corporate Immigration Software",
  "general-crm": "CRMs for Immigration Consultants",
};
export const categorySeoName = (slug: string, fallback: string) => CATEGORY_SEO[slug] ?? `${fallback} software`;
/** "Immigration CRM Software" -> "immigration CRM software" (keeps acronyms like CRM and AI). */
export const categorySeoLower = (slug: string, fallback: string) => categorySeoName(slug, fallback).replace(/\b([A-Z][a-z]+)\b/g, (w) => w.toLowerCase());
