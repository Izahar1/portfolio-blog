<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });
const form = useForm({ name: '', email: '', subject: '', message: '', website: '' });

function submit() {
    form.post(route('contact.store'), { onSuccess: () => form.reset() });
}
</script>

<template>
    <Head title="Contact" />
    <section class="mx-auto grid max-w-5xl gap-12 md:grid-cols-[0.8fr_1.2fr]"><div><p class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-300">Contact</p><h1 class="mt-3 text-4xl font-bold">Let’s build something useful.</h1><p class="mt-5 leading-7 text-slate-300">Share a little about your project or question. I’ll get back to you as soon as I can.</p></div><form @submit.prevent="submit" class="space-y-5 rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8"><div><label for="name" class="mb-2 block text-sm font-medium">Name</label><input id="name" v-model="form.name" required maxlength="120" autocomplete="name" class="w-full rounded-xl border-white/15 bg-slate-900 text-white focus:border-cyan-300 focus:ring-cyan-300"><p v-if="form.errors.name" class="mt-1 text-sm text-rose-300">{{ form.errors.name }}</p></div><div><label for="email" class="mb-2 block text-sm font-medium">Email</label><input id="email" v-model="form.email" required type="email" maxlength="255" autocomplete="email" class="w-full rounded-xl border-white/15 bg-slate-900 text-white focus:border-cyan-300 focus:ring-cyan-300"><p v-if="form.errors.email" class="mt-1 text-sm text-rose-300">{{ form.errors.email }}</p></div><div><label for="subject" class="mb-2 block text-sm font-medium">Subject</label><input id="subject" v-model="form.subject" maxlength="180" class="w-full rounded-xl border-white/15 bg-slate-900 text-white focus:border-cyan-300 focus:ring-cyan-300"></div><div><label for="message" class="mb-2 block text-sm font-medium">Message</label><textarea id="message" v-model="form.message" required minlength="10" maxlength="10000" rows="6" class="w-full rounded-xl border-white/15 bg-slate-900 text-white focus:border-cyan-300 focus:ring-cyan-300"></textarea><p v-if="form.errors.message" class="mt-1 text-sm text-rose-300">{{ form.errors.message }}</p></div><div class="absolute -left-[10000px]" aria-hidden="true"><label for="website">Website</label><input id="website" v-model="form.website" tabindex="-1" autocomplete="off"></div><button :disabled="form.processing" class="rounded-full bg-cyan-400 px-6 py-3 font-semibold text-slate-950 disabled:opacity-50">{{ form.processing ? 'Sending…' : 'Send message' }}</button></form></section>
</template>
