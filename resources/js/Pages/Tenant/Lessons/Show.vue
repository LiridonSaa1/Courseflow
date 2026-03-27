<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ lesson: Object });

function destroyLesson() {
    if (confirm('Delete lesson?')) {
        router.delete(`/modules/${props.lesson.module_id}/lessons/${props.lesson.id}`);
    }
}
</script>

<template>
    <TenantLayout>
        <Head :title="lesson.title" />
        <Link :href="`/courses/${lesson.module.course.id}`" class="text-sm text-indigo-400">← Course</Link>
        <div class="mt-4 flex flex-wrap justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900">{{ lesson.title }}</h1>
                <p class="text-sm text-slate-500">{{ lesson.type }}</p>
            </div>
            <div class="flex gap-2">
                <Link :href="`/modules/${lesson.module_id}/lessons/${lesson.id}/edit`" class="rounded-lg border border-neutral-300 px-3 py-1 text-sm text-neutral-800 hover:bg-neutral-50">Edit</Link>
                <Link :href="`/lessons/${lesson.id}/quizzes`" class="rounded-lg border border-neutral-300 px-3 py-1 text-sm text-neutral-800 hover:bg-neutral-50">Quizzes</Link>
                <Link :href="`/lessons/${lesson.id}/quizzes/create`" class="rounded-lg bg-indigo-500 px-3 py-1 text-sm text-white">Add quiz</Link>
                <button type="button" class="text-sm text-rose-400" @click="destroyLesson">Delete</button>
            </div>
        </div>
        <article class="prose prose-neutral mt-6 max-w-none text-neutral-800">
            <p class="whitespace-pre-wrap">{{ lesson.content?.body }}</p>
        </article>
        <div v-if="lesson.quizzes?.length" class="mt-8">
            <h2 class="font-semibold text-neutral-900">Quizzes</h2>
            <ul class="mt-2 space-y-2">
                <li v-for="q in lesson.quizzes" :key="q.id">
                    <Link :href="`/quizzes/${q.id}/take`" class="text-indigo-400 hover:underline">{{ q.title }} — Take</Link>
                    <Link :href="`/lessons/${lesson.id}/quizzes/${q.id}/edit`" class="ml-3 text-xs text-slate-500">Edit</Link>
                </li>
            </ul>
        </div>
    </TenantLayout>
</template>
