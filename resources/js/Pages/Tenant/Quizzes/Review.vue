<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    quiz: Object,
    attempt: Object,
    items: { type: Array, default: () => [] },
    show_correct: { type: Boolean, default: true },
});

function formatAnswer(val) {
    if (val === null || val === undefined) {
        return '—';
    }
    if (typeof val === 'object') {
        return JSON.stringify(val);
    }
    return String(val);
}
</script>

<template>
    <TenantLayout>
        <Head :title="`Review · ${quiz.title}`" />

        <div class="px-4 py-6">
            <h1 class="text-2xl font-bold text-neutral-900">{{ quiz.title }}</h1>
            <p class="mt-1 text-sm text-neutral-600">Review your answers</p>

            <div class="mt-8 space-y-6">
                <div
                    v-for="row in items"
                    :key="row.id"
                    class="rounded-xl border p-4"
                    :class="row.correct ? 'border-emerald-200 bg-emerald-50/50' : 'border-rose-200 bg-rose-50/50'"
                >
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <p class="font-medium text-neutral-900">{{ row.question_text }}</p>
                        <span class="text-xs text-neutral-500">{{ row.type }} · {{ row.points }} pts</span>
                    </div>
                    <p class="mt-2 text-sm text-neutral-700">
                        <span class="font-medium">Your answer:</span>
                        {{ formatAnswer(row.student_answer) }}
                    </p>
                    <p v-if="show_correct && row.correct_answer" class="mt-1 text-sm text-neutral-700">
                        <span class="font-medium">Correct:</span>
                        {{ row.correct_answer }}
                    </p>
                    <p v-if="row.explanation" class="mt-2 text-sm text-neutral-600">
                        {{ row.explanation }}
                    </p>
                </div>
            </div>

            <div class="mt-10">
                <Link
                    :href="`/quizzes/${quiz.id}/attempts/${attempt.id}`"
                    class="text-sm font-medium text-indigo-600"
                >
                    ← Back to result
                </Link>
            </div>
        </div>
    </TenantLayout>
</template>
