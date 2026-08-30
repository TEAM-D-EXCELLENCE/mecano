import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // Le backoffice n'est jamais indexé. L'en-tête double la balise `robots` du
  // layout : elle couvre aussi les réponses du BFF, qui ne sont pas du HTML.
  async headers() {
    return [
      {
        source: "/:path*",
        headers: [
          { key: "X-Robots-Tag", value: "noindex, nofollow, noarchive" },
          { key: "X-Content-Type-Options", value: "nosniff" },
          { key: "X-Frame-Options", value: "DENY" },
          { key: "Referrer-Policy", value: "strict-origin-when-cross-origin" },
        ],
      },
    ];
  },
};

export default nextConfig;
