<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
defineProps({ counts: Object, recentInquiries: Array });
</script>

<template>
    <Head title="CMS overview" />
    <div><p class="text-sm font-semibold uppercase tracking-widest text-cyan-700">Workspace</p><h1 class="mt-2 text-3xl font-bold">Overview</h1><div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4"><article v-for="item in [{ label: 'Blog posts', value: counts.posts }, { label: 'Drafts', value: counts.drafts }, { label: 'Projects', value: counts.projects }, { label: 'New inquiries', value: counts.newInquiries }]" :key="item.label" class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">{{ item.label }}</p><p class="mt-2 text-3xl font-bold">{{ item.value }}</p></article></div><section class="mt-8 rounded-2xl bg-white p-6 shadow-sm"><div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Recent inquiries</h2><Link :href="route('admin.inquiries.index')" class="text-sm text-cyan-800">View inbox →</Link></div><p v-if="!recentInquiries.length" class="mt-5 text-sm text-slate-500">No messages have arrived yet.</p><ul v-else class="mt-4 divide-y"><li v-for="inquiry in recentInquiries" :key="inquiry.id" class="flex justify-between gap-4 py-3"><div><p class="font-medium">{{ inquiry.subject || 'New message' }}</p><p class="text-sm text-slate-500">{{ inquiry.name }}</p></div><span class="text-sm capitalize text-slate-500">{{ inquiry.status }}</span></li></ul></section></div>
</template>
