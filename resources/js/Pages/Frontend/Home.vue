<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });
defineProps({ profile: Object, projects: Array, posts: Array });
</script>

<template>
    <Head :title="profile?.full_name || 'Portfolio'" />
    <section class="grid items-center gap-10 py-10 md:grid-cols-[1.3fr_0.7fr] md:py-20">
        <div>
            <p class="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-cyan-300">Portfolio &amp; journal</p>
            <h1 class="max-w-3xl text-4xl font-bold leading-tight tracking-tight sm:text-6xl">{{ profile?.headline || 'Thoughtful digital experiences, built for people.' }}</h1>
            <p class="mt-6 max-w-2xl whitespace-pre-line text-lg leading-8 text-slate-300">{{ profile?.bio || 'I design and build useful web experiences with a focus on clarity, performance, and craft.' }}</p>
            <div class="mt-8 flex flex-wrap gap-3"><Link :href="route('projects.index')" class="rounded-full bg-cyan-400 px-6 py-3 font-semibold text-slate-950">Explore my work</Link><Link :href="route('contact.index')" class="rounded-full border border-white/20 px-6 py-3 font-semibold">Start a conversation</Link></div>
        </div>
        <div class="rounded-3xl border border-white/10 bg-gradient-to-br from-cyan-300/20 via-indigo-400/10 to-transparent p-8 sm:p-12">
            <p class="text-sm text-cyan-200">{{ profile?.location || 'Available for select projects' }}</p>
            <p class="mt-5 text-3xl font-semibold leading-snug">“Good work should feel simple to use and rewarding to return to.”</p>
            <p class="mt-6 text-sm text-slate-400">{{ profile?.full_name || 'Independent developer' }}</p>
        </div>
    </section>
    <section class="mt-16">
        <div class="mb-6 flex items-end justify-between"><div><p class="text-sm uppercase tracking-widest text-cyan-300">Selected work</p><h2 class="mt-2 text-2xl font-bold">Featured projects</h2></div><Link :href="route('projects.index')" class="text-sm text-cyan-200">All projects →</Link></div>
        <div v-if="projects.length" class="grid gap-5 md:grid-cols-3"><article v-for="project in projects" :key="project.id" class="rounded-2xl border border-white/10 bg-white/[0.03] p-6"><p class="text-xs uppercase tracking-wider text-slate-400">Project</p><h3 class="mt-3 text-xl font-semibold">{{ project.title }}</h3><p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-300">{{ project.summary }}</p><Link :href="route('projects.show', project.slug)" class="mt-5 inline-block text-sm text-cyan-200">View project →</Link></article></div>
        <p v-else class="rounded-2xl border border-dashed border-white/15 p-8 text-slate-400">Projects will appear here once published.</p>
    </section>
    <section class="mt-16">
        <div class="mb-6 flex items-end justify-between"><div><p class="text-sm uppercase tracking-widest text-cyan-300">Notes and ideas</p><h2 class="mt-2 text-2xl font-bold">Latest writing</h2></div><Link :href="route('blog.index')" class="text-sm text-cyan-200">Visit the blog →</Link></div>
        <div v-if="posts.length" class="grid gap-5 md:grid-cols-3"><article v-for="post in posts" :key="post.id" class="rounded-2xl border border-white/10 p-6"><p class="text-xs text-slate-400">{{ post.category?.name || 'Article' }} · {{ new Date(post.published_at).toLocaleDateString() }}</p><h3 class="mt-3 text-xl font-semibold">{{ post.title }}</h3><p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-300">{{ post.excerpt }}</p><Link :href="route('blog.show', post.slug)" class="mt-5 inline-block text-sm text-cyan-200">Read article →</Link></article></div>
        <p v-else class="rounded-2xl border border-dashed border-white/15 p-8 text-slate-400">New articles will appear here.</p>
    </section>
</template>
