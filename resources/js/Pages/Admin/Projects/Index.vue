<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
defineProps({ projects: Object });

function remove(project) {
    if (window.confirm(`Move “${project.title}” to trash?`)) router.delete(route('admin.projects.destroy', project.id));
}
</script>

<template>
    <Head title="Projects" />
    <div><div class="flex items-end justify-between gap-4"><div><p class="text-sm font-semibold uppercase tracking-widest text-cyan-700">Portfolio</p><h1 class="mt-2 text-3xl font-bold">Projects</h1></div><Link :href="route('admin.projects.create')" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">New project</Link></div><div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3"><article v-for="project in projects.data" :key="project.id" class="rounded-2xl bg-white p-5 shadow-sm"><div class="flex items-start justify-between gap-3"><div><h2 class="font-semibold">{{ project.title }}</h2><p class="mt-1 text-xs capitalize text-slate-500">{{ project.status }}</p></div><span v-if="project.featured" class="rounded-full bg-amber-100 px-2 py-1 text-xs text-amber-800">Featured</span></div><p class="mt-3 line-clamp-3 text-sm text-slate-600">{{ project.summary }}</p><div class="mt-5 flex gap-4 text-sm"><Link :href="route('admin.projects.edit', project.id)" class="text-cyan-800">Edit</Link><button @click="remove(project)" class="text-rose-700">Delete</button></div></article><p v-if="!projects.data.length" class="rounded-2xl border border-dashed p-8 text-slate-500">No projects yet. Add a project to build your portfolio.</p></div></div>
</template>
