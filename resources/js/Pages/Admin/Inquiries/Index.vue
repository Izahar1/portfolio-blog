<script setup>
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
defineProps({ inquiries: Object });

function updateStatus(inquiry, status) {
    router.patch(route('admin.inquiries.update', inquiry.id), { status }, { preserveScroll: true });
}
</script>

<template>
    <Head title="Contact inquiries" />
    <div><p class="text-sm font-semibold uppercase tracking-widest text-cyan-700">Operations</p><h1 class="mt-2 text-3xl font-bold">Contact inquiries</h1><div class="mt-6 space-y-4"><article v-for="inquiry in inquiries.data" :key="inquiry.id" class="rounded-2xl bg-white p-5 shadow-sm"><div class="flex flex-col justify-between gap-4 sm:flex-row"><div><p class="font-semibold">{{ inquiry.subject || 'No subject' }}</p><p class="mt-1 text-sm text-slate-600">{{ inquiry.name }} · <a :href="`mailto:${inquiry.email}`" class="text-cyan-800">{{ inquiry.email }}</a></p><p class="mt-4 whitespace-pre-wrap text-sm leading-6 text-slate-700">{{ inquiry.message }}</p><p class="mt-3 text-xs text-slate-400">{{ new Date(inquiry.created_at).toLocaleString() }}</p></div><label class="shrink-0 text-sm">Status<select :value="inquiry.status" @change="updateStatus(inquiry, $event.target.value)" class="mt-1 block rounded-lg border-slate-300 text-sm"><option value="new">New</option><option value="read">Read</option><option value="in_progress">In progress</option><option value="resolved">Resolved</option><option value="spam">Spam</option></select></label></div></article><p v-if="!inquiries.data.length" class="rounded-xl border border-dashed p-10 text-center text-slate-500">Your inbox is clear.</p></div></div>
</template>
