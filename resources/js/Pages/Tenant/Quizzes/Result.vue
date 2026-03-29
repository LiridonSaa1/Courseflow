<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    quiz: Object,
    attempt: Object,
    percent: { type: Number, default: 0 },
    can_retry: { type: Boolean, default: false },
    show_results_instantly: { type: Boolean, default: true },
});

const passed = computed(() => props.attempt?.passed === true);
const scoreLine = computed(() => `${props.attempt?.score ?? 0} / ${props.attempt?.max_score ?? 0}`);
</script>

<template>
    <TenantLayout>
        <Head :title="`Result · ${quiz.title}`" />

        <div class="mx-auto max-w-lg px-4 py-8">
            <h1 class="text-2xl font-bold text-neutral-900">{{ quiz.title }}</h1>
            <p class="mt-2 text-sm text-neutral-600">Your result</p>

            <div v-if="show_results_instantly" class="mt-8 rounded-xl border border-neutral-200 bg-neutral-50 p-6">
                <p class="text-3xl font-bold text-neutral-900">{{ percent }}%</p>
                <p class="mt-1 text-sm text-neutral-600">{{ scoreLine }}</p>
                <p v-if="attempt.passed !== null && attempt.passed !== undefined" class="mt-4">
                    <span
                        class="inline-flex rounded-full px-3 py-1 text-sm font-medium"
                        :class="passed ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                    >
                        {{ passed ? 'Passed' : 'Failed' }}
                    </span>
                </p>
            </div>
            <div v-else class="mt-8 rounded-xl border border-neutral-200 bg-neutral-50 p-6 text-sm text-neutral-700">
                Results are hidden until released by your instructor.
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <Link
                    v-if="show_results_instantly"
                    :href="`/quizzes/${quiz.id}/attempts/${attempt.id}/review`"
                    class="rounded-lg bg-indigo-500 px-6 py-2.5 text-sm font-medium text-white"
                >
                    Review answers
                </Link>
                <Link
                    v-if="can_retry"
                    :href="`/quizzes/${quiz.id}/take`"
                    class="rounded-lg border border-neutral-300 px-6 py-2.5 text-sm text-neutral-800"
                >
                    Retry
                </Link>
                <Link href="/dashboard" class="rounded-lg border border-neutral-300 px-6 py-2.5 text-sm text-neutral-700">
                    Dashboard
                </Link>
            </div>
        </div>
    </TenantLayout>
</template>
