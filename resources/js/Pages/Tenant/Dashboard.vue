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
    workspaceStats: { type: Object, default: () => ({}) },
});

const productImages = [
    '/inapp/images/product-1.png',
    '/inapp/images/product-2.png',
    '/inapp/images/product-3.png',
    '/inapp/images/product-4.png',
    '/inapp/images/product-5.png',
    '/inapp/images/product-6.png',
    '/inapp/images/product-7.png',
    '/inapp/images/product-8.png',
    '/inapp/images/product-9.png',
    '/inapp/images/product-10.png',
];

const weekLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

const canManageCourses = computed(
    () =>
        props.roleNames.includes('owner') ||
        props.roleNames.includes('admin') ||
        props.roleNames.includes('teacher'),
);
const workspaceStats = computed(() => props.workspaceStats || {});

const todayLabel = computed(() => {
    const now = new Date();
    return now.toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
});

const safeActivity = computed(() => {
    const raw = Array.isArray(props.chartActivity) ? props.chartActivity.slice(0, 7) : [];
    const fallback = [0, 0, 0, 0, 0, 0, 0];
    const merged = [...fallback];

    raw.forEach((v, i) => {
        merged[i] = Number(v) || 0;
    });

    return merged;
});

const totalActivity = computed(() => safeActivity.value.reduce((sum, n) => sum + n, 0));

const avgProgressPercent = computed(() => {
    if (!props.progress.length) {
        return 0;
    }
    const sum = props.progress.reduce((acc, row) => acc + (Number(row.percent) || 0), 0);
    return Math.round(sum / props.progress.length);
});

const completionRate = computed(() => {
    if (!props.progress.length) {
        return 0;
    }

    const completed = props.progress.filter((row) => Number(row.percent) >= 100).length;
    return Math.round((completed / props.progress.length) * 100);
});

const scoreAverage = computed(() => {
    if (!props.recentAttempts.length) {
        return 0;
    }
    const avg =
        props.recentAttempts.reduce((sum, attempt) => {
            const max = Number(attempt.max_score) || 0;
            if (!max) {
                return sum;
            }
            return sum + (Number(attempt.score) / max) * 100;
        }, 0) / props.recentAttempts.length;
    return Math.round(avg);
});

const bestScore = computed(() => {
    if (!props.recentAttempts.length) {
        return 0;
    }
    return Math.max(
        ...props.recentAttempts.map((attempt) => {
            const max = Number(attempt.max_score) || 0;
            if (!max) {
                return 0;
            }
            return Math.round((Number(attempt.score) / max) * 100);
        }),
    );
});

const activityBars = computed(() => {
    const raw = safeActivity.value;
    const max = Math.max(...raw, 1);
    const maxIndex = raw.indexOf(Math.max(...raw));

    return raw.map((value, index) => {
        const scaled = 18 + Math.round((value / max) * 82);

        return {
            label: weekLabels[index] ?? `D${index + 1}`,
            height: scaled,
            strong: index === maxIndex && value > 0,
            value,
        };
    });
});

const summaryCards = computed(() => [
    {
        label: 'Total Courses',
        value: workspaceStats.value.courses ?? props.courses.length,
        delta: '+5% since last month',
        color: 'text-[#e66239]',
        tint: 'bg-[#e66239]/10 border-[#e66239]/25',
        iconBg: 'bg-[#e66239]',
    },
    {
        label: 'Total Students',
        value: workspaceStats.value.students ?? 0,
        delta: '+22% since last month',
        color: 'text-emerald-600',
        tint: 'bg-emerald-100 border-emerald-200',
        iconBg: 'bg-emerald-500',
    },
    {
        label: 'Total Teachers',
        value: workspaceStats.value.teachers ?? 0,
        delta: '+10% since last month',
        color: 'text-sky-600',
        tint: 'bg-sky-100 border-sky-200',
        iconBg: 'bg-sky-500',
    },
    {
        label: 'Quiz Attempts (7d)',
        value: totalActivity.value,
        delta: '+35% since last month',
        color: 'text-amber-600',
        tint: 'bg-amber-100 border-amber-200',
        iconBg: 'bg-amber-500',
    },
]);

