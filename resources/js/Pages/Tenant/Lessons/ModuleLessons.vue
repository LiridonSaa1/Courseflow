<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ module: Object });
</script>

<template>
    <TenantLayout>
        <Head title="Lessons" />
        <Link :href="`/courses/${module.course.id}`" class="text-sm text-indigo-400">← Course</Link>
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-neutral-900">{{ module.title }}</h1>
            <Link
                class="rounded-lg bg-indigo-500 px-3 py-1.5 text-sm text-white"
                :href="`/lessons?create=1&course_id=${module.course_id}&module_id=${module.id}`"
            >
                Add lesson
            </Link>
        </div>
        <ul class="mt-4 space-y-2">
            <li v-for="l in module.lessons" :key="l.id">
                <Link :href="`/modules/${module.id}/lessons/${l.id}`" class="text-indigo-300 hover:underline">{{ l.title }}</Link>
            </li>
        </ul>
    </TenantLayout>
</template>
