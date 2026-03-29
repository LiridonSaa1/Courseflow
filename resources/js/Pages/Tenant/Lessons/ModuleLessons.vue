<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ module: Object });
</script>

<template>
    <TenantLayout>
        <Head title="Lessons" />
        <Link :href="`/courses/${module.course.id}`" class="text-sm text-indigo-400">← Course</Link>
        <h1 class="mt-4 text-2xl font-bold text-neutral-900">{{ module.title }}</h1>
        <ul class="mt-4 space-y-2">
            <li v-for="l in module.lessons" :key="l.id">
                <Link :href="`/modules/${module.id}/lessons/${l.id}`" class="text-indigo-300 hover:underline">{{ l.title }}</Link>
            </li>
        </ul>
    </TenantLayout>
</template>
