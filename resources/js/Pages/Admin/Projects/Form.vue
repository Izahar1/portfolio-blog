<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({ project: Object, media: Array, categories: Array, skills: Array });
const currentUser = usePage().props.auth.user;
const form = useForm({
    title: props.project?.title || '',
    slug: props.project?.slug || '',
    summary: props.project?.summary || '',
    description: props.project?.description || '',
    thumbnail_media_id: props.project?.thumbnail_media_id || '',
    demo_url: props.project?.demo_url || '',
    repository_url: props.project?.repository_url || '',
    status: props.project?.status || 'draft',
    featured: props.project?.featured || false,
    sort_order: props.project?.sort_order || 0,
    published_at: props.project?.published_at ? new Date(props.project.published_at).toISOString().slice(0, 16) : '',
    category_ids: props.project?.categories?.map((item) => item.id) || [],
    skill_ids: props.project?.skills?.map((item) => item.id) || [],
    gallery_media_ids: props.project?.images?.map((item) => item.media_id) || [],
});

function submit() {
    if (props.project) form.put(route('admin.projects.update', props.project.id));
    else form.post(route('admin.projects.store'));
}
</script>

<template>
    <Head :title="project ? 'Edit project' : 'New project'" />
    <div class="mx-auto max-w-3xl">
        <Link :href="route('admin.projects.index')" class="text-sm text-cyan-800">← Projects</Link>
        <h1 class="mt-3 text-3xl font-bold">{{ project ? 'Edit project' : 'Create a project' }}</h1>
        <Link v-if="project && ['admin', 'super_admin'].includes(currentUser.role)" :href="route('admin.seo.edit', { type: 'projects', id: project.id })" class="mt-3 inline-block text-sm text-cyan-800">Edit SEO metadata</Link>
        <form @submit.prevent="submit" class="mt-6 space-y-5 rounded-2xl bg-white p-6 shadow-sm">
            <div><label for="title" class="mb-1.5 block text-sm font-medium">Title</label><input id="title" v-model="form.title" required class="w-full rounded-lg border-slate-300"><p v-if="form.errors.title" class="mt-1 text-sm text-rose-700">{{ form.errors.title }}</p></div>
            <div><label for="slug" class="mb-1.5 block text-sm font-medium">Slug <span class="font-normal text-slate-500">(leave blank to use the title)</span></label><input id="slug" v-model="form.slug" class="w-full rounded-lg border-slate-300"><p v-if="form.errors.slug" class="mt-1 text-sm text-rose-700">{{ form.errors.slug }}</p></div>
            <div><label for="summary" class="mb-1.5 block text-sm font-medium">Summary</label><textarea id="summary" v-model="form.summary" rows="3" class="w-full rounded-lg border-slate-300"></textarea></div>
            <div><label for="description" class="mb-1.5 block text-sm font-medium">Description</label><textarea id="description" v-model="form.description" rows="10" class="w-full rounded-lg border-slate-300"></textarea></div>
            <div><label for="thumbnail_media_id" class="mb-1.5 block text-sm font-medium">Project image</label><select id="thumbnail_media_id" v-model="form.thumbnail_media_id" class="w-full rounded-lg border-slate-300"><option value="">No image</option><option v-for="item in media" :key="item.id" :value="item.id">{{ item.original_name }}</option></select><p v-if="form.errors.thumbnail_media_id" class="mt-1 text-sm text-rose-700">{{ form.errors.thumbnail_media_id }}</p></div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div><label for="category_ids" class="mb-1.5 block text-sm font-medium">Categories</label><select id="category_ids" v-model="form.category_ids" multiple class="min-h-32 w-full rounded-lg border-slate-300"><option v-for="item in categories" :key="item.id" :value="item.id">{{ item.name }}</option></select><p class="mt-1 text-xs text-slate-500">Use Ctrl or Command to select several.</p></div>
                <div><label for="skill_ids" class="mb-1.5 block text-sm font-medium">Technologies and skills</label><select id="skill_ids" v-model="form.skill_ids" multiple class="min-h-32 w-full rounded-lg border-slate-300"><option v-for="item in skills" :key="item.id" :value="item.id">{{ item.name }}</option></select><p class="mt-1 text-xs text-slate-500">Use Ctrl or Command to select several.</p></div>
            </div>
            <div><label for="gallery_media_ids" class="mb-1.5 block text-sm font-medium">Gallery images</label><select id="gallery_media_ids" v-model="form.gallery_media_ids" multiple class="min-h-32 w-full rounded-lg border-slate-300"><option v-for="item in media" :key="item.id" :value="item.id">{{ item.original_name }}</option></select><p class="mt-1 text-xs text-slate-500">Select images in the order you want them displayed.</p></div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div><label for="demo_url" class="mb-1.5 block text-sm font-medium">Demo URL</label><input id="demo_url" v-model="form.demo_url" type="url" class="w-full rounded-lg border-slate-300"><p v-if="form.errors.demo_url" class="mt-1 text-sm text-rose-700">{{ form.errors.demo_url }}</p></div>
                <div><label for="repository_url" class="mb-1.5 block text-sm font-medium">Repository URL</label><input id="repository_url" v-model="form.repository_url" type="url" class="w-full rounded-lg border-slate-300"><p v-if="form.errors.repository_url" class="mt-1 text-sm text-rose-700">{{ form.errors.repository_url }}</p></div>
                <div><label for="status" class="mb-1.5 block text-sm font-medium">Status</label><select id="status" v-model="form.status" class="w-full rounded-lg border-slate-300"><option value="draft">Draft</option><option value="published">Published</option><option value="archived">Archived</option></select><p v-if="form.errors.status" class="mt-1 text-sm text-rose-700">{{ form.errors.status }}</p></div>
                <div><label for="sort_order" class="mb-1.5 block text-sm font-medium">Display order</label><input id="sort_order" v-model="form.sort_order" type="number" min="0" class="w-full rounded-lg border-slate-300"></div>
            </div>
            <label class="flex items-center gap-2 text-sm"><input v-model="form.featured" type="checkbox" class="rounded border-slate-300">Feature this project</label>
            <div class="flex gap-3 border-t pt-5"><button :disabled="form.processing" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white disabled:opacity-50">{{ form.processing ? 'Saving…' : 'Save project' }}</button><Link :href="route('admin.projects.index')" class="rounded-lg border px-5 py-2.5 text-sm">Cancel</Link></div>
        </form>
    </div>
</template>
