import Link from "next/link";

const navigation = [
  { href: "/voitures", label: "Nos véhicules" },
  { href: "/services", label: "Atelier" },
  { href: "/blog", label: "Conseils" },
  { href: "/contact", label: "Contact" },
];

/** The desktop navigation is server-rendered for crawlability and fast first paint. */
export function Header() {
  return (
    <header className="border-b border-slate-200 bg-white/95 backdrop-blur">
      <div className="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 lg:px-8">
        <Link href="/" className="flex items-center gap-3" aria-label="Mecano, accueil">
          <span className="grid size-10 place-items-center rounded-xl bg-emerald-700 text-xl font-black text-white">M</span>
          <span className="text-lg font-extrabold tracking-tight text-slate-950">Mecano<span className="text-emerald-700">.</span></span>
        </Link>
        <nav className="hidden items-center gap-7 md:flex" aria-label="Navigation principale">
          {navigation.map((item) => <Link key={item.href} href={item.href} className="text-sm font-semibold text-slate-600 transition hover:text-emerald-700">{item.label}</Link>)}
        </nav>
        <a href="https://wa.me/237600000000" className="rounded-full bg-slate-950 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700">Parler à l&apos;atelier <span aria-hidden>↗</span></a>
      </div>
    </header>
  );
}
