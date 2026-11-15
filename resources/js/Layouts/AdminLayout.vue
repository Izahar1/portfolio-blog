<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const links = [
    { label: 'Overview', route: 'admin.dashboard' },
    { label: 'Blog posts', route: 'admin.blogs.index' },
    { label: 'Categories', route: 'admin.taxonomies.index', params: { type: 'categories' } },
    { label: 'Tags', route: 'admin.taxonomies.index', params: { type: 'tags' } },
    { label: 'Projects', route: 'admin.projects.index' },
    { label: 'Pages', route: 'admin.pages.index' },
    { label: 'Profile', route: 'admin.profile.edit' },
    { label: 'Media library', route: 'admin.media.index' },
    { label: 'Inquiries', route: 'admin.inquiries.index' },
    { label: 'Project categories', route: 'admin.resources.index', params: { resource: 'project-categories' } },
    { label: 'Skills', route: 'admin.resources.index', params: { resource: 'skills' } },
    { label: 'Services', route: 'admin.resources.index', params: { resource: 'services' } },
    { label: 'Experience', route: 'admin.resources.index', params: { resource: 'experiences' } },
    { label: 'Education', route: 'admin.resources.index', params: { resource: 'educations' } },
    { label: 'Testimonials', route: 'admin.resources.index', params: { resource: 'testimonials' } },
    { label: 'Comments', route: 'admin.resources.index', params: { resource: 'comments' } },
    { label: 'Menus', route: 'admin.resources.index', params: { resource: 'menus' } },
    { label: 'Menu items', route: 'admin.resources.index', params: { resource: 'menu-items' } },
    { label: 'Social links', route: 'admin.resources.index', params: { resource: 'social-links' } },
    { label: 'Redirects', route: 'admin.resources.index', params: { resource: 'redirects' } },
];
if (page.props.auth.user?.role === 'super_admin') {
    links.push({ label: 'Users', route: 'admin.users.index' }, { label: 'Settings', route: 'admin.settings.edit' });
}
</script>

<template>
    <div class="min-h-screen bg-slate-100 text-slate-900 md:flex">
        <aside class="bg-slate-950 p-5 text-white md:min-h-screen md:w-64 md:shrink-0">
            <Link :href="route('home')" class="text-lg font-bold">Portfolio CMS</Link>
            <nav class="mt-8 flex gap-2 overflow-x-auto md:flex-col" aria-label="Admin navigation">
                <Link v-for="item in links" :key="item.label" :href="route(item.route, item.params || {})" class="whitespace-nowrap rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-white/10 hover:text-white">{{ item.label }}</Link>
            </nav>
            <div class="mt-8 hidden text-xs text-slate-400 md:block">Signed in as {{ page.props.auth.user?.name }}</div>
            <Link :href="route('logout')" method="post" as="button" class="mt-6 rounded-lg border border-white/20 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">Log out</Link>
        </aside>
        <main class="min-w-0 flex-1 p-5 sm:p-8">
            <div v-if="page.props.flash?.message" role="status" class="mb-6 rounded-lg bg-emerald-100 px-4 py-3 text-emerald-900">{{ page.props.flash.message }}</div>
            <slot />
        </main>
    </div>
</template>
