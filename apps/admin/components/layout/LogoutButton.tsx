"use client";

import { useState, useTransition } from "react";
import { useRouter } from "next/navigation";
import { LogOut } from "lucide-react";

import { Button } from "@/components/ui/button";
import { logout } from "@/lib/api/client";

export function LogoutButton() {
  const router = useRouter();
  const [isPending, startTransition] = useTransition();
  const [signingOut, setSigningOut] = useState(false);

  const handleClick = async () => {
    setSigningOut(true);
    await logout();
    startTransition(() => {
      router.replace("/connexion");
      router.refresh();
    });
  };

  return (
    <Button
      variant="ghost"
      size="sm"
      onClick={handleClick}
      disabled={signingOut || isPending}
      className="text-muted-foreground"
    >
      <LogOut aria-hidden="true" />
      {signingOut ? "Déconnexion…" : "Se déconnecter"}
    </Button>
  );
}
