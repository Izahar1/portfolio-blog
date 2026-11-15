<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
defineProps({ media: Object });
const form = useForm({ file: null, alt_text: '' });

function upload() {
    form.post(route('admin.media.store'), { forceFormData: true, onSuccess: () => form.reset() });
}

function remove(item) {
    if (window.confirm(`Delete ${item.original_name}?`)) router.delete(route('admin.media.destroy', item.id));
}
</script>

<template>
    <Head title="Media library" />
    <div><p class="text-sm font-semibold uppercase tracking-widest text-cyan-700">Content</p><h1 class="mt-2 text-3xl font-bold">Media library</h1><form @submit.prevent="upload" class="mt-6 grid gap-4 rounded-2xl bg-white p-5 shadow-sm sm:grid-cols-[1fr_1fr_auto] sm:items-end"><div><label for="file" class="mb-1 block text-sm font-medium">Image or PDF, up to 10 MB</label><input id="file" type="file" accept="image/jpeg,image/png,image/webp,application/pdf" required @input="form.file = $event.target.files[0]" class="w-full text-sm"></div><div><label for="alt_text" class="mb-1 block text-sm font-medium">Alt text (for images)</label><input id="alt_text" v-model="form.alt_text" maxlength="255" class="w-full rounded-lg border-slate-300"></div><button :disabled="form.processing" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Upload</button><p v-if="form.errors.file" class="text-sm text-rose-700 sm:col-span-3">{{ form.errors.file }}</p></form><div v-if="media.data.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"><article v-for="item in media.data" :key="item.id" class="overflow-hidden rounded-2xl bg-white shadow-sm"><a v-if="item.mime_type.startsWith('image/')" :href="item.url" target="_blank" rel="noopener noreferrer"><img :src="item.url" :alt="item.alt_text || item.original_name" class="aspect-video w-full object-cover"></a><div v-else class="flex aspect-video items-center justify-center bg-slate-100 text-sm font-semibold text-slate-500">PDF</div><div class="p-4"><p class="truncate text-sm font-medium" :title="item.original_name">{{ item.original_name }}</p><p class="mt-1 text-xs text-slate-500">{{ (item.size_bytes / 1024).toFixed(0) }} KB</p><button @click="remove(item)" class="mt-3 text-xs text-rose-700">Delete</button></div></article></div><p v-else class="mt-6 rounded-xl border border-dashed p-10 text-center text-slate-500">Your media library is empty.</p></div>
</template>
