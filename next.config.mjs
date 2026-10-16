/** @type {import('next').NextConfig} */
const nextConfig = {
  // Docker builds set BUILD_STANDALONE=1 for a small self-contained server bundle.
  output: process.env.BUILD_STANDALONE ? "standalone" : undefined,
  poweredByHeader: false,
  compress: true,
  experimental: { serverActions: { bodySizeLimit: "6mb" } },
  images: { remotePatterns: [{ protocol: "https", hostname: "**" }] },
  async rewrites() {
    return [
      // Markdown versions of pages for AI assistants (linked from llms.txt and <link rel="alternate">)
      { source: "/software/:slug([a-z0-9-]+).md", destination: "/md/software/:slug" },
      { source: "/category/:slug([a-z0-9-]+).md", destination: "/md/category/:slug" },
    ];
  },
  async headers() {
    return [
      {
        source: "/:path*",
        headers: [
          { key: "X-Content-Type-Options", value: "nosniff" },
          { key: "Referrer-Policy", value: "strict-origin-when-cross-origin" },
          { key: "X-Frame-Options", value: "SAMEORIGIN" },
          { key: "Permissions-Policy", value: "camera=(), microphone=(), geolocation=()" },
          { key: "Strict-Transport-Security", value: "max-age=63072000; includeSubDomains; preload" },
        ],
      },
      { source: "/md/:path*", headers: [{ key: "X-Robots-Tag", value: "noindex, follow" }] },
    ];
  },
};
export default nextConfig;
