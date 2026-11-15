<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({ settings: Object });
const form = useForm({ ...props.settings });

function submit() {
    form.patch(route('admin.settings.update'));
}
</script>

<template>
    <Head title="Site settings" />
    <div class="mx-auto max-w-3xl"><p class="text-sm font-semibold uppercase tracking-widest text-cyan-700">System</p><h1 class="mt-2 text-3xl font-bold">Site settings</h1><p class="mt-2 text-sm text-slate-600">These values control the site name and default search metadata.</p><form @submit.prevent="submit" class="mt-6 space-y-5 rounded-2xl bg-white p-6 shadow-sm"><div><label for="site_name" class="mb-1 block text-sm font-medium">Site name</label><input id="site_name" v-model="form.site_name" required maxlength="120" class="w-full rounded-lg border-slate-300"><p v-if="form.errors.site_name" class="mt-1 text-sm text-rose-700">{{ form.errors.site_name }}</p></div><div><label for="seo_title" class="mb-1 block text-sm font-medium">Default SEO title</label><input id="seo_title" v-model="form.seo_title" maxlength="255" class="w-full rounded-lg border-slate-300"></div><div><label for="seo_description" class="mb-1 block text-sm font-medium">Default SEO description</label><textarea id="seo_description" v-model="form.seo_description" maxlength="320" rows="3" class="w-full rounded-lg border-slate-300"></textarea></div><div><label for="contact_email" class="mb-1 block text-sm font-medium">Contact email</label><input id="contact_email" v-model="form.contact_email" type="email" maxlength="255" class="w-full rounded-lg border-slate-300"></div><button :disabled="form.processing" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white">Save settings</button></form></div>
</template>
