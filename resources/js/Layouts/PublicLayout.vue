<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const navigation = computed(() => page.props.menus?.header?.length ? page.props.menus.header : [
    { id: 'projects', label: 'Projects', href: route('projects.index'), target: '_self', children: [] },
    { id: 'services', label: 'Services', href: route('services.index'), target: '_self', children: [] },
    { id: 'blog', label: 'Blog', href: route('blog.index'), target: '_self', children: [] },
    { id: 'about', label: 'About', href: route('about'), target: '_self', children: [] },
]);
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <header class="border-b border-white/10">
            <nav class="mx-auto flex max-w-6xl items-center justify-between px-5 py-5" aria-label="Main navigation">
                <Link :href="route('home')" class="text-lg font-bold tracking-tight">{{ page.props.site?.site_name || 'Portfolio' }}<span class="text-cyan-400">.</span></Link>
                <div class="flex items-center gap-5 text-sm text-slate-300">
                    <template v-for="item in navigation" :key="item.id"><a v-if="item.href?.startsWith('http')" :href="item.href" :target="item.target" :rel="item.target === '_blank' ? 'noopener noreferrer' : undefined" class="hover:text-white">{{ item.label }}</a><Link v-else-if="item.href" :href="item.href" :target="item.target" :rel="item.target === '_blank' ? 'noopener noreferrer' : undefined" class="hover:text-white">{{ item.label }}</Link><template v-for="child in item.children" :key="child.id"><a v-if="child.href?.startsWith('http')" :href="child.href" :target="child.target" :rel="child.target === '_blank' ? 'noopener noreferrer' : undefined" class="text-xs text-slate-400 hover:text-white">{{ child.label }}</a><Link v-else-if="child.href" :href="child.href" :target="child.target" :rel="child.target === '_blank' ? 'noopener noreferrer' : undefined" class="text-xs text-slate-400 hover:text-white">{{ child.label }}</Link></template></template>
                    <Link :href="route('contact.index')" class="rounded-full bg-cyan-400 px-4 py-2 font-semibold text-slate-950 hover:bg-cyan-300">Contact</Link>
                    <Link v-if="page.props.auth.user" :href="route('admin.dashboard')" class="hover:text-white">Admin</Link>
                    <Link v-else-if="route().has('login')" :href="route('login')" class="hover:text-white">Log in</Link>
                </div>
            </nav>
        </header>
        <main class="mx-auto max-w-6xl px-5 py-12 sm:py-16">
            <div v-if="page.props.flash?.message" role="status" class="mb-8 rounded-xl border border-cyan-400/30 bg-cyan-400/10 px-4 py-3 text-cyan-100">
                {{ page.props.flash.message }}
            </div>
            <slot />
        </main>
        <footer class="border-t border-white/10">
            <div class="mx-auto flex max-w-6xl flex-col gap-3 px-5 py-8 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                <p>© {{ new Date().getFullYear() }} Portfolio. Built with care.</p>
                <div class="flex gap-4"><Link :href="route('blog.index')">Writing</Link><Link :href="route('contact.index')">Get in touch</Link></div>
            </div>
        </footer>
    </div>
</template>
