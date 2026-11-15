<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });
defineProps({ profile: Object });
</script>

<template>
    <Head title="About" />
    <article class="mx-auto max-w-4xl"><p class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-300">A little about me</p><h1 class="mt-3 text-4xl font-bold">{{ profile?.full_name || 'The person behind the work' }}</h1><p v-if="profile?.headline" class="mt-3 text-xl text-slate-300">{{ profile.headline }}</p><p class="mt-8 whitespace-pre-wrap text-lg leading-8 text-slate-200">{{ profile?.bio || 'Profile details will appear here once they are added in the CMS.' }}</p><p v-if="profile?.location" class="mt-5 text-sm text-slate-400">Based in {{ profile.location }}</p><section v-if="profile?.experiences?.length" class="mt-14"><h2 class="text-2xl font-bold">Experience</h2><div class="mt-5 space-y-4"><article v-for="item in profile.experiences" :key="item.id" class="rounded-2xl border border-white/10 p-5"><h3 class="font-semibold">{{ item.job_title }} · {{ item.company }}</h3><p class="mt-1 text-sm text-slate-400">{{ item.start_date }} – {{ item.is_current ? 'Present' : item.end_date }}</p><p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-slate-300">{{ item.description }}</p></article></div></section><section v-if="profile?.educations?.length" class="mt-12"><h2 class="text-2xl font-bold">Education</h2><ul class="mt-5 space-y-3"><li v-for="item in profile.educations" :key="item.id" class="rounded-2xl border border-white/10 p-5"><p class="font-semibold">{{ item.qualification }}<span v-if="item.field_of_study">, {{ item.field_of_study }}</span></p><p class="mt-1 text-sm text-slate-400">{{ item.institution }}</p></li></ul></section></article>
</template>
