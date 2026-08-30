import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { notFound } from "next/navigation";
import { getPost, posts } from "@/lib/mock-data";
import { formatDate } from "@/lib/format";
interface PostPageProps { params: Promise<{ slug: string }>; }
export function generateStaticParams() { return posts.map(({ slug }) => ({ slug })); }
export async function generateMetadata({ params }: PostPageProps): Promise<Metadata> { const post = getPost((await params).slug); return post ? { title: post.title, description: post.excerpt } : {}; }
export default async function PostPage({ params }: PostPageProps) { const post = getPost((await params).slug); if (!post) notFound(); return <main><article className="mx-auto max-w-3xl px-5 py-10 sm:py-16"><Link href="/blog" className="text-sm font-bold text-emerald-700">← Tous les conseils</Link><p className="mt-8 text-sm font-bold uppercase tracking-[.2em] text-emerald-700">{post.category} · {formatDate(post.publishedAt)}</p><h1 className="mt-4 text-4xl font-black tracking-tight text-slate-950 sm:text-6xl">{post.title}</h1><p className="mt-6 text-xl leading-8 text-slate-600">{post.excerpt}</p><div className="relative mt-10 aspect-[16/9] overflow-hidden rounded-3xl"><Image src={post.image} alt="" fill priority sizes="(max-width: 768px) 100vw, 768px" className="object-cover" /></div><div className="prose prose-slate mt-10 max-w-none text-lg leading-8">{post.body.map((paragraph) => <p key={paragraph}>{paragraph}</p>)}</div></article><section className="bg-emerald-50 px-5 py-14"><div className="mx-auto max-w-3xl"><h2 className="text-2xl font-black text-slate-950">Un doute sur votre véhicule ?</h2><p className="mt-3 text-slate-600">L&apos;atelier Mecano peut vous aider à faire le point.</p><Link href="/contact" className="mt-5 inline-block font-bold text-emerald-700">Échanger avec l&apos;atelier →</Link></div></section></main>; }
