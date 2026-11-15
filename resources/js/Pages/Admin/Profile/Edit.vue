<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({ profile: Object, media: Array });
const form = useForm({ full_name: props.profile?.full_name || '', headline: props.profile?.headline || '', bio: props.profile?.bio || '', location: props.profile?.location || '', availability_status: props.profile?.availability_status || '', avatar_media_id: props.profile?.avatar_media_id || '', resume_media_id: props.profile?.resume_media_id || '' });

function submit() {
    form.patch(route('admin.profile.update'));
}
</script>

<template>
    <Head title="Profile" />
    <div class="mx-auto max-w-3xl"><p class="text-sm font-semibold uppercase tracking-widest text-cyan-700">Portfolio</p><h1 class="mt-2 text-3xl font-bold">Profile</h1><p class="mt-2 text-slate-600">These details appear on the home and about pages.</p><form @submit.prevent="submit" class="mt-6 space-y-5 rounded-2xl bg-white p-6 shadow-sm"><div><label for="full_name" class="mb-1 block text-sm font-medium">Name</label><input id="full_name" v-model="form.full_name" required class="w-full rounded-lg border-slate-300"><p v-if="form.errors.full_name" class="mt-1 text-sm text-rose-700">{{ form.errors.full_name }}</p></div><div><label for="headline" class="mb-1 block text-sm font-medium">Headline</label><input id="headline" v-model="form.headline" class="w-full rounded-lg border-slate-300"></div><div><label for="bio" class="mb-1 block text-sm font-medium">Biography</label><textarea id="bio" v-model="form.bio" rows="8" class="w-full rounded-lg border-slate-300"></textarea></div><div class="grid gap-5 sm:grid-cols-2"><div><label for="location" class="mb-1 block text-sm font-medium">Location</label><input id="location" v-model="form.location" class="w-full rounded-lg border-slate-300"></div><div><label for="availability" class="mb-1 block text-sm font-medium">Availability</label><input id="availability" v-model="form.availability_status" class="w-full rounded-lg border-slate-300"></div></div><div class="grid gap-5 sm:grid-cols-2"><div><label for="avatar" class="mb-1 block text-sm font-medium">Profile image</label><select id="avatar" v-model="form.avatar_media_id" class="w-full rounded-lg border-slate-300"><option value="">No image</option><option v-for="item in media.filter((entry) => entry.mime_type.startsWith('image/'))" :key="item.id" :value="item.id">{{ item.original_name }}</option></select></div><div><label for="resume" class="mb-1 block text-sm font-medium">Resume file</label><select id="resume" v-model="form.resume_media_id" class="w-full rounded-lg border-slate-300"><option value="">No resume</option><option v-for="item in media.filter((entry) => entry.mime_type === 'application/pdf')" :key="item.id" :value="item.id">{{ item.original_name }}</option></select></div></div><p class="text-sm text-slate-500">Upload files in the <Link :href="route('admin.media.index')" class="text-cyan-800 underline">media library</Link>.</p><button :disabled="form.processing" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white">Save profile</button></form></div>
</template>
