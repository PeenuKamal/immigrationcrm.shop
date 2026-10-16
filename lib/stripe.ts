import "server-only";
import Stripe from "stripe";

export function stripe() {
  const key = process.env.STRIPE_SECRET_KEY;
  if (!key) return null;
  return new Stripe(key);
}

export const FEATURED_PLANS = [
  { months: 1, label: "1 month" },
  { months: 3, label: "3 months" },
  { months: 12, label: "12 months" },
] as const;

export function featuredPrice(months: number) {
  const monthly = Number(process.env.FEATURED_PRICE_CENTS || 9900);
  const discount = months >= 12 ? 0.8 : months >= 3 ? 0.9 : 1;
  return Math.round(monthly * months * discount);
}

export function formatMoney(cents: number) {
  const currency = (process.env.FEATURED_CURRENCY || "usd").toUpperCase();
  return new Intl.NumberFormat("en-US", { style: "currency", currency }).format(cents / 100);
}
