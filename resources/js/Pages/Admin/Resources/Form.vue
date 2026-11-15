<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({ resource: String, title: String, fields: Array, record: Object });
const values = Object.fromEntries(props.fields.map((field) => [field.name, props.record?.[field.name] ?? (field.type === 'checkbox' ? false : '')]));
const form = useForm(values);

function submit() {
    if (props.record) form.put(route('admin.resources.update', { resource: props.resource, id: props.record.id }));
    else form.post(route('admin.resources.store', { resource: props.resource }));
}
</script>

<template>
    <Head :title="record ? `Edit ${title.toLowerCase()}` : `New ${title.toLowerCase()}`" />
    <div class="mx-auto max-w-3xl"><Link :href="route('admin.resources.index', { resource })" class="text-sm text-cyan-800">← {{ title }}</Link><h1 class="mt-3 text-3xl font-bold">{{ record ? `Edit ${title.toLowerCase()}` : `Create ${title.toLowerCase()}` }}</h1><form @submit.prevent="submit" class="mt-6 space-y-5 rounded-2xl bg-white p-6 shadow-sm"><div v-for="field in fields" :key="field.name"><label :for="field.name" class="mb-1 block text-sm font-medium">{{ field.label }}</label><textarea v-if="field.type === 'textarea'" :id="field.name" v-model="form[field.name]" rows="6" class="w-full rounded-lg border-slate-300"></textarea><select v-else-if="field.type === 'select'" :id="field.name" v-model="form[field.name]" class="w-full rounded-lg border-slate-300"><option value="">Choose…</option><option v-for="(label, value) in field.options" :key="value" :value="value">{{ label }}</option></select><label v-else-if="field.type === 'checkbox'" class="flex items-center gap-2"><input :id="field.name" v-model="form[field.name]" type="checkbox" class="rounded border-slate-300"><span class="text-sm text-slate-600">{{ field.label }}</span></label><input v-else :id="field.name" v-model="form[field.name]" :type="field.type || 'text'" :min="field.type === 'number' ? 0 : undefined" class="w-full rounded-lg border-slate-300"><p v-if="form.errors[field.name]" class="mt-1 text-sm text-rose-700">{{ form.errors[field.name] }}</p></div><div class="flex gap-3 border-t pt-5"><button :disabled="form.processing" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white">{{ form.processing ? 'Saving…' : 'Save' }}</button><Link :href="route('admin.resources.index', { resource })" class="rounded-lg border px-5 py-2.5 text-sm">Cancel</Link></div></form></div>
</template>
