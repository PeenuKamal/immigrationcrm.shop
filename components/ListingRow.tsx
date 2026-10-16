import Link from "next/link";
import type { Software } from "@/db";
import { LogoBox } from "./Logo";
import { RatingInline } from "./Stars";
import { isFeatured } from "@/lib/util";

export function ListingRow({ s, categoryNames = [] }: { s: Software; categoryNames?: string[] }) {
  const featured = isFeatured(s.featuredUntil);
  return (
    <li className={`listing${featured ? " featured" : ""}`}>
      <LogoBox name={s.name} url={s.logoUrl} />
      <div>
        <h3>
          <Link href={`/software/${s.slug}`}>{s.name}</Link>
        </h3>
        <p className="tagline">{s.tagline}</p>
        <div className="meta">
          {featured && <span className="featured-tag">★ Featured</span>}
          <RatingInline avg={s.ratingAvg} count={s.reviewCount} />
          {categoryNames.length > 0 && <span>{categoryNames.join(", ")}</span>}
          {s.regions.length > 0 && <span>{s.regions.slice(0, 4).join(", ")}</span>}
          <Link className="compare-tag" href={`/compare?add=${s.slug}`} rel="nofollow">Compare</Link>
        </div>
      </div>
    </li>
  );
}
