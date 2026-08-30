import type { MetadataRoute } from "next";
import { cars, posts } from "@/lib/mock-data";

const baseUrl = "https://garage.example.com";

/** The API will later provide the dynamic slugs used by this SEO entry point. */
export default function sitemap(): MetadataRoute.Sitemap {
  const staticPages = ["", "/voitures", "/services", "/blog", "/contact"].map((path) => ({ url: `${baseUrl}${path}`, lastModified: new Date() }));
  const carPages = cars.map((car) => ({ url: `${baseUrl}/voitures/${car.slug}`, lastModified: new Date() }));
  const postPages = posts.map((post) => ({ url: `${baseUrl}/blog/${post.slug}`, lastModified: new Date(post.publishedAt) }));
  return [...staticPages, ...carPages, ...postPages];
}
