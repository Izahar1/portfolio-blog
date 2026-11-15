<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });
const props = defineProps({ projects: Object, categories: Array, skills: Array, filters: Object });
const form = useForm({ ...props.filters });
function search() { form.get(route('projects.index'), { preserveState: true, preserveScroll: true }); }
</script>

<template>
    <Head title="Projects" />
    <section><p class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-300">Selected work</p><h1 class="mt-3 text-4xl font-bold">Projects</h1><p class="mt-3 max-w-2xl text-slate-300">A collection of products, experiments, and collaborations.</p>
        <form class="mt-8 grid gap-3 rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:grid-cols-4" @submit.prevent="search"><label class="sr-only" for="project-search">Search projects</label><input id="project-search" v-model="form.search" placeholder="Search projects" class="rounded-lg border-white/10 bg-slate-950"><select v-model="form.category" aria-label="Filter by category" class="rounded-lg border-white/10 bg-slate-950"><option value="">All categories</option><option v-for="category in categories" :key="category.id" :value="category.slug">{{ category.name }}</option></select><select v-model="form.skill" aria-label="Filter by technology" class="rounded-lg border-white/10 bg-slate-950"><option value="">All technologies</option><option v-for="skill in skills" :key="skill.id" :value="skill.slug">{{ skill.name }}</option></select><button class="rounded-lg bg-cyan-400 px-4 py-2 font-semibold text-slate-950">Filter projects</button></form>
        <div v-if="projects.data.length" class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3"><article v-for="project in projects.data" :key="project.id" class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03]"><img v-if="project.thumbnail_media?.url" :src="project.thumbnail_media.url" :alt="project.thumbnail_media.alt_text || project.title" loading="lazy" class="h-48 w-full object-cover"><div class="p-6"><h2 class="text-xl font-semibold">{{ project.title }}</h2><p class="mt-3 text-sm leading-6 text-slate-300">{{ project.summary }}</p><div class="mt-4 flex flex-wrap gap-2"><span v-for="item in project.categories" :key="item.id" class="rounded-full bg-white/10 px-2.5 py-1 text-xs">{{ item.name }}</span></div><Link :href="route('projects.show', project.slug)" class="mt-5 inline-block text-sm text-cyan-200">Project details →</Link></div></article></div><p v-else class="mt-10 rounded-2xl border border-dashed border-white/15 p-10 text-slate-400">Published projects will appear here.</p>
        <nav v-if="projects.links.length > 3" class="mt-10 flex gap-2" aria-label="Pagination"><Link v-for="(link, index) in projects.links" :key="index" :href="link.url || '#'" class="rounded-lg border border-white/10 px-3 py-2 text-sm" v-html="link.label" /></nav>
    </section>
</template>
