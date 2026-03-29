<script setup>
import TenantAppShell from '@/Layouts/TenantAppShell.vue';
import TenantSidebarSection from '@/Components/TenantSidebarSection.vue';

const cs = (slug) => `/coming-soon/${slug}`;

const mainItems = [
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Courses', href: '/courses' },
    { label: 'Lessons', href: '/lessons' },
    { label: 'Quizzes', href: '/quizzes' },
];

const usersItems = [
    { label: 'Students', href: '/students' },
    { label: 'Teachers', href: '/teachers' },
    { label: 'Classes', href: cs('classes') },
];

const learningItems = [
    { label: 'Assignments', href: cs('assignments') },
    { label: 'Attendance', href: cs('attendance') },
    { label: 'Certificates', href: '/certificates' },
];

const interactionItems = [
    { label: 'Live Sessions', href: '/live-sessions' },
    { label: 'Community', href: '/community' },
];

const analyticsItems = [
    { label: 'Analytics', href: '/analytics' },
    { label: 'Reports', href: cs('reports') },
];

const businessItems = [
    { label: 'Subscription', href: cs('subscription') },
    { label: 'Payments', href: cs('payments') },
    { label: 'Invoices', href: cs('invoices') },
];

const systemItems = [
    { label: 'Settings', href: '/settings' },
    { label: 'Branding', href: cs('branding') },
    { label: 'Domain', href: cs('domain') },
    { label: 'Roles & Permissions', href: cs('roles-permissions') },
];
</script>

<template>
    <TenantAppShell>
        <template #sidebar="{ showNavText }">
            <div v-if="showNavText" class="mx-3 mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] leading-snug text-amber-900">
                <span class="font-semibold">Billing:</span>
                review subscription and payment status in the next release.
            </div>

            <TenantSidebarSection title="Main" :items="mainItems" :show-nav-text="showNavText" first />
            <TenantSidebarSection title="Users" :items="usersItems" :show-nav-text="showNavText" />
            <TenantSidebarSection title="Learning" :items="learningItems" :show-nav-text="showNavText" />
            <TenantSidebarSection title="Interaction" :items="interactionItems" :show-nav-text="showNavText" />
            <TenantSidebarSection title="Analytics" :items="analyticsItems" :show-nav-text="showNavText" />
            <TenantSidebarSection title="Business" :items="businessItems" :show-nav-text="showNavText" />
            <TenantSidebarSection title="System" :items="systemItems" :show-nav-text="showNavText" />
        </template>

        <slot />
        <template v-if="$slots.aside" #aside>
            <slot name="aside" />
        </template>
    </TenantAppShell>
</template>
