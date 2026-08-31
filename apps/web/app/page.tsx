import Link from "next/link";
import { posts } from "@/lib/mock-data";
import ScrollExpand from "@/components/common/ScrollExpend";
import CarShowcase from "@/features/cars/CarShowcase";
import AboutSection from "@/features/about/AboutSection";
import ServicesSection from "@/features/services/ServiceSection";
import BlogSection from "@/features/blog/BlogSection";
import CustomerServiceSection from "@/features/nous-contacter/CustomerServiceSection";

export const revalidate = 3600;

export default function HomePage() {
  return (
    <>
      <ScrollExpand
        src="/hero.jpg"
        alt="Présentation du produit"
        title="Des voitures prêtes à vous suivre"
        startWidth={95}
        startHeight={95}
        startRadius={24}
        endRadius={0}
        mediaZoom={1.35}
        scrollDistance={1.2}
        overlayScrim={0.45}
      >
        <h1 className="mt-16 sm:mt-6 text-white/70 max-w-xl text-4xl font-black leading-[0.92] tracking-tight sm:text-5xl lg:text-7xl">
          La route commence
          <br />
          avec la confiance.
        </h1>
        <p className="mt-2 text-white/80">
          Conduisez votre voiture de rêve en tout temps et en tout lieu.
        </p>
        <div className="mt-8 flex flex-wrap gap-3">
          <Link
            href="/voitures"
            className="group inline-flex items-center gap-2 rounded-full bg-white px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-xl shadow-black/20 transition duration-300 hover:-translate-y-0.5 hover:bg-slate-100 md:px-6 md:py-3.5 md:text-base"
          >
            Voir les véhicules
          </Link>

          <Link
            href="/services"
            className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-xl transition duration-300 hover:-translate-y-0.5 hover:bg-white/15 md:px-6 md:py-3.5 md:text-base"
          >
            Découvrir l&apos;atelier
          </Link>
        </div>
      </ScrollExpand>

      <>
        <AboutSection />
        <CarShowcase />

        <BlogSection posts={posts} />
        <ServicesSection />

       <CustomerServiceSection />
      </>
    </>
  );
}
