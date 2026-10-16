export function slugify(input: string) {
  return input
    .toLowerCase()
    .normalize("NFKD")
    .replace(/[̀-ͯ]/g, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .slice(0, 60);
}

export function emailDomain(email: string) {
  return email.split("@")[1]?.toLowerCase().trim() ?? "";
}

/** "https://www.app.example.com/path" -> "example.com" style root match helper */
export function domainFromUrl(url: string | null | undefined) {
  if (!url) return null;
  try {
    const u = new URL(url.startsWith("http") ? url : `https://${url}`);
    return u.hostname.replace(/^www\./, "").toLowerCase();
  } catch {
    return null;
  }
}

/** true when a and b are the same domain or one is a subdomain of the other */
export function sameOrganisation(a: string | null | undefined, b: string | null | undefined) {
  if (!a || !b) return false;
  a = a.toLowerCase();
  b = b.toLowerCase();
  return a === b || a.endsWith(`.${b}`) || b.endsWith(`.${a}`);
}

export function isFeatured(featuredUntil: Date | null | undefined) {
  return !!featuredUntil && featuredUntil.getTime() > Date.now();
}

export function formatDate(d: Date | string | null | undefined) {
  if (!d) return "";
  return new Date(d).toLocaleDateString("en-GB", { day: "numeric", month: "short", year: "numeric" });
}

export function lines(text: string) {
  return text
    .split(/\r?\n/)
    .map((l) => l.replace(/^[-•*]\s*/, "").trim())
    .filter(Boolean);
}

export function appUrl() {
  return (process.env.APP_URL || "http://localhost:3000").replace(/\/$/, "");
}
