<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    courses: { type: Array, default: () => [] },
});
</script>

<template>
    <TenantLayout>
        <Head title="Courses" />
        <div class="flex justify-between">
            <h1 class="text-2xl font-bold text-neutral-900">Courses</h1>
            <Link href="/courses/create" class="rounded-lg bg-indigo-500 px-3 py-1.5 text-sm text-white hover:bg-indigo-400">Add</Link>
        </div>
        <ul class="mt-6 space-y-2">
            <li v-for="c in courses" :key="c.id">
                <Link :href="`/courses/${c.id}`" class="block rounded-lg border border-neutral-200 px-4 py-3 hover:bg-neutral-50">
                    {{ c.title }}
                </Link>
            </li>
        </ul>
    </TenantLayout>
</template>
