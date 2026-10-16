import { softwareMarkdown, MD_HEADERS } from "@/lib/markdown";
import { url } from "@/lib/site";

export async function GET(_: Request, { params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const md = await softwareMarkdown(slug);
  if (!md) return new Response("Not found", { status: 404 });
  return new Response(md, { headers: { ...MD_HEADERS, Link: `<${url(`/software/${slug}`)}>; rel="canonical"` } });
}
