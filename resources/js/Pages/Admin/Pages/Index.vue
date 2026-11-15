<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
defineProps({ pages: Object });

function remove(page) {
    if (window.confirm(`Move “${page.title}” to trash?`)) router.delete(route('admin.pages.destroy', page.id));
}
</script>

<template>
    <Head title="Pages" />
    <div><div class="flex items-end justify-between"><div><p class="text-sm font-semibold uppercase tracking-widest text-cyan-700">Content</p><h1 class="mt-2 text-3xl font-bold">Pages</h1></div><Link :href="route('admin.pages.create')" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">New page</Link></div><div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-5 py-3 font-medium">Page</th><th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 font-medium">Sections</th><th class="px-5 py-3"></th></tr></thead><tbody class="divide-y"><tr v-for="page in pages.data" :key="page.id"><td class="px-5 py-4"><p class="font-medium">{{ page.title }}</p><p class="text-xs text-slate-500">/{{ page.slug }}</p></td><td class="px-5 py-4 capitalize">{{ page.status }}</td><td class="px-5 py-4">{{ page.sections_count }}</td><td class="space-x-3 px-5 py-4 text-right"><Link :href="route('admin.pages.edit', page.id)" class="text-cyan-800">Edit</Link><button @click="remove(page)" class="text-rose-700">Delete</button></td></tr><tr v-if="!pages.data.length"><td colspan="4" class="px-5 py-10 text-center text-slate-500">No custom pages yet.</td></tr></tbody></table></div></div>
</template>
