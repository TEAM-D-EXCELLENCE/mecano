# Conventions frontend — `apps/web` et `apps/admin`

Next.js (App Router), React, TypeScript strict, Tailwind CSS, shadcn/ui.

Les deux applications partagent ces conventions mais **aucun code** (décision D03). Ce qui les différencie est signalé explicitement.

---

## Règles fondatrices

1. **Server Component par défaut.** `"use client"` se justifie en revue de PR.
2. **Aucun appel direct à l'API depuis le navigateur, côté `admin`.** Tout passe par le BFF.
3. **`types/api.d.ts` est généré.** Le modifier à la main fait refuser la PR.
4. **Aucune règle métier côté front.** Voir le [contrat FE/BE](contrat-frontend-backend.md).
5. **Un état d'interface partageable vit dans l'URL**, pas dans un `useState`.

---

## Server et Client Components

### `apps/web` — la vitrine

Tout est Server Component, sauf quatre endroits : la galerie, les filtres, le lecteur vidéo, le menu mobile.

C'est ce qui tient le budget de 120 Ko de JavaScript. Chaque nouveau `"use client"` doit expliquer en PR pourquoi le serveur ne peut pas faire le travail.

Un composant client doit être **une feuille de l'arbre**. Rendre un composant parent client rend clients tous ses enfants, et le bundle explose.

```tsx
// ✅ le serveur récupère, le client gère seulement l'interaction
export default async function CarPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params
  const car = await getCar(slug)          // serveur
  return (
    <>
      <CarSpecs car={car} />              {/* serveur */}
      <CarGallery photos={car.photos} />  {/* client — l'interaction seulement */}
    </>
  )
}
```

### `apps/admin` — le backoffice

Le compromis s'inverse : rien n'est indexé, l'interactivité domine. Les composants clients sont donc la norme. Deux exceptions importantes :

- **La garde d'authentification est côté serveur** (`layout.tsx`), lecture du cookie httpOnly. Aucun contenu protégé n'apparaît avant redirection.
- Les chargements initiaux de listes sont faits côté serveur, ce qui évite un état de chargement au premier rendu.

---

## Récupération de données

### `apps/web` — directement l'API publique, avec tags

```ts
export async function getCar(slug: string) {
  const res = await fetch(`${process.env.API_BASE_URL}/cars/${slug}`, {
    next: { tags: [`car:${slug}`, 'cars'], revalidate: 3600 },
  })
  if (res.status === 404) notFound()
  if (!res.ok) throw new ApiError(res)
  return (await res.json()) as CarDetail
}
```

**Les tags sont obligatoires.** Sans eux, le webhook de revalidation n'a rien à invalider et la page reste périmée jusqu'à l'expiration du filet horaire. Les tags viennent de la table contractuelle de [07 — Performance et SEO](../01-architecture/07-performance-seo-pwa.md#ce-qui-déclenche-une-revalidation).

`revalidate: 3600` est le filet de sécurité. Il est obligatoire lui aussi.

### `apps/admin` — par le BFF, jamais en cache

```ts
// app/bff/[...path]/route.ts
export async function POST(req: Request, { params }: { params: Promise<{ path: string[] }> }) {
  const token = (await cookies()).get(process.env.COOKIE_NAME!)?.value
  if (!token) return Response.json({ error: { code: 'UNAUTHENTICATED' } }, { status: 401 })

  const { path } = await params
  const res = await fetch(`${process.env.API_BASE_URL}/${path.join('/')}`, {
    method: 'POST',
    headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' },
    body: await req.text(),
    cache: 'no-store',
  })
  return new Response(res.body, { status: res.status, headers: { 'Content-Type': 'application/json' } })
}
```

Le BFF **relaie fidèlement** : même code de statut, même corps d'erreur. Il ne réinterprète rien — sinon le débogage devient impossible.

---

## L'état partageable vit dans l'URL

Règle ferme sur `apps/web`.

```tsx
// ✅ rendu serveur, indexable, partageable, le bouton retour fonctionne
export default async function CatalogPage({
  searchParams,
}: { searchParams: Promise<{ marque?: string; prix_max?: string; annee_min?: string }> }) {
  const filters = await searchParams
  const cars = await getCars(filters)
  return <CarGrid cars={cars} />
}

// ❌ invisible de Google, non partageable, casse le bouton retour
const [brand, setBrand] = useState<string>()
```

Les noms de paramètres sont **en français** (`marque`, `prix_max`, `annee_min`) : ils sont visibles dans l'URL par les visiteurs.

---

## Images

Le poste le plus lourd du site. Non négociable :

```tsx
<Image
  src={photo.published_url}
  alt={photo.alt}
  width={photo.width}       // fourni par l'API — OBLIGATOIRE
  height={photo.height}     // fourni par l'API — OBLIGATOIRE
  sizes="(max-width: 768px) 100vw, 50vw"
  priority={index === 0}    // la première image est le LCP
/>
```

- `width` et `height` **toujours** présents : c'est ce qui tient le CLS. Sans eux, la page saute au chargement.
- `priority` sur la première image uniquement.
- Chargement paresseux partout ailleurs.
- Aucune URL Cloudinary construite côté front. L'API fournit les URL.
- Les vidéos en `preload="none"` avec une vignette.

---

## Composants

