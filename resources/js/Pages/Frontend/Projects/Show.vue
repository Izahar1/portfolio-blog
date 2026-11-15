<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });
defineProps({ project: Object });
</script>

<template>
    <Head :title="project.seo?.meta_title || project.title"><meta name="description" :content="project.seo?.meta_description || project.summary || ''"><link v-if="project.seo?.canonical_url" rel="canonical" :href="project.seo.canonical_url"><meta v-if="project.seo?.noindex" name="robots" content="noindex"></Head>
    <article class="mx-auto max-w-3xl"><Link :href="route('projects.index')" class="text-sm text-cyan-200">← All projects</Link><img v-if="project.thumbnail_media?.url" :src="project.thumbnail_media.url" :alt="project.thumbnail_media.alt_text || project.title" class="mt-8 max-h-[28rem] w-full rounded-2xl object-cover"><p class="mt-8 text-sm uppercase tracking-widest text-cyan-300">Project</p><h1 class="mt-3 text-4xl font-bold sm:text-5xl">{{ project.title }}</h1><p v-if="project.summary" class="mt-5 text-xl leading-8 text-slate-300">{{ project.summary }}</p><div class="mt-5 flex flex-wrap gap-2"><span v-for="item in project.categories" :key="item.id" class="rounded-full bg-white/10 px-3 py-1 text-sm">{{ item.name }}</span><span v-for="item in project.skills" :key="`skill-${item.id}`" class="rounded-full border border-white/15 px-3 py-1 text-sm text-slate-300">{{ item.name }}</span></div><div class="mt-10 whitespace-pre-wrap text-base leading-8 text-slate-200">{{ project.description }}</div><div v-if="project.images.length" class="mt-10 grid gap-4 sm:grid-cols-2"><figure v-for="image in project.images" :key="image.id"><img :src="image.media.url" :alt="image.media.alt_text || project.title" loading="lazy" class="w-full rounded-xl object-cover"><figcaption v-if="image.caption" class="mt-2 text-sm text-slate-400">{{ image.caption }}</figcaption></figure></div><div class="mt-8 flex gap-3"><a v-if="project.demo_url" :href="project.demo_url" target="_blank" rel="noopener noreferrer" class="rounded-full bg-cyan-400 px-5 py-3 font-semibold text-slate-950">Visit project</a><a v-if="project.repository_url" :href="project.repository_url" target="_blank" rel="noopener noreferrer" class="rounded-full border border-white/20 px-5 py-3">Source code</a></div></article>
</template>
