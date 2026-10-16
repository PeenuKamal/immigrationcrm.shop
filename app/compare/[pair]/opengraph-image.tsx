import { loadCompare, parseCompare } from "@/lib/compare";
import { ogCard, OG_SIZE } from "@/components/og";
export const size = OG_SIZE;
export const contentType = "image/png";
export default async function Image({ params }: { params: Promise<{ pair: string }> }) {
  const items = await loadCompare(parseCompare((await params).pair));
  return ogCard({ eyebrow: "Side-by-side comparison", title: items.map((i) => i.s.name).join(" vs ") || "Compare software", subtitle: "Verified ratings, features, markets and pros & cons" });
}