```
components/
├── ui/          shadcn/ui — GÉNÉRÉ, ne pas modifier à la main
├── car/         métier : CarCard, CarGallery, CarSpecs, WhatsAppButton, SoldBadge
├── layout/      Header, Footer, MobileNav
└── forms/       admin uniquement : CarForm, ServiceForm, PostForm
```

- Un fichier par composant, nommé comme le composant, en `PascalCase`.
- Props typées par une `interface` explicite, jamais `any`, jamais `React.FC`.
- Un composant de plus de 150 lignes se découpe.
- Pas de logique métier : elle vient de l'API.

### shadcn/ui

Les composants de `ui/` sont générés par la CLI et **ne se modifient pas à la main** : une personnalisation passe par les variantes (`cva`) ou par un composant qui l'enveloppe. Sinon la prochaine régénération écrase le travail.

---

## Tailwind

- **Mobile d'abord.** On écrit le style mobile, puis `md:`, `lg:`.
- Les couleurs, espacements et rayons viennent des jetons de `tailwind.config.ts`. Aucune valeur arbitraire (`text-[#1a2b3c]`) sans justification.
- Aucun CSS écrit à la main hors `globals.css`.
- L'ordre des classes est géré par `prettier-plugin-tailwindcss`, pas discuté en revue.
- Les variantes conditionnelles passent par `cn()` (`clsx` + `tailwind-merge`).

---

## Formulaires — `apps/admin`

`react-hook-form` + `zod`, schémas dérivés des types générés.

```tsx
const carSchema = z.object({
  brand_id:  z.number().int().positive(),
  model:     z.string().min(1).max(120),
  year:      z.number().int().min(1950).max(new Date().getFullYear() + 1),
  price_xaf: z.number().int().positive(),
})
```

- **La validation côté client double celle de l'API, elle ne la remplace pas.** Elle sert le confort ; l'API reste la seule autorité.
- Les erreurs `422` sont mappées **champ par champ**, sous chaque champ concerné.
- Un formulaire en cours de saisie n'est jamais perdu : un `401` redirige vers la connexion puis revient, sans vider la saisie.
- Bouton d'envoi désactivé pendant la soumission, avec un indicateur.

---

## Erreurs

Le front compare sur `error.code`, **jamais** sur `error.message` (qui peut changer sans préavis).

```ts
switch (error.code) {
  case 'QUOTA_EXCEEDED':  return `Quota atteint (${d.used}/${d.limit}), disponible le 1er du mois.`
  case 'CAR_NOT_PUBLISHABLE': return 'Ajoutez une photo principale avant de publier.'
  case 'UNAUTHENTICATED': return redirect('/login')
  default: return 'Une erreur est survenue. Réessayez.'
}
```

Tous les états à traiter sont listés dans le [contrat FE/BE](contrat-frontend-backend.md#les-états-que-le-front-doit-toujours-traiter). Ce ne sont pas des cas limites, ce sont des états normaux.

---

## Accessibilité

Minimum exigé, vérifié en revue :

- HTML sémantique : `<nav>`, `<main>`, `<article>`, `<button>` pour une action, `<a>` pour une navigation.
- Toute image porte un `alt` utile. L'API le fournit par défaut (« Toyota Corolla 2018 — photo 3 »).
- Tout élément interactif est atteignable au clavier, avec un focus **visible**.
- Contraste minimum 4,5:1 sur le texte.
- Les icônes seules ont un `aria-label`.
- La galerie se navigue au clavier (flèches, `Échap` pour fermer).

Un mécanicien consulte son backoffice sur un téléphone, dans un atelier, parfois avec les mains sales : les zones tactiles font au moins 44×44 px.

---

## TypeScript

- `strict: true`. Aucun `any`, aucun `@ts-ignore` sans commentaire justifiant.
- Types de l'API **toujours** importés depuis `types/api.d.ts`. Jamais redéclarés à la main.
- `unknown` plutôt que `any` quand le type est réellement inconnu.
- `tsc --noEmit` bloque la PR.

---

## Formatage

Les fonctions de formatage vivent dans `lib/format.ts`, jamais en ligne dans un composant :

```ts
export const formatPriceXaf = (v: number) =>
  `${new Intl.NumberFormat('fr-FR').format(v)} FCFA`      // 4 500 000 FCFA

export const formatMileage = (km: number) =>
  `${new Intl.NumberFormat('fr-FR').format(km)} km`        // 85 000 km
```

Ce fichier est le seul endroit du front à contenir de la logique testable — et il est testé (voir [tests](tests.md)).

---

## Ce qu'on ne fait pas

| Non retenu | Pourquoi |
|---|---|
| Client d'état global (Redux, Zustand) | Le serveur est l'état. Server Components + URL suffisent |
| Client de données (React Query, SWR) sur `web` | Le cache de `fetch` de Next fait le travail. Justifiable sur `admin` si le besoin apparaît |
| Bibliothèque de composants complète | Trop lourd pour le budget de la vitrine |
| CSS-in-JS | Incompatible avec les Server Components, et inutile avec Tailwind |
| `useEffect` pour récupérer des données | Server Components. Un `useEffect` de récupération est un signal d'erreur de conception |
| Barils (`index.ts` ré-exportant tout) | Casse le découpage de bundle |
