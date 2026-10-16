import type { MetadataRoute } from "next";
import { SITE } from "@/lib/site";

// Web app manifest: lets people install the site on phones and desktops like an app.
export default function manifest(): MetadataRoute.Manifest {
  return {
    name: `${SITE.name} — ${SITE.tagline}`,
    short_name: SITE.shortName,
    description: SITE.description,
    start_url: "/",
    scope: "/",
    display: "standalone",
    background_color: "#edf1ec",
    theme_color: "#0f6b5c",
    categories: ["business", "productivity", "utilities"],
    lang: "en",
    icons: [
      { src: "/icon-192.png", sizes: "192x192", type: "image/png" },
      { src: "/icon-512.png", sizes: "512x512", type: "image/png" },
      { src: "/icon-maskable-512.png", sizes: "512x512", type: "image/png", purpose: "maskable" },
    ],
    shortcuts: [
      { name: "Browse software", url: "/software" },
      { name: "Compare software", url: "/compare" },
      { name: "Write a review", url: "/software" },
    ],
  };
}
