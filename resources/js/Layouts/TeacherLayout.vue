<script setup>
import TenantAppShell from '@/Layouts/TenantAppShell.vue';
import TenantSidebarSection from '@/Components/TenantSidebarSection.vue';

const cs = (slug) => `/coming-soon/${slug}`;

const mainItems = [
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'My Courses', href: '/courses' },
    { label: 'Lessons', href: '/lessons' },
    { label: 'Quizzes', href: '/quizzes' },
];

const studentsItems = [
    { label: 'My Students', href: '/students' },
    { label: 'Classes', href: cs('classes') },
];

const learningItems = [
    { label: 'Assignments', href: cs('assignments'), badge: '3' },
    { label: 'Attendance', href: cs('attendance') },
    { label: 'Certificates', href: '/certificates' },
];

const interactionItems = [
    { label: 'Live Sessions', href: '/live-sessions' },
    { label: 'Community', href: '/community' },
];

const analyticsItems = [
    { label: 'Student Progress', href: '/analytics' },
    { label: 'Quiz Results', href: cs('quiz-results') },
];
</script>

<template>
    <TenantAppShell>
        <template #sidebar="{ showNavText }">
            <div v-if="showNavText" class="mx-3 mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] leading-snug text-amber-900">
                <span class="font-semibold">Students:</span>
                check learners who may need extra support (insights coming soon).
            </div>

            <TenantSidebarSection title="Main" :items="mainItems" :show-nav-text="showNavText" first />
            <TenantSidebarSection title="Students" :items="studentsItems" :show-nav-text="showNavText" />
            <TenantSidebarSection title="Learning" :items="learningItems" :show-nav-text="showNavText" />
            <TenantSidebarSection title="Interaction" :items="interactionItems" :show-nav-text="showNavText" />
            <TenantSidebarSection title="Analytics" :items="analyticsItems" :show-nav-text="showNavText" />
        </template>

        <slot />
        <template v-if="$slots.aside" #aside>
            <slot name="aside" />
        </template>
    </TenantAppShell>
</template>
