import "server-only";
import { appUrl } from "./util";

/**
 * Tells IndexNow search engines (Bing — which powers ChatGPT search and Copilot — plus Yandex, Seznam, Naver)
 * that pages changed, so new reviews and listings are crawled within minutes. Fire-and-forget.
 */
export function pingIndexNow(paths: string[]) {
  const key = process.env.INDEXNOW_KEY;
  const base = appUrl();
  if (!key || base.includes("localhost") || !paths.length) return;
  const host = new URL(base).host;
  fetch("https://api.indexnow.org/indexnow", {
    method: "POST",
    headers: { "Content-Type": "application/json; charset=utf-8" },
    body: JSON.stringify({ host, key, keyLocation: `${base}/indexnow.txt`, urlList: [...new Set(paths)].map((p) => `${base}${p}`) }),
  }).catch(() => {});
}
