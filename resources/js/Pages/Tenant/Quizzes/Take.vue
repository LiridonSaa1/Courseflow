<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({ quiz: Object });

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

function submit() {
    form.answers = { ...answers };
    form.post(`/quizzes/${props.quiz.id}/attempts`);
}
</script>

<template>
    <TenantLayout>
        <Head :title="quiz.title" />
        <h1 class="text-2xl font-bold text-neutral-900">{{ quiz.title }}</h1>
        <form class="mt-6 space-y-8" @submit.prevent="submit">
            <fieldset v-for="q in quiz.questions" :key="q.id" class="rounded-xl border border-neutral-200 bg-neutral-50 p-4">
                <legend class="px-2 text-sm font-medium text-indigo-300">{{ q.type }} · {{ q.points }} pts</legend>
                <p class="mt-2 text-neutral-800">{{ q.payload?.question }}</p>
                <div v-if="q.type === 'multiple_choice'" class="mt-3 space-y-2">
                    <label v-for="(opt, idx) in q.payload?.options ?? []" :key="idx" class="flex items-center gap-2 text-sm text-neutral-700">
                        <input v-model="answers[q.id]" type="radio" :name="'q'+q.id" :value="String(idx)" />
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
            <button type="submit" :disabled="form.processing" class="rounded-lg bg-indigo-500 px-6 py-2 text-white">Submit</button>
        </form>
    </TenantLayout>
</template>
