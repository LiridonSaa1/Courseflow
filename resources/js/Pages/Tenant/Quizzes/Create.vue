<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ lesson: Object });

const form = useForm({
    title: 'Quiz',
    time_limit_seconds: null,
    randomize: false,
    questions: [
        {
            type: 'multiple_choice',
            points: 1,
            payload: {
                question: 'Translate: Hello',
                options: ['Hallo', 'Bonjour', 'Ciao'],
                correct: '0',
            },
        },
    ],
});

function submit() {
    form.post(`/lessons/${props.lesson.id}/quizzes`);
}
</script>

<template>
    <TenantLayout>
        <Head title="New quiz" />
        <Link :href="`/modules/${lesson.module_id}/lessons/${lesson.id}`" class="text-sm text-indigo-400">← Lesson</Link>
        <h1 class="mt-4 text-2xl font-bold text-neutral-900">New quiz</h1>
        <form class="mt-6 max-w-lg space-y-4" @submit.prevent="submit">
            <input v-model="form.title" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900" />
            <label class="flex items-center gap-2 text-sm text-neutral-600">
                <input v-model="form.randomize" type="checkbox" /> Randomize order
            </label>
            <button type="submit" class="rounded-lg bg-indigo-500 px-4 py-2 text-white">Save with demo question</button>
        </form>
    </TenantLayout>
</template>