const insightCards = computed(() => [
    {
        label: 'Total XP',
        value: props.stat?.xp ?? 0,
        trend: '+35% vs last month',
        trendColor: 'text-emerald-600',
        iconColor: 'text-[#e66239]',
    },
    {
        label: 'Level',
        value: props.stat?.level ?? 0,
        trend: '-20% vs last month',
        trendColor: 'text-red-500',
        iconColor: 'text-red-500',
    },
    {
        label: 'Streak (days)',
        value: props.stat?.streak_days ?? 0,
        trend: '-20% vs last month',
        trendColor: 'text-amber-600',
        iconColor: 'text-amber-500',
    },
]);

const topCourses = computed(() =>
    props.courses.slice(0, 5).map((course, index) => ({
        id: course.id,
        title: course.title,
        modules: course.modules_count ?? 0,
        classes: course.study_classes_count ?? 0,
        image: productImages[index % productImages.length],
        growth: [18, 32, 22, 28, 25][index % 5],
    })),
);

const lowProgressRows = computed(() => {
    const rows = [...props.progress]
        .sort((a, b) => (Number(a.percent) || 0) - (Number(b.percent) || 0))
        .slice(0, 5);

    return rows.map((row, index) => ({
        id: row.id ?? `${index}-${row.lesson_id ?? 'lesson'}`,
        title: row.lesson?.title || `Lesson #${row.lesson_id ?? index + 1}`,
        lessonId: row.lesson_id ?? '-',
        percent: Number(row.percent) || 0,
        image: productImages[(index + 5) % productImages.length],
    }));
});

const recentAttemptRows = computed(() =>
    props.recentAttempts.slice(0, 5).map((attempt, index) => ({
        id: attempt.id ?? `${index}-attempt`,
        title: `Quiz Attempt #${attempt.id ?? index + 1}`,
        subtitle: `${attempt.score ?? 0} / ${attempt.max_score ?? 0}`,
        status: String(attempt.status || 'pending'),
        image: productImages[(index + 2) % productImages.length],
    })),
);

function growthBadgeClass(index) {
    const variants = [
        'bg-red-50 text-red-600 border-red-200',
        'bg-blue-50 text-blue-600 border-blue-200',
        'bg-cyan-50 text-cyan-600 border-cyan-200',
        'bg-emerald-50 text-emerald-600 border-emerald-200',
        'bg-amber-50 text-amber-600 border-amber-200',
    ];

    return variants[index % variants.length];
}

function statusBadgeClass(status) {
    const normalized = String(status || '').toLowerCase();

    if (['completed', 'passed', 'success'].includes(normalized)) {
        return 'bg-emerald-50 text-emerald-600 border-emerald-200';
    }
    if (['processing', 'in_progress', 'started'].includes(normalized)) {
        return 'bg-blue-50 text-blue-600 border-blue-200';
    }
    if (['pending', 'queued', 'draft'].includes(normalized)) {
        return 'bg-amber-50 text-amber-600 border-amber-200';
    }

    return 'bg-red-50 text-red-600 border-red-200';
}
</script>

