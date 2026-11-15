<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({ resource: String, title: String, records: Object, columns: Array });

function remove(record) {
    if (window.confirm('Delete this item?')) router.delete(route('admin.resources.destroy', { resource: props.resource, id: record.id }));
}
</script>

<template>
    <Head :title="title" />
    <div>
        <div class="flex items-end justify-between gap-4"><div><p class="text-sm font-semibold uppercase tracking-widest text-cyan-700">Portfolio CMS</p><h1 class="mt-2 text-3xl font-bold">{{ title }}</h1></div><Link v-if="resource !== 'comments'" :href="route('admin.resources.create', { resource })" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">New {{ title.toLowerCase().replace(/s$/, '') }}</Link></div>
        <div class="mt-6 overflow-x-auto rounded-2xl bg-white shadow-sm"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th v-for="column in columns" :key="column" class="px-5 py-3 font-medium">{{ column.replaceAll('_', ' ') }}</th><th class="px-5 py-3"></th></tr></thead><tbody class="divide-y"><tr v-for="record in records.data" :key="record.id"><td v-for="column in columns" :key="column" class="max-w-xs truncate px-5 py-4" :title="record[column] ?? ''">{{ record[column] ?? '—' }}</td><td class="space-x-3 whitespace-nowrap px-5 py-4 text-right"><Link :href="route('admin.resources.edit', { resource, id: record.id })" class="text-cyan-800">{{ resource === 'comments' ? 'Moderate' : 'Edit' }}</Link><button @click="remove(record)" class="text-rose-700">Delete</button></td></tr><tr v-if="!records.data.length"><td :colspan="columns.length + 1" class="px-5 py-12 text-center text-slate-500">No {{ title.toLowerCase() }} yet.</td></tr></tbody></table><nav v-if="records.links.length > 3" class="flex gap-2 border-t p-4" aria-label="Pagination"><Link v-for="(link, index) in records.links" :key="index" :href="link.url || '#'" class="rounded border px-3 py-2 text-xs" v-html="link.label" /></nav></div>
    </div>
</template>
