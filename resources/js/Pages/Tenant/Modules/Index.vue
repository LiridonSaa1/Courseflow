<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ course: Object });
</script>

<template>
    <TenantLayout>
        <Head title="Modules" />
        <Link :href="`/courses/${course.id}`" class="text-sm text-indigo-400">← Back</Link>
        <h1 class="mt-4 text-2xl font-bold text-neutral-900">Modules · {{ course.title }}</h1>
    </TenantLayout>
</template>
