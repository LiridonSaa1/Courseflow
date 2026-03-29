<script setup>
import TenantAppShell from '@/Layouts/TenantAppShell.vue';
import TenantSidebarSection from '@/Components/TenantSidebarSection.vue';

const cs = (slug) => `/coming-soon/${slug}`;

const mainItems = [
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'My Courses', href: '/courses' },
    { label: 'Continue learning', href: '/lessons' },
];

const learningItems = [
    { label: 'Lessons', href: '/lessons' },
    { label: 'Quizzes', href: '/quizzes' },
    { label: 'Assignments', href: cs('student-assignments') },
];

const progressItems = [
    { label: 'My Progress', href: '/progress' },
    { label: 'Results', href: cs('results') },
];

const extraItems = [
    { label: 'Certificates', href: '/certificates' },
    { label: 'Community', href: '/community' },
    { label: 'Live Classes', href: '/live-sessions' },
];
</script>

<template>
    <TenantAppShell>
        <template #sidebar="{ showNavText }">
            <div v-if="showNavText" class="mx-3 mt-3 rounded-lg border border-neutral-200 bg-white px-3 py-3 shadow-sm">
                <div class="flex items-center justify-between gap-2 text-[11px] font-semibold text-neutral-700">
                    <span>Continue lesson</span>
                    <span aria-hidden="true">🔥</span>
                </div>
                <p class="mt-1 text-[10px] text-neutral-500">Jump back into your latest lesson from the lessons hub.</p>
                <a
                    class="mt-2 inline-flex text-[11px] font-medium text-[#e66239]"
                    href="/lessons"
                >
                    Open lessons
                </a>
                <div class="mt-3">
                    <div class="mb-1 flex justify-between text-[10px] text-neutral-500">
                        <span>Progress</span>
                        <span>35%</span>
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-neutral-200">
                        <div class="h-full w-[35%] rounded-full bg-[#e66239]" />
                    </div>
                </div>
                <p class="mt-2 text-[10px] text-neutral-600">
                    <span class="font-semibold text-[#e66239]">3-day</span> streak — keep learning daily!
                </p>
            </div>

            <TenantSidebarSection title="Main" :items="mainItems" :show-nav-text="showNavText" first />
            <TenantSidebarSection title="Learning" :items="learningItems" :show-nav-text="showNavText" />
            <TenantSidebarSection title="Progress" :items="progressItems" :show-nav-text="showNavText" />
            <TenantSidebarSection title="Extra" :items="extraItems" :show-nav-text="showNavText" />
        </template>

        <slot />
        <template v-if="$slots.aside" #aside>
            <slot name="aside" />
        </template>
    </TenantAppShell>
</template>
