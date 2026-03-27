<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    avgScores: Array,
    weakLessons: Array,
    courseOutline: Array,
});
</script>

<template>
    <TenantLayout>
        <Head title="Analytics" />
        <h1 class="text-2xl font-bold text-neutral-900">Analytics</h1>
        <p class="mt-2 text-sm text-neutral-600">Avg score by quiz, weak lesson aggregates, and catalog shape.</p>
        <div class="mt-8 grid gap-6 md:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-4">
                <h2 class="font-semibold text-neutral-900">Courses</h2>
                <ul class="mt-2 text-sm text-neutral-600">
                    <li v-for="c in courseOutline" :key="c.title">{{ c.title }} — {{ c.modules }} modules, {{ c.lessons }} lessons</li>
                </ul>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-4">
                <h2 class="font-semibold text-neutral-900">Quiz avg %</h2>
                <ul class="mt-2 text-sm text-neutral-600">
                    <li v-for="row in avgScores" :key="row.quiz_id">Quiz #{{ row.quiz_id }}: {{ Math.round(row.pct ?? 0) }}%</li>
                </ul>
            </div>
        </div>
    </TenantLayout>
</template>
