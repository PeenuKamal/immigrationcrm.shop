import type { Metadata } from "next";
import { permanentRedirect } from "next/navigation";
import { getDirectory, DirectoryBody, type DirSP } from "@/components/Directory";
import { JsonLd, graph, breadcrumbLd, Breadcrumbs, itemListLd, meta, year } from "@/lib/seo";

export const dynamic = "force-dynamic";
type SP = Promise<DirSP & { category?: string }>;

export async function generateMetadata({ searchParams }: { searchParams: SP }): Promise<Metadata> {
  const sp = await searchParams;
  return meta({
    title: sp.q ? `“${sp.q}” — immigration software search` : `All immigration software: verified reviews (${year()})`,
    description: "Every immigration CRM, case management, law practice management and AI tool, with ratings only from verified practitioners. Filter by market and compare side by side.",
    path: "/software",
    noindex: !!(sp.q || sp.region || sp.sort),
  });
}

export default async function Software({ searchParams }: { searchParams: SP }) {
  const sp = await searchParams;
  if (sp.category) {
    const p = new URLSearchParams();
    for (const k of ["q", "region", "sort"] as const) if (sp[k]) p.set(k, sp[k]!);
    permanentRedirect(`/category/${sp.category}${p.size ? `?${p}` : ""}`);
  }
  const data = await getDirectory(sp);
  const crumbs = [{ name: "Home", path: "/" }, { name: "All software", path: "/software" }];
  return (
    <div className="wrap">
      <JsonLd data={graph(breadcrumbLd(crumbs), itemListLd("Immigration software directory", data.results.map((s) => ({ name: s.name, path: `/software/${s.slug}` }))))} />
      <Breadcrumbs crumbs={crumbs} />
      <div className="page-head">
        <h1>{sp.q ? `Results for “${sp.q}”` : "All immigration software"}</h1>
        <p>Every tool used to run an immigration practice, with reviews only from verified practitioners.</p>
        <form className="search" action="/software" role="search" style={{ marginTop: 16 }}>
          <label htmlFor="q" className="sr-only">Search software</label>
          <input id="q" name="q" type="search" defaultValue={sp.q} placeholder="Search by name or feature" />
          <button className="btn" type="submit">Search</button>
        </form>
      </div>
      <DirectoryBody data={data} sp={sp} />
    </div>
  );
}
