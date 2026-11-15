<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });
defineProps({ services: Array });
</script>

<template>
    <Head title="Services" />
    <section><p class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-300">How I can help</p><h1 class="mt-3 text-4xl font-bold">Services</h1><div v-if="services.length" class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3"><article v-for="service in services" :key="service.id" class="rounded-2xl border border-white/10 bg-white/[0.03] p-6"><h2 class="text-xl font-semibold">{{ service.title }}</h2><p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-slate-300">{{ service.description }}</p></article></div><p v-else class="mt-10 rounded-2xl border border-dashed border-white/15 p-10 text-slate-400">Services will appear here once published.</p></section>
</template>
