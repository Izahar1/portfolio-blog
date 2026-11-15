<script setup>
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });
defineProps({ users: Object });

function saveAccess(user, event) {
    const [role, active] = event.target.value.split('|');
    router.patch(route('admin.users.update', user.id), { role, is_active: active === 'active' }, { preserveScroll: true });
}
</script>

<template>
    <Head title="Users and roles" />
    <div><p class="text-sm font-semibold uppercase tracking-widest text-cyan-700">System</p><h1 class="mt-2 text-3xl font-bold">Users &amp; roles</h1><p class="mt-2 text-sm text-slate-600">Only super admins can change access. The last active super admin is protected.</p><div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm"><div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-5 py-3 font-medium">User</th><th class="px-5 py-3 font-medium">Email</th><th class="px-5 py-3 font-medium">Role and status</th></tr></thead><tbody class="divide-y"><tr v-for="user in users.data" :key="user.id"><td class="px-5 py-4 font-medium">{{ user.name }}</td><td class="px-5 py-4 text-slate-600">{{ user.email }}</td><td class="px-5 py-4"><label :for="`access-${user.id}`" class="sr-only">Access for {{ user.name }}</label><select :id="`access-${user.id}`" :value="`${user.role}|${user.is_active ? 'active' : 'inactive'}`" @change="saveAccess(user, $event)" class="rounded-lg border-slate-300 text-sm"><option value="super_admin|active">Super admin · active</option><option value="admin|active">Admin · active</option><option value="editor|active">Editor · active</option><option value="super_admin|inactive">Super admin · inactive</option><option value="admin|inactive">Admin · inactive</option><option value="editor|inactive">Editor · inactive</option></select></td></tr></tbody></table></div></div></div>
</template>
