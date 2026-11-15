<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
defineProps({ blogs: Object });

function remove(blog) {
    if (window.confirm(`Move “${blog.title}” to trash?`)) {
        router.delete(route('admin.blogs.destroy', blog.id));
    }
}
</script>

<template>
    <Head title="Blog posts" />
    <div><div class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm font-semibold uppercase tracking-widest text-cyan-700">Content</p><h1 class="mt-2 text-3xl font-bold">Blog posts</h1></div><Link :href="route('admin.blogs.create')" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">New post</Link></div><div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm"><div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-5 py-3 font-medium">Title</th><th class="px-5 py-3 font-medium">Category</th><th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 font-medium">Updated</th><th class="px-5 py-3"></th></tr></thead><tbody class="divide-y"><tr v-for="blog in blogs.data" :key="blog.id"><td class="px-5 py-4 font-medium">{{ blog.title }}</td><td class="px-5 py-4 text-slate-500">{{ blog.category?.name || '—' }}</td><td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs capitalize">{{ blog.status }}</span></td><td class="px-5 py-4 text-slate-500">{{ new Date(blog.updated_at).toLocaleDateString() }}</td><td class="space-x-3 whitespace-nowrap px-5 py-4 text-right"><Link :href="route('admin.blogs.edit', blog.id)" class="text-cyan-800">Edit</Link><button @click="remove(blog)" class="text-rose-700">Delete</button></td></tr><tr v-if="!blogs.data.length"><td colspan="5" class="px-5 py-12 text-center text-slate-500">No posts yet. Create the first one.</td></tr></tbody></table></div><nav v-if="blogs.links.length > 3" class="flex gap-2 border-t p-4" aria-label="Pagination"><Link v-for="(link, index) in blogs.links" :key="index" :href="link.url || '#'" class="rounded border px-3 py-2 text-xs" v-html="link.label" /></nav></div></div>
</template>
