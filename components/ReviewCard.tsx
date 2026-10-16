import type { Review, User } from "@/db";
import { Stars } from "./Stars";
import { Stamp } from "./Stamp";
import { formatDate, lines } from "@/lib/util";

export function ReviewCard({ r, author, softwareName }: { r: Review; author: Pick<User, "name" | "company" | "linkedinName">; softwareName?: string }) {
  const checks = [
    r.verifiedEmail && "Work email",
    r.verifiedLinkedin && "LinkedIn profile",
    r.proofStatus === "APPROVED" && "Proof of use",
  ].filter(Boolean) as string[];
  const displayName = r.showName ? author.linkedinName || author.name : null;
  return (
    <article className="review" id={`review-${r.id}`}>
      <div className="review-body">
        <div className="rating" style={{ marginBottom: 8 }}>
          <Stars value={r.rating} /> {r.rating}.0
          {softwareName && <span className="muted" style={{ fontWeight: 400 }}>· {softwareName}</span>}
        </div>
        <h3>{r.title}</h3>
        <p className="reviewer">
          <strong>{displayName ?? `Verified reviewer · ${r.reviewerRole}`}</strong>
          {r.showName && author.company ? `, ${author.company}` : ""} · {r.firmSize} · used for {r.usageDuration.toLowerCase()} ·{" "}
          {formatDate(r.publishedAt ?? r.createdAt)}
        </p>
        <div className="pc">
          <div>
            <h4>What works</h4>
            <ul>{lines(r.pros).map((l, i) => <li key={i}>{l}</li>)}</ul>
          </div>
          <div className="cons">
            <h4>What doesn't</h4>
            <ul>{lines(r.cons).map((l, i) => <li key={i}>{l}</li>)}</ul>
          </div>
        </div>
        <p className="use-case">
          <strong>How they use it:</strong> {r.useCase}
        </p>
        <p className="small muted" style={{ marginBottom: 0 }}>
          {r.recommend ? "Would recommend to a peer." : "Would not recommend to a peer."} Ease of use {r.ratingEase}/5 · Support{" "}
          {r.ratingSupport}/5 · Value {r.ratingValue}/5 · Features {r.ratingFeatures}/5
        </p>
        {r.vendorResponse && (
          <div className="vendor-reply" style={{ marginTop: 16 }}>
            <strong>Response from the vendor</strong>
            <p style={{ margin: "4px 0 0" }}>{r.vendorResponse}</p>
          </div>
        )}
      </div>
      <div>
        <Stamp className="review-stamp" center="VERIFIED" sub={`${checks.length} CHECKS`} title={`Verified by ${checks.join(", ")}`} />
        <ul className="stamp-legend">{checks.map((c) => <li key={c}>{c}</li>)}</ul>
      </div>
    </article>
  );
}
