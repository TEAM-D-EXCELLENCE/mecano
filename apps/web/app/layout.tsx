import type { Metadata } from "next";
import { Footer } from "@/components/layout/Footer";
import { SiteHeader } from "@/components/layout/SiteHeader";
import { IntroReveal } from "@/components/common/IntroReveal";
import "./globals.css";
import { Corinthia } from 'next/font/google';

const corinthia = Corinthia({
  weight: '400',
  subsets: ['latin'],
  variable: '--font-corinthia', // Déclaration de la variable CSS
});

export const metadata: Metadata = {
  title: { default: "Mecano | Véhicules & atelier", template: "%s | Mecano" },
  description:
    "Véhicules d'occasion sélectionnés et atelier automobile de confiance.",
  icons: { icon: "/icon.svg" },
};

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html
      lang="fr"
      className={`${corinthia.variable} h-full antialiased`}
    >
      <body className="min-h-full flex flex-col">
        <IntroReveal />
        <SiteHeader />
        <div className="flex-1">{children}</div>
        <Footer />
      </body>
    </html>
  );
}
