<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ lesson: Object, quiz: Object });

const form = useForm({
    title: props.quiz.title,
    time_limit_seconds: props.quiz.time_limit_seconds,
    randomize: props.quiz.randomize,
});

function submit() {
    form.put(`/lessons/${props.lesson.id}/quizzes/${props.quiz.id}`);
}
</script>

<template>
    <TenantLayout>
        <Head title="Edit quiz" />
        <Link :href="`/lessons/${lesson.id}/quizzes`" class="text-sm text-indigo-400">← Quizzes</Link>
        <h1 class="mt-4 text-2xl font-bold text-neutral-900">Edit quiz</h1>
        <form class="mt-6 max-w-md space-y-4" @submit.prevent="submit">
            <input v-model="form.title" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900" />
            <button type="submit" class="rounded-lg bg-indigo-500 px-4 py-2 text-white">Update</button>
        </form>
    </TenantLayout>
</template>