<template>
    <TenantLayout>
        <Head title="Dashboard" />

        <section class="space-y-3">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-[1.5rem] font-semibold text-[#171717] sm:text-[1.75rem]">Dashboard</h1>
                    <p class="text-sm text-neutral-500">Your tenant overview panel for courses, progress, and activity.</p>
                    <p class="mt-1 text-xs text-neutral-500">{{ todayLabel }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full border border-neutral-200 bg-white px-3 py-1 text-xs text-neutral-600">
                        {{ roleNames.join(', ') || 'Member' }}
                    </span>
                    <Link
                        v-if="canManageCourses"
                        href="/courses/create"
                        class="rounded-lg bg-[#e66239] px-3 py-2 text-xs font-semibold text-white transition hover:bg-[#cf5330]"
                    >
                        Add Course
                    </Link>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <article
                    v-for="card in summaryCards"
                    :key="card.label"
                    class="rounded-lg border p-4 shadow-sm"
                    :class="card.tint"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="inline-flex size-10 items-center justify-center rounded-md text-white"
                            :class="card.iconBg"
                        >
                            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M4 4h16v16H4z" />
                                <path d="M8 12h8" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm text-neutral-600">{{ card.label }}</p>
                            <p class="mt-1 text-2xl font-bold text-[#171717]">{{ card.value }}</p>
                            <p class="mt-1 text-xs" :class="card.color">{{ card.delta }}</p>
                        </div>
                    </div>
                </article>
            </div>

            <div class="grid gap-3 xl:grid-cols-3">
                <article
                    v-for="insight in insightCards"
                    :key="insight.label"
                    class="rounded-lg border border-neutral-200 bg-white shadow-sm"
                >
                    <div class="p-4">
                        <div class="mb-4 flex items-center justify-between border-b border-neutral-200 pb-5">
                            <div>
                                <p class="text-2xl font-bold text-[#171717]">{{ insight.value }}</p>
                                <p class="text-sm text-neutral-600">{{ insight.label }}</p>
                            </div>
                            <svg
                                viewBox="0 0 24 24"
                                class="size-8"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                :class="insight.iconColor"
                            >
                                <path d="M5 12h14" />
                                <path d="M12 5v14" />
                            </svg>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <p class="text-neutral-500">
                                <span :class="insight.trendColor">{{ insight.trend.split(' ')[0] }}</span>
                                {{ insight.trend.replace(insight.trend.split(' ')[0], '') }}
                            </p>
                            <a href="#courses" class="text-[#e66239] underline-offset-2 hover:underline">View</a>
                        </div>
                    </div>
                </article>
            </div>

            <div class="grid gap-3 2xl:grid-cols-2">
                <article class="rounded-lg border border-neutral-200 bg-white shadow-sm">
                    <header class="flex items-center justify-between border-b border-neutral-200 px-4 py-3">
                        <h2 class="text-base font-semibold text-[#171717]">Activity vs Attempts</h2>
                        <select class="rounded-md border border-neutral-200 bg-white px-2 py-1 text-xs text-neutral-600">
                            <option>This Week</option>
                            <option>This Month</option>
                            <option>This Year</option>
                        </select>
                    </header>
                    <div class="p-4">
                        <div class="flex h-56 items-end gap-2">
                            <div
                                v-for="bar in activityBars"
                                :key="bar.label"
                                class="flex min-w-0 flex-1 flex-col items-center justify-end"
                            >
                                <div class="w-full rounded-t-md transition-all" :class="bar.strong ? 'bg-[#e66239]' : 'bg-[#e66239]/25'" :style="{ height: bar.height + '%' }" />
                                <p class="mt-2 text-[11px] text-neutral-500">{{ bar.label }}</p>
                                <p class="text-[10px] text-neutral-400">{{ bar.value }}</p>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="rounded-lg border border-neutral-200 bg-white shadow-sm">
                    <header class="flex items-center justify-between border-b border-neutral-200 px-4 py-3">
                        <h2 class="text-base font-semibold text-[#171717]">Overall Information</h2>
                        <select class="rounded-md border border-neutral-200 bg-white px-2 py-1 text-xs text-neutral-600">
                            <option>Last 6 Months</option>
                            <option>This Month</option>
                            <option>This Week</option>
                        </select>
                    </header>
                    <div class="p-4">
                        <h3 class="text-sm font-semibold text-[#171717]">Learning Overview</h3>
                        <div class="mt-3 grid items-center gap-4 sm:grid-cols-2">
                            <div class="mx-auto">
                                <div
                                    class="relative grid size-40 place-items-center rounded-full"
                                    :style="{ background: `conic-gradient(#e66239 ${completionRate}%, #eceff3 0)` }"
                                >
                                    <div class="grid size-28 place-items-center rounded-full bg-white text-center">
                                        <p class="text-2xl font-semibold text-[#171717]">{{ completionRate }}%</p>
                                        <p class="text-[11px] text-neutral-500">Completion</p>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-lg border border-neutral-200 p-3 text-center">
                                    <p class="text-xl font-semibold text-[#171717]">{{ bestScore }}%</p>
                                    <p class="text-xs text-emerald-600">Best score</p>
                                </div>
                                <div class="rounded-lg border border-neutral-200 p-3 text-center">
                                    <p class="text-xl font-semibold text-[#171717]">{{ scoreAverage }}%</p>
                                    <p class="text-xs text-amber-600">Average score</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-4 gap-2 border-t border-neutral-200 pt-4 text-center">
                            <div>
                                <p class="text-lg font-semibold text-[#171717]">{{ workspaceStats.lessons ?? 0 }}</p>
                                <p class="text-[11px] text-neutral-500">Lessons</p>
                            </div>
                            <div>
                                <p class="text-lg font-semibold text-[#171717]">{{ workspaceStats.quizzes ?? 0 }}</p>
                                <p class="text-[11px] text-neutral-500">Quizzes</p>
                            </div>
                            <div>
                                <p class="text-lg font-semibold text-[#171717]">{{ workspaceStats.liveSessions ?? 0 }}</p>
                                <p class="text-[11px] text-neutral-500">Sessions</p>
                            </div>
                            <div>
                                <p class="text-lg font-semibold text-[#171717]">{{ workspaceStats.attempts ?? 0 }}</p>
                                <p class="text-[11px] text-neutral-500">Attempts</p>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <div class="grid gap-3 xl:grid-cols-3">
                <article id="courses" class="rounded-lg border border-neutral-200 bg-white shadow-sm">
                    <header class="flex items-center justify-between border-b border-neutral-200 px-4 py-3">
                        <h3 class="text-base font-semibold text-[#171717]">Top Courses</h3>
                        <button type="button" class="rounded-md border border-neutral-200 px-2 py-1 text-xs text-neutral-600">Today</button>
                    </header>
                    <ul class="divide-y divide-neutral-200">
                        <li v-for="(course, index) in topCourses" :key="course.id" class="flex items-center gap-3 px-4 py-3">
                            <img :src="course.image" alt="" class="size-12 rounded-md object-cover" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-[#171717]">{{ course.title }}</p>
                                <p class="mt-1 text-xs text-neutral-500">{{ course.modules }} modules &middot; {{ course.classes }} classes</p>
                            </div>
                            <span class="rounded-full border px-2 py-0.5 text-[11px] font-medium" :class="growthBadgeClass(index)">
                                {{ course.growth }}%
                            </span>
                        </li>
                        <li v-if="!topCourses.length" class="px-4 py-6 text-sm text-neutral-500">No courses yet.</li>
                    </ul>
                </article>

                <article class="rounded-lg border border-neutral-200 bg-white shadow-sm">
                    <header class="flex items-center justify-between border-b border-neutral-200 px-4 py-3">
                        <h3 class="text-base font-semibold text-[#171717]">Low Progress Lessons</h3>
                        <Link href="/analytics" class="text-xs text-[#e66239] underline-offset-2 hover:underline">View All</Link>
                    </header>
                    <ul class="divide-y divide-neutral-200">
                        <li v-for="lesson in lowProgressRows" :key="lesson.id" class="flex items-center gap-3 px-4 py-3">
                            <img :src="lesson.image" alt="" class="size-12 rounded-md object-cover" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-[#171717]">{{ lesson.title }}</p>
                                <p class="mt-1 text-xs text-neutral-500">ID: #{{ lesson.lessonId }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm font-semibold text-[#e66239]">{{ lesson.percent }}%</p>
                                <p class="text-[11px] text-neutral-500">complete</p>
                            </div>
                        </li>
                        <li v-if="!lowProgressRows.length" class="px-4 py-6 text-sm text-neutral-500">
                            No lesson progress records yet.
                        </li>
                    </ul>
                </article>

                <article class="rounded-lg border border-neutral-200 bg-white shadow-sm">
                    <header class="flex items-center justify-between border-b border-neutral-200 px-4 py-3">
                        <h3 class="text-base font-semibold text-[#171717]">Recent Quiz Attempts</h3>
                        <button type="button" class="rounded-md border border-neutral-200 px-2 py-1 text-xs text-neutral-600">Weekly</button>
                    </header>
                    <ul class="divide-y divide-neutral-200">
                        <li v-for="attempt in recentAttemptRows" :key="attempt.id" class="flex items-center gap-3 px-4 py-3">
                            <img :src="attempt.image" alt="" class="size-12 rounded-md object-cover" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-[#171717]">{{ attempt.title }}</p>
                                <p class="mt-1 text-xs text-neutral-500">{{ attempt.subtitle }}</p>
                            </div>
                            <span class="rounded-full border px-2 py-0.5 text-[11px] font-medium capitalize" :class="statusBadgeClass(attempt.status)">
                                {{ attempt.status.replace('_', ' ') }}
                            </span>
                        </li>
                        <li v-if="!recentAttemptRows.length" class="px-4 py-6 text-sm text-neutral-500">
                            No recent attempts found.
                        </li>
                    </ul>
                </article>
            </div>
        </section>
    </TenantLayout>
</template>
