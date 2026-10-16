import type { MetadataRoute } from "next";
import { appUrl } from "@/lib/util";

const PRIVATE = ["/admin", "/account", "/vendor", "/api/", "/signin", "/md/", "/*/review$", "/*/claim$", "/*?*sort=", "/*?*region="];

// Search engines and AI assistants are explicitly welcome, including training crawlers,
// so the site can be cited by ChatGPT, Claude, Gemini, Perplexity, Copilot and others.
const AI_AND_SEARCH_BOTS = [
  "Googlebot", "Google-Extended", "GoogleOther", "Bingbot", "Applebot", "Applebot-Extended", "DuckDuckBot", "YandexBot",
  "GPTBot", "OAI-SearchBot", "ChatGPT-User",
  "ClaudeBot", "Claude-SearchBot", "Claude-User", "anthropic-ai",
  "PerplexityBot", "Perplexity-User",
  "Meta-ExternalAgent", "Meta-ExternalFetcher", "Amazonbot", "DuckAssistBot", "MistralAI-User", "cohere-ai", "YouBot", "CCBot", "Diffbot",
];

export default function robots(): MetadataRoute.Robots {
  const base = appUrl();
  return {
    rules: [
      { userAgent: AI_AND_SEARCH_BOTS, allow: ["/", "/llms.txt", "/llms-full.txt", "/*.md"], disallow: PRIVATE },
      { userAgent: "*", allow: "/", disallow: PRIVATE },
    ],
    sitemap: `${base}/sitemap.xml`,
    host: base,
  };
}
