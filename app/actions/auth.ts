"use server";
import { createHash, randomInt } from "crypto";
import { and, desc, eq, gt } from "drizzle-orm";
import { redirect } from "next/navigation";
import { db, otpCodes, users } from "@/db";
import { sendEmail } from "@/lib/email";
import { isFreeEmailDomain } from "@/lib/free-email-domains";
import { createSession, destroySession } from "@/lib/session";
import { emailDomain } from "@/lib/util";
import { refreshUserReviews } from "@/lib/reviews";

const hash = (email: string, code: string) =>
  createHash("sha256").update(`${email}:${code}:${process.env.SESSION_SECRET ?? ""}`).digest("hex");

const isAdminEmail = (email: string) =>
  (process.env.ADMIN_EMAILS || "").split(",").map((e) => e.trim().toLowerCase()).includes(email);

const safeNext = (next: string | null | undefined) => (next && next.startsWith("/") && !next.startsWith("//") ? next : "/account");

export type AuthState = { error?: string } | undefined;

export async function requestCode(_: AuthState, form: FormData): Promise<AuthState> {
  const email = String(form.get("email") || "").trim().toLowerCase();
  const next = safeNext(String(form.get("next") || ""));
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return { error: "Enter an email address like name@yourfirm.com." };
  const domain = emailDomain(email);
  if (isFreeEmailDomain(domain) && !isAdminEmail(email)) {
    return {
      error: `Use the email address you have at work. Personal addresses like @${domain} can't show where you work, so we can't verify reviews from them.`,
    };
  }

  // Rate limit: one code per minute, five per hour.
  const hourAgo = new Date(Date.now() - 60 * 60 * 1000);
  const recent = await db.select().from(otpCodes).where(and(eq(otpCodes.email, email), gt(otpCodes.createdAt, hourAgo))).orderBy(desc(otpCodes.createdAt));
  if (recent[0] && Date.now() - recent[0].createdAt.getTime() < 60_000) {
    redirect(`/signin?step=code&email=${encodeURIComponent(email)}&next=${encodeURIComponent(next)}&resent=wait`);
  }
  if (recent.length >= 5) return { error: "Too many codes requested. Wait an hour, then try again." };

  const code = String(randomInt(0, 1_000_000)).padStart(6, "0");
  await db.insert(otpCodes).values({ email, codeHash: hash(email, code), expiresAt: new Date(Date.now() + 10 * 60 * 1000) });
  try {
    await sendEmail(
      email,
      `${code} is your ImmigrationCRM.shop code`,
      `Your sign-in code is ${code}\n\nIt expires in 10 minutes. If you didn't request it, you can ignore this email.\n\n— ImmigrationCRM.shop`
    );
  } catch {
    return { error: "We couldn't send the code just now. Check the address and try again in a minute." };
  }
  redirect(`/signin?step=code&email=${encodeURIComponent(email)}&next=${encodeURIComponent(next)}`);
}

export async function verifyCode(_: AuthState, form: FormData): Promise<AuthState> {
  const email = String(form.get("email") || "").trim().toLowerCase();
  const code = String(form.get("code") || "").replace(/\D/g, "");
  const next = safeNext(String(form.get("next") || ""));
  if (code.length !== 6) return { error: "Enter the 6-digit code from the email." };

  const [otp] = await db
    .select()
    .from(otpCodes)
    .where(and(eq(otpCodes.email, email), eq(otpCodes.consumed, false), gt(otpCodes.expiresAt, new Date())))
    .orderBy(desc(otpCodes.createdAt))
    .limit(1);
  if (!otp) return { error: "That code has expired. Request a new one below." };
  if (otp.attempts >= 5) return { error: "Too many wrong attempts. Request a new code below." };
  if (otp.codeHash !== hash(email, code)) {
    await db.update(otpCodes).set({ attempts: otp.attempts + 1 }).where(eq(otpCodes.id, otp.id));
    return { error: "That code doesn't match. Check the latest email and try again." };
  }
  await db.update(otpCodes).set({ consumed: true }).where(eq(otpCodes.id, otp.id));

  const domain = emailDomain(email);
  const isWork = !isFreeEmailDomain(domain);
  let user = await db.query.users.findFirst({ where: eq(users.email, email) });
  if (!user) {
    [user] = await db
      .insert(users)
      .values({ email, emailDomain: domain, emailVerifiedAt: new Date(), isWorkEmail: isWork, role: isAdminEmail(email) ? "ADMIN" : "USER" })
      .returning();
  } else {
    [user] = await db.update(users).set({ emailVerifiedAt: new Date(), isWorkEmail: isWork }).where(eq(users.id, user.id)).returning();
  }
  await refreshUserReviews(user);
  await createSession(user.id);
  redirect(next);
}

export async function signOut() {
  await destroySession();
  redirect("/");
}
