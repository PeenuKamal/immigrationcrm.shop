"use client";
import { useActionState } from "react";
import type { ListingState } from "@/app/actions/vendor";

type Cat = { slug: string; name: string };
type Init = Partial<Record<"id" | "name" | "website" | "vendorName" | "tagline" | "description" | "deployment" | "regions" | "audiences" | "features" | "pricingNote" | "logoUrl", string>> & { categories?: string[] };

export function ListingForm({ action, cats, initial = {}, cta }: { action: (s: ListingState, f: FormData) => Promise<ListingState>; cats: Cat[]; initial?: Init; cta: string }) {
  const [state, formAction, pending] = useActionState(action, undefined);
  const v = { ...initial, ...(state?.fields ?? {}) } as Record<string, string | undefined>;
  return (
    <form action={formAction} className="panel">
      {initial.id && <input type="hidden" name="id" value={initial.id} />}
      {state?.error && <div className="notice error" role="alert"><p>{state.error}</p></div>}
      <div className="grid-2">
        <div className="field"><label htmlFor="name">Product name</label><input id="name" name="name" defaultValue={v.name} maxLength={80} /></div>
        <div className="field"><label htmlFor="website">Website</label><input id="website" name="website" type="url" defaultValue={v.website} placeholder="https://" /></div>
        <div className="field"><label htmlFor="vendorName">Company <span className="muted">(if different)</span></label><input id="vendorName" name="vendorName" defaultValue={v.vendorName} /></div>
        <div className="field">
          <label htmlFor="deployment">Deployment</label>
          <select id="deployment" name="deployment" defaultValue={v.deployment ?? "Cloud"}>
            <option>Cloud</option><option>On-premise</option><option>Hybrid</option>
          </select>
        </div>
      </div>
      <div className="field">
        <label htmlFor="tagline">One-line summary</label>
        <input id="tagline" name="tagline" defaultValue={v.tagline} maxLength={120} />
        <p className="hint">Up to 120 characters. Say what it does, not how great it is.</p>
      </div>
      <div className="field">
        <label htmlFor="description">Description</label>
        <textarea id="description" name="description" defaultValue={v.description} />
      </div>
      <fieldset className="field">
        <legend>Categories</legend>
        <div className="checks">
          {cats.map((c) => (
            <label key={c.slug}><input type="checkbox" name="categories" value={c.slug} defaultChecked={initial.categories?.includes(c.slug)} /> {c.name}</label>
          ))}
        </div>
      </fieldset>
      <div className="grid-2">
        <div className="field"><label htmlFor="regions">Markets served</label><input id="regions" name="regions" defaultValue={v.regions} placeholder="Canada, USA, Australia" /><p className="hint">Separate with commas.</p></div>
        <div className="field"><label htmlFor="audiences">Built for</label><input id="audiences" name="audiences" defaultValue={v.audiences} placeholder="RCICs, Immigration law firms" /><p className="hint">Separate with commas.</p></div>
      </div>
      <div className="field"><label htmlFor="features">Key features</label><textarea id="features" name="features" defaultValue={v.features} style={{ minHeight: 100 }} /><p className="hint">One per line, up to 20.</p></div>
      <div className="grid-2">
        <div className="field"><label htmlFor="pricingNote">Pricing <span className="muted">(optional)</span></label><input id="pricingNote" name="pricingNote" defaultValue={v.pricingNote} placeholder="From $49 per user per month" /></div>
        <div className="field"><label htmlFor="logoUrl">Logo image link <span className="muted">(optional)</span></label><input id="logoUrl" name="logoUrl" type="url" defaultValue={v.logoUrl} placeholder="https://…/logo.png" /></div>
      </div>
      <button className="btn" disabled={pending}>{pending ? "Saving…" : cta}</button>
    </form>
  );
}
