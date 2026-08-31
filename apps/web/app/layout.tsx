import type { Metadata } from "next";
import { Footer } from "@/components/layout/Footer";
import { IntroReveal } from "@/components/common/IntroReveal";
import "./globals.css";

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
      className="h-full antialiased"
    >
      <body className="min-h-full flex flex-col">
        <IntroReveal />
        <div className="flex-1">{children}</div>
        <Footer />
      </body>
    </html>
  );
}
