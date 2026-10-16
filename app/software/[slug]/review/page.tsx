import Link from "next/link";
import { notFound } from "next/navigation";
import { and, eq } from "drizzle-orm";
import { db, software, reviews } from "@/db";
import { requireUser } from "@/lib/session";
import { sameOrganisation } from "@/lib/util";
import { ReviewForm } from "./form";
import { reviewOptions } from "@/app/actions/reviews";

export const metadata = { robots: { index: false } };

export default async function WriteReview({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const s = await db.query.software.findFirst({ where: and(eq(software.slug, slug), eq(software.status, "PUBLISHED")) });
  if (!s) notFound();
  const user = await requireUser(`/software/${slug}/review`);
  const existing = await db.query.reviews.findFirst({ where: and(eq(reviews.softwareId, s.id), eq(reviews.userId, user.id)) });
  const opts = await reviewOptions();

  let blocker: React.ReactNode = null;
  if (!user.isWorkEmail) blocker = <p>You're signed in with a personal address. <Link href={`/signin?next=/software/${slug}/review`}>Sign in with your work email</Link> to leave a review.</p>;
  else if (sameOrganisation(user.emailDomain, s.domain)) blocker = <p>Your email is on {s.name}'s own domain, so you can't review it. You can <Link href={`/software/${slug}/claim`}>manage the listing</Link> instead.</p>;
  else if (existing) blocker = <p>You've already reviewed {s.name}. <Link href="/account">See its status on your account</Link>.</p>;

  return (
    <div className="wrap" style={{ maxWidth: 760 }}>
      <p className="small"><Link href={`/software/${slug}`}>Back to {s.name}</Link></p>
      <div className="page-head">
        <h1>Review {s.name}</h1>
        <p>Tell other immigration practitioners what it's really like to use. Your name stays hidden unless you choose to show it.</p>
      </div>
      {blocker ? (
        <div className="notice warn">{blocker}</div>
      ) : (
        <>
          <div className={`notice${user.linkedinVerifiedAt ? "" : " warn"}`}>
            {user.linkedinVerifiedAt ? (
              <p>Your work email and LinkedIn are verified, so this review goes live as soon as you submit it.</p>
            ) : (
              <p>
                Your work email ({user.email}) is verified. To publish, we need one more proof:{" "}
                <a href={`/api/auth/linkedin/start?next=/software/${slug}/review`}>connect LinkedIn</a> (instant), or attach proof of use below (checked within one
                business day).
              </p>
            )}
          </div>
          <ReviewForm softwareId={s.id} softwareName={s.name} hasLinkedin={!!user.linkedinVerifiedAt} jobTitle={user.jobTitle ?? ""} company={user.company ?? ""} {...opts} />
        </>
      )}
    </div>
  );
}
