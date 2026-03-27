<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    roleNames: { type: Array, default: () => [] },
    courses: { type: Array, default: () => [] },
    progress: { type: Array, default: () => [] },
    recentAttempts: { type: Array, default: () => [] },
    stat: { type: Object, default: null },
    chartActivity: { type: Array, default: () => [0, 0, 0, 0, 0, 0, 0] },
});

const palette = ['bg-blue-500', 'bg-violet-500', 'bg-orange-500', 'bg-rose-500', 'bg-emerald-500', 'bg-amber-500'];

function rowIconClass(i) {
    return palette[i % palette.length];
}

const dateSubtitle = computed(() => {
    const now = new Date();
    const end = now.getDate();
    const month = now.toLocaleString('en', { month: 'long' });
    const year = now.getFullYear();

    return `01 – ${end} ${month}, ${year}`;
});

const barMeta = computed(() => {
    const raw = props.chartActivity.length ? props.chartActivity : [0, 0, 0, 0, 0, 0, 0];
    const max = Math.max(...raw, 1);
    const highlightIdx = raw.indexOf(Math.max(...raw));

    return raw.map((n, idx) => ({
        h: 28 + Math.round((n / max) * 72),
        strong: idx === highlightIdx && n > 0,
    }));
});

const avgProgressPercent = computed(() => {
    const rows = props.progress || [];
    if (!rows.length) {
        return 0;
    }
    const sum = rows.reduce((acc, p) => acc + (Number(p.percent) || 0), 0);

    return Math.round(sum / rows.length);
});

const canManageCourses = computed(
    () => props.roleNames.includes('owner') || props.roleNames.includes('teacher'),
);
</script>

<template>
    <TenantLayout>
        <Head title="Dashboard" />

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-neutral-900 sm:text-3xl">Dashboard</h1>
                <p class="mt-1 text-sm text-neutral-600">{{ dateSubtitle }}</p>
                <p class="mt-0.5 text-xs text-neutral-500">{{ roleNames.join(', ') }}</p>
            </div>
            <div class="flex -space-x-2">
                <div
                    v-for="i in 4"
                    :key="i"
                    class="flex size-9 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-indigo-400 to-violet-600 text-[10px] font-semibold text-white"
                >
                    {{ String.fromCharCode(64 + i) }}
                </div>
                <Link
                    v-if="canManageCourses"
                    href="/courses/create"
                    class="flex size-9 items-center justify-center rounded-full border-2 border-dashed border-neutral-200 bg-white text-lg leading-none text-neutral-400 hover:border-indigo-300 hover:text-indigo-600"
                    aria-label="New course"
                >
                    +
                </Link>
            </div>
        </div>

        <div class="mt-8 flex h-32 items-end justify-between gap-1 sm:gap-1.5" aria-hidden="true">
            <div
                v-for="(bar, idx) in barMeta"
                :key="idx"
                class="flex-1 rounded-t-md transition-all"
                :class="bar.strong ? 'bg-blue-500' : 'bg-blue-200/80'"
                :style="{ height: bar.h + 'px' }"
            />
        </div>

        <div v-if="stat" class="mt-8 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-neutral-200 bg-neutral-50/80 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-neutral-500">XP</p>
                <p class="mt-1 text-xl font-semibold text-indigo-600">{{ stat.xp }}</p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-neutral-50/80 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-neutral-500">Level</p>
                <p class="mt-1 text-xl font-semibold text-neutral-900">{{ stat.level }}</p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-neutral-50/80 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-neutral-500">Streak (days)</p>
                <p class="mt-1 text-xl font-semibold text-emerald-600">{{ stat.streak_days }}</p>
            </div>
        </div>

        <section id="courses" class="mt-10 scroll-mt-6">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-neutral-900">Courses</h2>
                <button type="button" class="rounded-md p-1 text-neutral-400 hover:bg-neutral-100" aria-label="More">⋯</button>
            </div>
            <ul class="mt-4 divide-y divide-neutral-100 border-t border-neutral-100">
                <li v-for="(c, i) in courses" :key="c.id" class="flex items-center gap-4 py-4">
                    <div
                        class="flex size-11 shrink-0 items-center justify-center rounded-full text-white shadow-sm"
                        :class="rowIconClass(i)"
                    >
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                            />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <Link :href="`/courses/${c.id}`" class="font-semibold text-neutral-900 hover:text-indigo-600">
                            {{ c.title }}
                        </Link>
                        <p class="mt-0.5 text-sm text-neutral-600">
                            {{ c.language }} · {{ c.level }}
                        </p>
                    </div>
                    <span class="shrink-0 text-sm font-medium text-neutral-700">{{ c.modules_count }} modules</span>
                </li>
                <li v-if="!courses.length" class="py-10 text-center text-sm text-neutral-500">No courses yet.</li>
            </ul>
        </section>

        <section v-if="recentAttempts.length" class="mt-10">
            <h2 class="text-base font-bold text-neutral-900">Recent quiz attempts</h2>
            <ul class="mt-3 space-y-2 text-sm text-neutral-600">
                <li v-for="a in recentAttempts" :key="a.id" class="rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-2">
                    {{ a.score }} / {{ a.max_score }} · {{ a.status }}
                </li>
            </ul>
        </section>

        <template #aside>
            <div>
                <h2 class="text-base font-bold text-neutral-900">Learning progress</h2>
                <p class="mt-1 text-xs text-neutral-500">Based on your lesson progress records.</p>

                <div class="mt-6">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-neutral-800">Overall</span>
                        <span class="font-semibold text-neutral-900">{{ avgProgressPercent }}%</span>
                    </div>
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-neutral-200/80">
                        <div class="h-full rounded-full bg-teal-500 transition-all" :style="{ width: avgProgressPercent + '%' }" />
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="text-sm font-semibold text-neutral-800">Recent attempts</h3>
                    <ul class="mt-3 space-y-2 text-xs text-neutral-600">
                        <li v-for="a in recentAttempts.slice(0, 4)" :key="a.id">{{ a.score }} / {{ a.max_score }} · {{ a.status }}</li>
                        <li v-if="!recentAttempts.length" class="text-neutral-500">No attempts yet.</li>
                    </ul>
                </div>

                <div class="mt-10 rounded-2xl border border-neutral-200/80 bg-slate-100/80 p-5">
                    <div class="mb-4 flex justify-center text-neutral-400">
                        <svg class="size-16" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="12" y="20" width="40" height="36" rx="2" class="fill-neutral-300" />
                            <rect x="16" y="16" width="12" height="8" rx="1" class="fill-neutral-400" />
                            <path d="M28 12c0-2 2-4 4-4s4 2 4 4v4H28v-4z" class="fill-emerald-600" />
                        </svg>
                    </div>
                    <h3 class="text-center text-sm font-bold text-neutral-900">Grow your school</h3>
                    <p class="mt-2 text-center text-xs leading-relaxed text-neutral-600">
                        Create a course, add modules and lessons, then invite students from the Students page.
                    </p>
                    <Link
                        v-if="canManageCourses"
                        href="/courses/create"
                        class="mt-4 flex w-full items-center justify-center rounded-xl bg-neutral-900 py-3 text-xs font-semibold uppercase tracking-wide text-white hover:bg-neutral-800"
                    >
                        New course
                    </Link>
                </div>
            </div>
        </template>
    </TenantLayout>
</template>
