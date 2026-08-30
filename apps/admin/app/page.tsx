import { redirect } from "next/navigation";

/** Le backoffice n'a pas de page d'accueil propre : on entre par le tableau de bord. */
export default function Home() {
  redirect("/tableau-de-bord");
}
