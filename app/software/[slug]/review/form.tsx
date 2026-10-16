"use client";
import { useActionState } from "react";
import { submitReview } from "@/app/actions/reviews";

function StarField({ name, label, value }: { name: string; label: string; value?: string }) {
  return (
    <div className="row" role="radiogroup" aria-label={label}>
      <span>{label}</span>
      <span className="star-input">
        {[5, 4, 3, 2, 1].map((n) => (
          <span key={n} style={{ display: "contents" }}>
            <input type="radio" id={`${name}-${n}`} name={name} value={n} defaultChecked={value === String(n)} />
            <label htmlFor={`${name}-${n}`} title={`${n} star${n > 1 ? "s" : ""}`}>★<span className="sr-only">{n} stars</span></label>
          </span>
        ))}
      </span>
    </div>
  );
}

export function ReviewForm(props: {
  softwareId: string; softwareName: string; hasLinkedin: boolean; jobTitle: string; company: string;
  ROLES: string[]; SIZES: string[]; DURATIONS: string[];
}) {
  const [state, action, pending] = useActionState(submitReview, undefined);
  const f = state?.fields ?? {};
  return (
    <form action={action} className="panel">
      <input type="hidden" name="softwareId" value={props.softwareId} />
      {state?.error && <div className="notice error" role="alert"><p>{state.error}</p></div>}

      <fieldset className="field">
        <legend>Your ratings</legend>
        <div className="rating-grid">
          <StarField name="rating" label="Overall" />
          <StarField name="ratingEase" label="Ease of use" />
          <StarField name="ratingSupport" label="Customer support" />
          <StarField name="ratingValue" label="Value for money" />
          <StarField name="ratingFeatures" label="Immigration features" />
        </div>
      </fieldset>

      <div className="field">
        <label htmlFor="title">Headline</label>
        <input id="title" name="title" maxLength={140} defaultValue={f.title} placeholder={`e.g. Saved our team hours on H-1B forms`} />
      </div>
      <div className="field">
        <label htmlFor="pros">What works well</label>
        <p className="hint">One point per line. Specific workflows, forms or features help most.</p>
        <textarea id="pros" name="pros" defaultValue={f.pros} />
      </div>
      <div className="field">
        <label htmlFor="cons">What doesn't work</label>
        <p className="hint">One point per line. Bugs, missing features, pricing, onboarding.</p>
        <textarea id="cons" name="cons" defaultValue={f.cons} />
      </div>
      <div className="field">
        <label htmlFor="useCase">How your firm uses {props.softwareName}</label>
        <p className="hint">Case types, visa programs, countries, team size using it.</p>
        <textarea id="useCase" name="useCase" style={{ minHeight: 90 }} defaultValue={f.useCase} />
      </div>

      <div className="grid-2">
        <div className="field">
          <label htmlFor="reviewerRole">Your role</label>
          <select id="reviewerRole" name="reviewerRole" defaultValue={f.reviewerRole || ""}>
            <option value="" disabled>Choose one</option>
            {props.ROLES.map((r) => <option key={r}>{r}</option>)}
          </select>
        </div>
        <div className="field">
          <label htmlFor="firmSize">Firm size</label>
          <select id="firmSize" name="firmSize" defaultValue={f.firmSize || ""}>
            <option value="" disabled>Choose one</option>
            {props.SIZES.map((r) => <option key={r}>{r}</option>)}
          </select>
        </div>
        <div className="field">
          <label htmlFor="usageDuration">How long you've used it</label>
          <select id="usageDuration" name="usageDuration" defaultValue={f.usageDuration || ""}>
            <option value="" disabled>Choose one</option>
            {props.DURATIONS.map((r) => <option key={r}>{r}</option>)}
          </select>
        </div>
        <fieldset className="field">
          <legend>Would you recommend it to a peer?</legend>
          <div className="checks">
            <label><input type="radio" name="recommend" value="yes" /> Yes</label>
            <label><input type="radio" name="recommend" value="no" /> No</label>
          </div>
        </fieldset>
        <div className="field">
          <label htmlFor="jobTitle">Job title <span className="muted">(optional)</span></label>
          <input id="jobTitle" name="jobTitle" defaultValue={f.jobTitle ?? props.jobTitle} />
        </div>
        <div className="field">
          <label htmlFor="company">Firm name <span className="muted">(optional)</span></label>
          <input id="company" name="company" defaultValue={f.company ?? props.company} />
        </div>
      </div>

      <div className="field">
        <label htmlFor="proof">Proof of use {props.hasLinkedin ? <span className="muted">(optional)</span> : <span className="muted">(needed unless you connect LinkedIn)</span>}</label>
        <p className="hint">A screenshot of your dashboard with client data hidden, or an invoice. PNG, JPG, WebP or PDF up to 5 MB. Only our moderators see it.</p>
        <input id="proof" name="proof" type="file" accept="image/png,image/jpeg,image/webp,application/pdf" />
      </div>

      <div className="field checks" style={{ display: "grid", gap: 8 }}>
        <label><input type="checkbox" name="showName" /> Show my name and firm on the review</label>
        <label><input type="checkbox" name="consent" /> This is my own honest experience, and I'm not paid or employed by the vendor or a competitor.</label>
      </div>

      <button className="btn stamp" disabled={pending}>{pending ? "Submitting…" : "Submit review"}</button>
    </form>
  );
}
