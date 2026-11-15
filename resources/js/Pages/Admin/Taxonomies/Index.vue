<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({ type: String, items: Object });
const form = useForm({ name: '', slug: '', description: '' });
const editingId = ref(null);
const editForm = useForm({ name: '', slug: '', description: '' });
const title = props.type === 'categories' ? 'Categories' : 'Tags';

function create() {
    form.post(route('admin.taxonomies.store', { type: props.type }), { onSuccess: () => form.reset() });
}

function edit(item) {
    editingId.value = item.id;
    editForm.name = item.name;
    editForm.slug = item.slug;
    editForm.description = item.description || '';
}

function update() {
    editForm.put(route('admin.taxonomies.update', { type: props.type, id: editingId.value }), { onSuccess: () => { editingId.value = null; } });
}

function remove(item) {
    if (window.confirm(`Delete “${item.name}”?`)) router.delete(route('admin.taxonomies.destroy', { type: props.type, id: item.id }));
}
</script>

<template>
    <Head :title="title" />
    <div class="mx-auto max-w-5xl"><Link :href="route('admin.blogs.index')" class="text-sm text-cyan-800">← Blog posts</Link><div class="mt-3 flex items-end justify-between"><div><p class="text-sm font-semibold uppercase tracking-widest text-cyan-700">Content</p><h1 class="mt-2 text-3xl font-bold">{{ title }}</h1></div></div><div class="mt-6 grid gap-6 lg:grid-cols-[0.8fr_1.2fr]"><form @submit.prevent="create" class="h-fit space-y-4 rounded-2xl bg-white p-5 shadow-sm"><h2 class="font-semibold">Add {{ type === 'categories' ? 'a category' : 'a tag' }}</h2><div><label for="name" class="mb-1 block text-sm">Name</label><input id="name" v-model="form.name" required class="w-full rounded-lg border-slate-300"><p v-if="form.errors.name" class="mt-1 text-sm text-rose-700">{{ form.errors.name }}</p></div><div><label for="slug" class="mb-1 block text-sm">Slug (optional)</label><input id="slug" v-model="form.slug" class="w-full rounded-lg border-slate-300"></div><div v-if="type === 'categories'"><label for="description" class="mb-1 block text-sm">Description</label><textarea id="description" v-model="form.description" rows="3" class="w-full rounded-lg border-slate-300"></textarea></div><button :disabled="form.processing" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Add</button></form><div class="space-y-3"><article v-for="item in items.data" :key="item.id" class="rounded-2xl bg-white p-5 shadow-sm"><form v-if="editingId === item.id" @submit.prevent="update" class="space-y-3"><input v-model="editForm.name" required class="w-full rounded-lg border-slate-300"><input v-model="editForm.slug" class="w-full rounded-lg border-slate-300"><textarea v-if="type === 'categories'" v-model="editForm.description" rows="2" class="w-full rounded-lg border-slate-300"></textarea><div class="flex gap-3"><button class="text-sm font-semibold text-cyan-800">Save</button><button type="button" @click="editingId = null" class="text-sm text-slate-500">Cancel</button></div></form><div v-else class="flex items-center justify-between gap-4"><div><p class="font-medium">{{ item.name }}</p><p class="text-sm text-slate-500">/{{ item.slug }}</p></div><div class="flex gap-3 text-sm"><button @click="edit(item)" class="text-cyan-800">Edit</button><button @click="remove(item)" class="text-rose-700">Delete</button></div></div></article><p v-if="!items.data.length" class="rounded-xl border border-dashed p-8 text-center text-slate-500">No {{ type }} yet.</p></div></div></div>
</template>
