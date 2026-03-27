<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ lesson: Object });
</script>

<template>
    <TenantLayout>
        <Head title="Quizzes" />
        <Link :href="`/modules/${lesson.module_id}/lessons/${lesson.id}`" class="text-sm text-indigo-400">← Lesson</Link>
        <h1 class="mt-4 text-2xl font-bold text-neutral-900">Quizzes · {{ lesson.title }}</h1>
        <Link :href="`/lessons/${lesson.id}/quizzes/create`" class="mt-4 inline-block rounded-lg bg-indigo-500 px-3 py-1.5 text-sm text-white">New quiz</Link>
        <ul class="mt-4 space-y-2">
            <li v-for="q in lesson.quizzes" :key="q.id" class="flex justify-between rounded-lg border border-neutral-200 px-3 py-2">
                <span>{{ q.title }}</span>
                <Link :href="`/quizzes/${q.id}/take`" class="text-indigo-400">Take</Link>
            </li>
        </ul>
    </TenantLayout>
</template>
