<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ course: Object });

function destroyCourse() {
    if (confirm('Delete course?')) router.delete(`/courses/${props.course.id}`);
}
</script>

<template>
    <TenantLayout>
        <Head :title="course.title" />
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900">{{ course.title }}</h1>
                <p class="text-neutral-600">{{ course.language }} · {{ course.level }}</p>
            </div>
            <div class="flex gap-2">
                <Link :href="`/courses/${course.id}/edit`" class="rounded-lg border border-neutral-300 px-3 py-1.5 text-sm text-neutral-800 hover:bg-neutral-50">Edit</Link>
                <Link :href="`/courses/${course.id}/modules/create`" class="rounded-lg bg-indigo-500 px-3 py-1.5 text-sm text-white">Add module</Link>
                <button type="button" class="text-sm text-rose-400 hover:underline" @click="destroyCourse">Delete</button>
            </div>
        </div>
        <p v-if="course.description" class="mt-4 text-neutral-600">{{ course.description }}</p>
        <div class="mt-8 space-y-4">
            <div v-for="mod in course.modules" :key="mod.id" class="rounded-xl border border-neutral-200 bg-neutral-50 p-4">
                <div class="flex justify-between">
                    <h2 class="font-semibold text-neutral-900">{{ mod.title }}</h2>
                    <Link
                        :href="`/lessons?create=1&course_id=${course.id}&module_id=${mod.id}`"
                        class="text-xs text-indigo-400"
                    >
                        Add lesson
                    </Link>
                </div>
                <ul class="mt-2 text-sm text-neutral-600">
                    <li v-for="les in mod.lessons" :key="les.id" class="flex justify-between py-1">
                        <Link :href="`/modules/${mod.id}/lessons/${les.id}`" class="text-neutral-800 hover:text-indigo-600">{{ les.title }}</Link>
                        <span class="text-xs">{{ les.type }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </TenantLayout>
</template>
