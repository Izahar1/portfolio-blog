<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });
defineProps({ post: Object });
</script>

<template>
    <Head :title="post.seo?.meta_title || post.title"><meta name="description" :content="post.seo?.meta_description || post.excerpt || ''"><link v-if="post.seo?.canonical_url" rel="canonical" :href="post.seo.canonical_url"><meta v-if="post.seo?.noindex" name="robots" content="noindex"></Head>
    <article class="mx-auto max-w-3xl">
        <Link :href="route('blog.index')" class="text-sm text-cyan-200">← All articles</Link>
        <p class="mt-8 text-sm text-cyan-200">{{ post.category?.name || 'Article' }} · {{ new Date(post.published_at).toLocaleDateString() }}</p>
        <h1 class="mt-3 text-4xl font-bold leading-tight sm:text-5xl">{{ post.title }}</h1>
        <p v-if="post.excerpt" class="mt-5 text-xl leading-8 text-slate-300">{{ post.excerpt }}</p>
        <p class="mt-5 text-sm text-slate-400">By {{ post.author?.name || 'Author' }}</p>
        <img v-if="post.featured_media?.url" :src="post.featured_media.url" :alt="post.featured_media.alt_text || post.title" class="mt-8 max-h-[28rem] w-full rounded-2xl object-cover">
        <div class="mt-10 whitespace-pre-wrap break-words border-t border-white/10 pt-8 text-base leading-8 text-slate-200">{{ post.content }}</div>
        <div v-if="post.tags?.length" class="mt-8 flex flex-wrap gap-2"><Link v-for="tag in post.tags" :key="tag.id" :href="route('blog.tag', tag.slug)" class="rounded-full border border-white/15 px-3 py-1 text-sm text-slate-300">#{{ tag.name }}</Link></div>
    </article>
</template>
