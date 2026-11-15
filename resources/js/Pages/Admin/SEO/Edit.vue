<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({ type: String, record: Object, seo: Object });
const form = useForm({ meta_title: props.seo.meta_title || '', meta_description: props.seo.meta_description || '', canonical_url: props.seo.canonical_url || '', noindex: props.seo.noindex || false });

function submit() {
    form.patch(route('admin.seo.update', { type: props.type, id: props.record.id }));
}
</script>

<template>
    <Head title="SEO metadata" />
    <div class="mx-auto max-w-3xl"><Link :href="route(`admin.${type}.index`)" class="text-sm text-cyan-800">← Back to {{ type }}</Link><p class="mt-4 text-sm font-semibold uppercase tracking-widest text-cyan-700">Search appearance</p><h1 class="mt-2 text-3xl font-bold">{{ record.title }}</h1><form @submit.prevent="submit" class="mt-6 space-y-5 rounded-2xl bg-white p-6 shadow-sm"><div><label for="meta_title" class="mb-1 block text-sm font-medium">Meta title</label><input id="meta_title" v-model="form.meta_title" maxlength="255" class="w-full rounded-lg border-slate-300"><p v-if="form.errors.meta_title" class="mt-1 text-sm text-rose-700">{{ form.errors.meta_title }}</p></div><div><label for="meta_description" class="mb-1 block text-sm font-medium">Meta description</label><textarea id="meta_description" v-model="form.meta_description" maxlength="320" rows="4" class="w-full rounded-lg border-slate-300"></textarea><p v-if="form.errors.meta_description" class="mt-1 text-sm text-rose-700">{{ form.errors.meta_description }}</p></div><div><label for="canonical_url" class="mb-1 block text-sm font-medium">Canonical URL</label><input id="canonical_url" v-model="form.canonical_url" type="url" class="w-full rounded-lg border-slate-300"><p v-if="form.errors.canonical_url" class="mt-1 text-sm text-rose-700">{{ form.errors.canonical_url }}</p></div><label class="flex items-center gap-2 text-sm"><input v-model="form.noindex" type="checkbox" class="rounded border-slate-300">Hide from search engines and sitemap</label><button :disabled="form.processing" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white">Save metadata</button></form></div>
</template>
