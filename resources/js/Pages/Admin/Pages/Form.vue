<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({ page: Object });
const currentUser = usePage().props.auth.user;
const allowedTypes = ['hero', 'about', 'skills', 'featured_projects', 'services', 'testimonials', 'latest_blogs', 'contact_cta', 'rich_text'];
const form = useForm({
    title: props.page?.title || '',
    slug: props.page?.slug || '',
    status: props.page?.status || 'draft',
    template: props.page?.template || 'default',
    sections: props.page?.sections?.map((section) => ({
        type: section.type,
        heading: section.heading || '',
        content_json: section.content_json || {},
        is_enabled: section.is_enabled,
    })) || [],
});
const jsonError = ref('');

function defaultContent(type) {
    if (type === 'hero') return { title: '', subtitle: '', cta_label: '', cta_url: '' };
    if (type === 'about' || type === 'contact_cta') return { body: '' };
    if (type === 'featured_projects' || type === 'latest_blogs') return { limit: 3 };
    if (type === 'rich_text') return { content: '' };
    return {};
}

function addSection() {
    form.sections.push({ type: 'hero', heading: '', content_json: defaultContent('hero'), is_enabled: true });
}

function move(index, direction) {
    const target = index + direction;
    if (target < 0 || target >= form.sections.length) return;
    const [section] = form.sections.splice(index, 1);
    form.sections.splice(target, 0, section);
}

function updateSectionJson(index, event) {
    try {
        form.sections[index].content_json = JSON.parse(event.target.value);
        jsonError.value = '';
    } catch {
        jsonError.value = `Section ${index + 1} contains invalid JSON.`;
    }
}

function submit() {
    jsonError.value = '';
    if (props.page) form.put(route('admin.pages.update', props.page.id));
    else form.post(route('admin.pages.store'));
}
</script>

<template>
    <Head :title="page ? 'Edit page' : 'New page'" />
    <div class="mx-auto max-w-4xl">
        <Link :href="route('admin.pages.index')" class="text-sm text-cyan-800">← Pages</Link>
        <h1 class="mt-3 text-3xl font-bold">{{ page ? 'Edit page' : 'Create a page' }}</h1>
        <Link v-if="page && ['admin', 'super_admin'].includes(currentUser.role)" :href="route('admin.seo.edit', { type: 'pages', id: page.id })" class="mt-3 inline-block text-sm text-cyan-800">Edit SEO metadata</Link>
        <form @submit.prevent="submit" class="mt-6 space-y-5 rounded-2xl bg-white p-6 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label for="title" class="mb-1 block text-sm font-medium">Title</label><input id="title" v-model="form.title" required class="w-full rounded-lg border-slate-300"><p v-if="form.errors.title" class="mt-1 text-sm text-rose-700">{{ form.errors.title }}</p></div>
                <div><label for="slug" class="mb-1 block text-sm font-medium">Slug (optional)</label><input id="slug" v-model="form.slug" class="w-full rounded-lg border-slate-300"><p v-if="form.errors.slug" class="mt-1 text-sm text-rose-700">{{ form.errors.slug }}</p></div>
                <div><label for="status" class="mb-1 block text-sm font-medium">Status</label><select id="status" v-model="form.status" class="w-full rounded-lg border-slate-300"><option value="draft">Draft</option><option value="published">Published</option></select></div>
                <div><label for="template" class="mb-1 block text-sm font-medium">Template</label><input id="template" v-model="form.template" class="w-full rounded-lg border-slate-300"></div>
            </div>

            <section class="border-t pt-5">
                <div class="flex items-center justify-between"><div><h2 class="font-semibold">Page sections</h2><p class="mt-1 text-xs text-slate-500">Content fields use JSON. Rich text is displayed as escaped text.</p></div><button type="button" @click="addSection" class="rounded-lg border px-3 py-2 text-sm">Add section</button></div>
                <p v-if="jsonError" class="mt-3 text-sm text-rose-700">{{ jsonError }}</p>
                <p v-if="form.errors.sections" class="mt-3 text-sm text-rose-700">{{ form.errors.sections }}</p>
                <div class="mt-4 space-y-4">
                    <article v-for="(section, index) in form.sections" :key="index" class="rounded-xl border border-slate-200 p-4">
                        <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
                            <div><label :for="`section-type-${index}`" class="mb-1 block text-xs font-medium">Type</label><select :id="`section-type-${index}`" v-model="section.type" @change="section.content_json = defaultContent(section.type)" class="w-full rounded-lg border-slate-300 text-sm"><option v-for="type in allowedTypes" :key="type" :value="type">{{ type.replaceAll('_', ' ') }}</option></select></div>
                            <div><label :for="`section-heading-${index}`" class="mb-1 block text-xs font-medium">Heading</label><input :id="`section-heading-${index}`" v-model="section.heading" class="w-full rounded-lg border-slate-300 text-sm"></div>
                            <div class="flex items-end gap-2"><button type="button" @click="move(index, -1)" :disabled="index === 0" aria-label="Move section up" class="rounded border px-3 py-2 disabled:opacity-40">↑</button><button type="button" @click="move(index, 1)" :disabled="index === form.sections.length - 1" aria-label="Move section down" class="rounded border px-3 py-2 disabled:opacity-40">↓</button><button type="button" @click="form.sections.splice(index, 1)" class="rounded border border-rose-200 px-3 py-2 text-rose-700">Remove</button></div>
                        </div>
                        <label class="mt-3 flex items-center gap-2 text-xs"><input v-model="section.is_enabled" type="checkbox" class="rounded">Enabled on public page</label>
                        <label :for="`section-content-${index}`" class="mb-1 mt-3 block text-xs font-medium">Content JSON</label>
                        <textarea :id="`section-content-${index}`" :value="JSON.stringify(section.content_json, null, 2)" rows="5" spellcheck="false" class="w-full rounded-lg border-slate-300 font-mono text-xs" @change="updateSectionJson(index, $event)"></textarea>
                        <p v-for="(error, key) in form.errors" :key="key" class="mt-1 text-xs text-rose-700">{{ key.startsWith(`sections.${index}.`) ? error : '' }}</p>
                    </article>
                    <p v-if="!form.sections.length" class="rounded-lg border border-dashed p-6 text-center text-sm text-slate-500">Add a section to build the page.</p>
                </div>
            </section>
            <div class="flex gap-3 border-t pt-5"><button :disabled="form.processing || !!jsonError" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white disabled:opacity-50">{{ form.processing ? 'Saving…' : 'Save page' }}</button><Link :href="route('admin.pages.index')" class="rounded-lg border px-5 py-2.5 text-sm">Cancel</Link></div>
        </form>
    </div>
</template>
