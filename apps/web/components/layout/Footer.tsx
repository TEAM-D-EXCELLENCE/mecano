import Link from "next/link";

export function Footer() {
  return (
    <footer className="mt-auto bg-slate-950 text-slate-300">
      <div className="mx-auto grid max-w-7xl gap-10 px-5 py-14 sm:grid-cols-2 lg:grid-cols-4 lg:px-8">
        <div><p className="text-xl font-extrabold text-white">Mecano<span className="text-emerald-400">.</span></p><p className="mt-4 max-w-xs text-sm leading-6">Véhicules d&apos;occasion sélectionnés et atelier de confiance, au même endroit.</p></div>
        <div><h2 className="font-bold text-white">Explorer</h2><ul className="mt-4 space-y-2 text-sm"><li><Link href="/voitures">Véhicules</Link></li><li><Link href="/services">Services atelier</Link></li><li><Link href="/blog">Nos conseils</Link></li></ul></div>
        <div><h2 className="font-bold text-white">Nous trouver</h2><p className="mt-4 text-sm leading-6">Bonabéri, Douala<br />Lun. — Sam. · 8h — 18h</p></div>
        <div><h2 className="font-bold text-white">Un projet ?</h2><a className="mt-4 inline-flex text-sm font-bold text-emerald-400 hover:text-emerald-300" href="https://wa.me/237600000000">Écrire sur WhatsApp →</a></div>
      </div>
      <div className="border-t border-white/10 px-5 py-5 text-center text-xs text-slate-500">© {new Date().getFullYear()} Mecano. Tous droits réservés.</div>
    </footer>
  );
}
