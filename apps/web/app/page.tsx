import Image from "next/image";
import Link from "next/link";
import { CarCard } from "@/components/car/CarCard";
import { PostCard } from "@/components/content/PostCard";
import { ServiceCard } from "@/components/content/ServiceCard";
import { cars, posts, services } from "@/lib/mock-data";
import ScrollExpand from "@/components/common/ScrollExpend";

export const revalidate = 3600;

export default function HomePage() {
  const featuredCars = cars.filter((car) => car.featured);
  return (
    <>
      <ScrollExpand
        src="/hero.jpg"
        alt="Présentation du produit"
        title="Construit pour durer"
        scrollHint="Scrollez pour découvrir"
        startWidth={42}
        startHeight={58}
        startRadius={24}
        endRadius={0}
        mediaZoom={1.35}
        scrollDistance={1.2}
        overlayScrim={0.45}
      >
        <h2 className="text-2xl font-bold text-white">Chaque pixel compte</h2>
        <p className="mt-2 text-white/80">
          Le cadre s&apos;ouvre au fil du scroll et laisse toute la place à
          votre image.
        </p>
      </ScrollExpand>

      {/* Le reste de votre page vient juste après */}
      <>
        <section className="relative isolate overflow-hidden rounded-[36px] bg-slate-950 text-white">
          {/* Background effects */}
          <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.22),transparent_30%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.18),transparent_28%),linear-gradient(135deg,#020617_0%,#0f172a_45%,#111827_100%)]" />
          <div className="absolute inset-0 opacity-30 mix-blend-screen [background-image:linear-gradient(rgba(255,255,255,0.06)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.06)_1px,transparent_1px)] [background-size:32px_32px]" />

          {/* Image */}
          <div className="relative h-[340px] w-full sm:h-[420px] lg:absolute lg:inset-y-0 lg:right-0 lg:h-full lg:w-[52%]">
            <div className="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent z-10" />
            <Image
              src="/car.jpeg"
              alt="Voiture mise en avant"
              fill
              priority
              sizes="(min-width: 1024px) 52vw, 100vw"
              className="object-cover object-center scale-105 transition duration-700 ease-out hover:scale-110"
            />
          </div>

          {/* Content */}
          <div className="relative px-6 pb-10 pt-10 sm:px-10 sm:pt-14 lg:max-w-[50%] lg:px-14 lg:py-20">
            <div className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm text-white/85 backdrop-blur-xl shadow-lg shadow-black/20">
              <span className="inline-flex h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_20px_rgba(52,211,153,0.8)]" />
              <span>1000+ véhicules accompagnés avec succès</span>
            </div>

            <h1 className="mt-6 max-w-xl text-4xl font-black leading-[0.92] tracking-tight sm:text-5xl lg:text-7xl">
              La route commence
              <br />
              avec la confiance.
            </h1>

            <p className="mt-6 max-w-lg text-base leading-7 text-slate-300 sm:text-lg">
              Trouvez un véhicule d&apos;occasion fiable, ou confiez votre
              voiture à une équipe qui travaille avec exigence, transparence et
              précision.
            </p>

            <div className="mt-8 flex flex-wrap gap-3">
              <Link
                href="/voitures"
                className="group inline-flex items-center gap-2 rounded-full bg-white px-6 py-3.5 font-semibold text-slate-950 shadow-xl shadow-black/20 transition duration-300 hover:-translate-y-0.5 hover:bg-slate-100"
              >
                Voir les véhicules
                <span className="transition-transform duration-300 group-hover:translate-x-1">
                  →
                </span>
              </Link>

              <Link
                href="/services"
                className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-6 py-3.5 font-semibold text-white backdrop-blur-xl transition duration-300 hover:-translate-y-0.5 hover:bg-white/15"
              >
                Découvrir l&apos;atelier
              </Link>
            </div>

            <div className="mt-10 grid max-w-xl grid-cols-2 gap-4 sm:grid-cols-4">
              <div className="rounded-2xl border border-white/10 bg-white/8 p-4 backdrop-blur-xl">
                <p className="text-3xl font-black text-white">120+</p>
                <p className="mt-1 text-sm text-slate-300">Véhicules vendus</p>
              </div>
              <div className="rounded-2xl border border-white/10 bg-white/8 p-4 backdrop-blur-xl">
                <p className="text-3xl font-black text-white">15+</p>
                <p className="mt-1 text-sm text-slate-300">Techniciens</p>
              </div>
              <div className="rounded-2xl border border-white/10 bg-white/8 p-4 backdrop-blur-xl">
                <p className="text-3xl font-black text-white">98%</p>
                <p className="mt-1 text-sm text-slate-300">
                  Clients satisfaits
                </p>
              </div>
              <div className="rounded-2xl border border-white/10 bg-white/8 p-4 backdrop-blur-xl">
                <p className="text-3xl font-black text-white">3 ans</p>
                <p className="mt-1 text-sm text-slate-300">Garantie atelier</p>
              </div>
            </div>
          </div>

          {/* Floating card */}
          <div className="relative z-10 mt-8 px-6 pb-6 sm:px-10 lg:absolute lg:bottom-8 lg:left-8 lg:mt-0 lg:max-w-md lg:px-0 lg:pb-0">
            <div className="rounded-[28px] border border-white/10 bg-white/10 p-5 backdrop-blur-2xl shadow-2xl shadow-black/30">
              <p className="text-sm uppercase tracking-[0.22em] text-slate-300">
                Expérience premium
              </p>
              <p className="mt-2 text-lg font-semibold text-white">
                Inspection, conseil et accompagnement complet pour acheter ou
                entretenir votre voiture.
              </p>
            </div>
          </div>
        </section>

        <section className="mx-auto max-w-7xl px-5 py-20 lg:px-8">
          <div className="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
              <p className="text-sm font-bold uppercase tracking-[.2em] text-emerald-700">
                Nos arrivages
              </p>
              <h2 className="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
                Des voitures prêtes à vous suivre.
              </h2>
            </div>
            <Link href="/voitures" className="font-bold text-emerald-700">
              Voir tout le catalogue →
            </Link>
          </div>
          <div className="mt-10 grid gap-6 md:grid-cols-2">
            {featuredCars.map((car) => (
              <CarCard key={car.slug} car={car} />
            ))}
          </div>
        </section>
        <section className="bg-emerald-50">
          <div className="mx-auto grid max-w-7xl gap-10 px-5 py-20 lg:grid-cols-[.8fr_1.2fr] lg:px-8">
            <div>
              <p className="text-sm font-bold uppercase tracking-[.2em] text-emerald-700">
                L&apos;atelier Mecano
              </p>
              <h2 className="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
                Bien entretenir, c&apos;est rouler plus serein.
              </h2>
              <p className="mt-5 max-w-md leading-7 text-slate-600">
                Du diagnostic à la carrosserie, nous vous expliquons ce que nous
                faisons et pourquoi nous le faisons.
              </p>
              <Link
                href="/services"
                className="mt-7 inline-flex rounded-full bg-slate-950 px-5 py-3 font-bold text-white"
              >
                Nos services →
              </Link>
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
              {services.map((service) => (
                <ServiceCard key={service.slug} service={service} />
              ))}
            </div>
          </div>
        </section>
        <section className="mx-auto max-w-7xl px-5 py-20 lg:px-8">
          <div className="flex items-end justify-between gap-5">
            <div>
              <p className="text-sm font-bold uppercase tracking-[.2em] text-emerald-700">
                Carnet de route
              </p>
              <h2 className="mt-3 text-3xl font-black tracking-tight text-slate-950">
                Les bons réflexes, simplement.
              </h2>
            </div>
            <Link
              href="/blog"
              className="hidden font-bold text-emerald-700 sm:block"
            >
              Tous nos conseils →
            </Link>
          </div>
          <div className="mt-10 grid gap-6 md:grid-cols-3">
            {posts.map((post) => (
              <PostCard key={post.slug} post={post} />
            ))}
          </div>
        </section>
        <section className="bg-slate-950 px-5 py-20 text-white">
          <div className="mx-auto max-w-4xl text-center">
            <p className="text-sm font-bold uppercase tracking-[.2em] text-emerald-300">
              Un projet en tête ?
            </p>
            <h2 className="mt-4 text-4xl font-black tracking-tight sm:text-5xl">
              Dites-nous ce que vous cherchez.
            </h2>
            <p className="mx-auto mt-5 max-w-2xl text-lg leading-8 text-slate-300">
              Un modèle précis, un budget, une réparation à prévoir : commençons
              par une conversation simple.
            </p>
            <Link
              href="/contact"
              className="mt-8 inline-flex rounded-full bg-emerald-400 px-6 py-3.5 font-extrabold text-slate-950"
            >
              Contacter Mecano →
            </Link>
          </div>
        </section>
      </>
    </>
  );
}
