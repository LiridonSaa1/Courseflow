<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    quiz: Object,
    started: { type: Boolean, default: false },
    meta: {
        type: Object,
        default: () => ({
            question_count: 0,
            attempts_used: 0,
            attempts_remaining: null,
            can_start: true,
        }),
    },
});

const answers = reactive({});

watch(
    () => props.quiz?.questions,
    (qs) => {
        qs?.forEach((q) => {
            if (answers[q.id] === undefined) {
                answers[q.id] = '';
            }
        });
    },
    { immediate: true }
);

const form = useForm({ answers: {} });

function startQuiz() {
    if (!props.meta.can_start) {
        return;
    }
    router.get(`/quizzes/${props.quiz.id}/take`, { started: 1 }, { preserveState: false });
}

function submit() {
    form.answers = { ...answers };
    form.post(`/quizzes/${props.quiz.id}/attempts`);
}

function formatDuration(sec) {
    if (sec == null || sec === '') {
        return 'No limit';
    }
    const s = Number(sec);
    if (s < 60) {
        return `${s}s`;
    }
    const m = Math.floor(s / 60);
    const r = s % 60;
    return r ? `${m}m ${r}s` : `${m}m`;
}
</script>

<template>
    <TenantLayout>
        <Head :title="quiz.title" />

        <div v-if="!started" class="mx-auto max-w-lg px-4 py-8">
            <h1 class="text-2xl font-bold text-neutral-900">{{ quiz.title }}</h1>
            <p v-if="quiz.description" class="mt-3 text-sm text-neutral-600">
                {{ quiz.description }}
            </p>
            <ul class="mt-6 space-y-2 text-sm text-neutral-700">
                <li>
                    <span class="font-medium text-neutral-900">Questions:</span>
                    {{ meta.question_count }}
                </li>
                <li>
                    <span class="font-medium text-neutral-900">Duration:</span>
                    {{ formatDuration(quiz.time_limit_seconds) }}
                </li>
                <li v-if="quiz.pass_mark != null">
                    <span class="font-medium text-neutral-900">Pass mark:</span>
                    {{ quiz.pass_mark }}%
                </li>
                <li v-if="meta.attempts_remaining != null">
                    <span class="font-medium text-neutral-900">Attempts left:</span>
                    {{ meta.attempts_remaining }}
                </li>
            </ul>

            <div v-if="!meta.can_start" class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                You have no attempts remaining for this quiz.
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <button
                    type="button"
                    class="rounded-lg bg-indigo-500 px-6 py-2.5 text-sm font-medium text-white disabled:opacity-50"
                    :disabled="!meta.can_start"
                    @click="startQuiz"
                >
                    Start quiz
                </button>
                <Link href="/dashboard" class="rounded-lg border border-neutral-300 px-6 py-2.5 text-sm text-neutral-700">
                    Cancel
                </Link>
            </div>
        </div>

        <div v-else class="px-4 py-6">
            <h1 class="text-2xl font-bold text-neutral-900">{{ quiz.title }}</h1>
            <form class="mt-6 space-y-8" @submit.prevent="submit">
                <fieldset
                    v-for="q in quiz.questions"
                    :key="q.id"
                    class="rounded-xl border border-neutral-200 bg-neutral-50 p-4"
                >
                    <legend class="px-2 text-sm font-medium text-indigo-300">{{ q.type }} · {{ q.points }} pts</legend>
                    <p class="mt-2 text-neutral-800">{{ q.payload?.question }}</p>
                    <div v-if="q.type === 'multiple_choice'" class="mt-3 space-y-2">
                        <label
                            v-for="(opt, idx) in q.payload?.options ?? []"
                            :key="idx"
                            class="flex items-center gap-2 text-sm text-neutral-700"
                        >
                            <input v-model="answers[q.id]" type="radio" :name="'q' + q.id" :value="String(idx)" />
                            {{ opt }}
                        </label>
                    </div>
                    <input
                        v-else
                        v-model="answers[q.id]"
                        type="text"
                        class="mt-3 w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900"
                        placeholder="Your answer"
                    />
                </fieldset>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-indigo-500 px-6 py-2 text-white disabled:opacity-50"
                >
                    Submit
                </button>
            </form>
        </div>
    </TenantLayout>
</template>
