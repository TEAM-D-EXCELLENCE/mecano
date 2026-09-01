import Image from "next/image";
import Link from "next/link";
import type { Post } from "@/lib/mock-data";
import { formatDate } from "@/lib/format";

interface PostCardProps { post: Post; }

export function PostCard({ post }: PostCardProps) {
  return <article className="group overflow-hidden rounded-3xl border border-slate-200 bg-white"><Link href={`/blog/${post.slug}`}><div className="relative aspect-[16/10] overflow-hidden"><Image src={post.image} alt="" fill sizes="(max-width: 768px) 100vw, 33vw" className="object-cover transition duration-500 group-hover:scale-105" /></div><div className="p-6"><p className="text-xs font-bold uppercase tracking-widest text-emerald-700">{post.category} · {formatDate(post.publishedAt)}</p><h3 className="mt-3 text-xl font-extrabold tracking-tight text-slate-950">{post.title}</h3><p className="mt-3 text-sm leading-6 text-slate-600">{post.excerpt}</p><span className="mt-5 inline-block text-sm font-bold text-slate-950">Lire l&apos;article →</span></div></Link></article>;
}
