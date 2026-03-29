<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ lesson: Object });
</script>

<template>
    <TenantLayout>
        <Head :title="`Quizzes · ${lesson.title}`" />
        <Link :href="`/modules/${lesson.module_id}/lessons/${lesson.id}`" class="text-sm text-indigo-400">← Lesson</Link>
        <h1 class="mt-4 text-2xl font-bold text-neutral-900">Quizzes · {{ lesson.title }}</h1>
        <Link :href="`/lessons/${lesson.id}/quizzes/create`" class="mt-4 inline-block rounded-lg bg-indigo-500 px-3 py-1.5 text-sm text-white">New quiz</Link>
        <ul class="mt-4 space-y-2">
            <li v-for="q in lesson.quizzes" :key="q.id" class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-neutral-200 px-3 py-2">
                <span class="font-medium text-neutral-900">{{ q.title }}</span>
                <div class="flex flex-wrap gap-3 text-sm">
                    <Link :href="`/lessons/${lesson.id}/quizzes/${q.id}/edit`" class="text-indigo-400">Edit</Link>
                    <Link :href="`/quizzes/${q.id}/take`" class="text-neutral-600">Take</Link>
                </div>
            </li>
        </ul>
    </TenantLayout>
</template>
