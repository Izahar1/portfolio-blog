<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({ blog: Object, categories: Array, tags: Array, media: Array });
const currentUser = usePage().props.auth.user;
const form = useForm({
    title: props.blog?.title || '',
    slug: props.blog?.slug || '',
    excerpt: props.blog?.excerpt || '',
    content: props.blog?.content || '',
    category_id: props.blog?.category_id || '',
    featured_media_id: props.blog?.featured_media_id || '',
    tag_ids: props.blog?.tags?.map((tag) => tag.id) || [],
    status: props.blog?.status || 'draft',
    published_at: props.blog?.published_at ? new Date(props.blog.published_at).toISOString().slice(0, 16) : '',
    featured: props.blog?.featured || false,
});

function submit() {
    if (props.blog) form.put(route('admin.blogs.update', props.blog.id));
    else form.post(route('admin.blogs.store'));
}
</script>

<template>
    <Head :title="blog ? 'Edit post' : 'New post'" />
    <div class="mx-auto max-w-4xl">
        <Link :href="route('admin.blogs.index')" class="text-sm text-cyan-800">← Blog posts</Link>
        <h1 class="mt-3 text-3xl font-bold">{{ blog ? 'Edit post' : 'Create a post' }}</h1>
        <Link v-if="blog && ['admin', 'super_admin'].includes(currentUser.role)" :href="route('admin.seo.edit', { type: 'blogs', id: blog.id })" class="mt-3 inline-block text-sm text-cyan-800">Edit SEO metadata</Link>
        <form @submit.prevent="submit" class="mt-6 space-y-5 rounded-2xl bg-white p-6 shadow-sm sm:p-8">
            <div><label for="title" class="mb-1.5 block text-sm font-medium">Title</label><input id="title" v-model="form.title" required class="w-full rounded-lg border-slate-300"><p v-if="form.errors.title" class="mt-1 text-sm text-rose-700">{{ form.errors.title }}</p></div>
            <div><label for="slug" class="mb-1.5 block text-sm font-medium">Slug <span class="font-normal text-slate-500">(leave blank to use the title)</span></label><input id="slug" v-model="form.slug" class="w-full rounded-lg border-slate-300"><p v-if="form.errors.slug" class="mt-1 text-sm text-rose-700">{{ form.errors.slug }}</p></div>
            <div><label for="excerpt" class="mb-1.5 block text-sm font-medium">Excerpt</label><textarea id="excerpt" v-model="form.excerpt" rows="3" class="w-full rounded-lg border-slate-300"></textarea><p v-if="form.errors.excerpt" class="mt-1 text-sm text-rose-700">{{ form.errors.excerpt }}</p></div>
            <div><label for="content" class="mb-1.5 block text-sm font-medium">Article content</label><textarea id="content" v-model="form.content" required rows="16" class="w-full rounded-lg border-slate-300 font-mono text-sm"></textarea><p v-if="form.errors.content" class="mt-1 text-sm text-rose-700">{{ form.errors.content }}</p></div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div><label for="category" class="mb-1.5 block text-sm font-medium">Category</label><select id="category" v-model="form.category_id" class="w-full rounded-lg border-slate-300"><option value="">No category</option><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option></select></div>
                <div><label for="featured_media_id" class="mb-1.5 block text-sm font-medium">Featured image</label><select id="featured_media_id" v-model="form.featured_media_id" class="w-full rounded-lg border-slate-300"><option value="">No image</option><option v-for="item in media" :key="item.id" :value="item.id">{{ item.original_name }}</option></select><p v-if="form.errors.featured_media_id" class="mt-1 text-sm text-rose-700">{{ form.errors.featured_media_id }}</p></div>
                <div><label for="status" class="mb-1.5 block text-sm font-medium">Status</label><select id="status" v-model="form.status" class="w-full rounded-lg border-slate-300"><option value="draft">Draft</option><option value="in_review">In review</option><option value="scheduled">Scheduled</option><option value="published">Published</option><option value="archived">Archived</option></select><p v-if="form.errors.status" class="mt-1 text-sm text-rose-700">{{ form.errors.status }}</p></div>
                <div v-if="['scheduled', 'published'].includes(form.status)"><label for="published_at" class="mb-1.5 block text-sm font-medium">Publication time</label><input id="published_at" v-model="form.published_at" type="datetime-local" class="w-full rounded-lg border-slate-300"><p v-if="form.errors.published_at" class="mt-1 text-sm text-rose-700">{{ form.errors.published_at }}</p></div>
            </div>
            <fieldset><legend class="mb-2 text-sm font-medium">Tags</legend><div class="flex flex-wrap gap-3"><label v-for="tag in tags" :key="tag.id" class="flex items-center gap-2 text-sm"><input v-model="form.tag_ids" type="checkbox" :value="tag.id" class="rounded border-slate-300 text-cyan-700 focus:ring-cyan-600">{{ tag.name }}</label><span v-if="!tags.length" class="text-sm text-slate-500">No tags have been added yet.</span></div><p v-if="form.errors.tag_ids" class="mt-1 text-sm text-rose-700">{{ form.errors.tag_ids }}</p></fieldset>
            <label class="flex items-center gap-2 text-sm"><input v-model="form.featured" type="checkbox" class="rounded border-slate-300 text-cyan-700 focus:ring-cyan-600">Feature this post</label>
            <div class="flex gap-3 border-t pt-5"><button :disabled="form.processing" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white disabled:opacity-50">{{ form.processing ? 'Saving…' : 'Save post' }}</button><Link :href="route('admin.blogs.index')" class="rounded-lg border px-5 py-2.5 text-sm">Cancel</Link></div>
        </form>
    </div>
</template>
