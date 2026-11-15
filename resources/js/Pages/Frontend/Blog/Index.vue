<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });
const props = defineProps({ posts: Object, categories: Array, filters: Object });
const search = ref(props.filters.search || '');

function submitSearch() {
    router.get(route('blog.index'), { search: search.value }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Blog" />
    <section>
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-300">The journal</p>
        <div class="mt-3 flex flex-col justify-between gap-5 sm:flex-row sm:items-end"><div><h1 class="text-4xl font-bold">Ideas, notes, and lessons</h1><p class="mt-3 max-w-xl text-slate-300">Writing about the work behind useful products and thoughtful web experiences.</p></div><form @submit.prevent="submitSearch" class="flex gap-2"><label class="sr-only" for="blog-search">Search articles</label><input id="blog-search" v-model="search" class="rounded-full border-white/15 bg-white/5 text-white placeholder:text-slate-500 focus:border-cyan-300 focus:ring-cyan-300" placeholder="Search articles"><button class="rounded-full bg-cyan-400 px-5 font-semibold text-slate-950">Search</button></form></div>
        <nav v-if="categories.length" class="mt-8 flex flex-wrap gap-2" aria-label="Blog categories"><Link :href="route('blog.index')" class="rounded-full border border-white/15 px-4 py-2 text-sm">All topics</Link><Link v-for="category in categories" :key="category.id" :href="route('blog.category', category.slug)" class="rounded-full border border-white/15 px-4 py-2 text-sm text-slate-300 hover:border-cyan-300">{{ category.name }}</Link></nav>
        <div v-if="posts.data.length" class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3"><article v-for="post in posts.data" :key="post.id" class="flex flex-col rounded-2xl border border-white/10 bg-white/[0.03] p-6"><p class="text-xs text-cyan-200">{{ post.category?.name || 'Article' }} <span class="text-slate-500">· {{ new Date(post.published_at).toLocaleDateString() }}</span></p><h2 class="mt-4 text-xl font-semibold"><Link :href="route('blog.show', post.slug)" class="hover:text-cyan-200">{{ post.title }}</Link></h2><p class="mt-3 flex-1 text-sm leading-6 text-slate-300">{{ post.excerpt }}</p><div class="mt-6 flex items-center justify-between text-xs text-slate-400"><span>By {{ post.author?.name || 'Author' }}</span><Link :href="route('blog.show', post.slug)" class="text-cyan-200">Read →</Link></div></article></div>
        <p v-else class="mt-10 rounded-2xl border border-dashed border-white/15 p-10 text-center text-slate-400">No published articles match this search.</p>
        <nav v-if="posts.links.length > 3" class="mt-10 flex flex-wrap gap-2" aria-label="Pagination"><Link v-for="(link, index) in posts.links" :key="index" :href="link.url || '#'" :class="['rounded-lg px-3 py-2 text-sm', link.active ? 'bg-cyan-400 text-slate-950' : 'border border-white/10 text-slate-300', !link.url && 'pointer-events-none opacity-40']" v-html="link.label" /></nav>
    </section>
</template>
