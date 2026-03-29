<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    title: { type: String, default: 'Coming soon' },
});
</script>

<template>
    <TenantLayout>
        <Head :title="title" />
        <div class="mx-auto max-w-lg rounded-xl border border-dashed border-neutral-300 bg-white p-8 text-center shadow-sm">
            <h1 class="text-xl font-semibold text-neutral-900">{{ title }}</h1>
            <p class="mt-3 text-sm text-neutral-600">This section is not available yet. It will appear here in a future update.</p>
            <Link class="mt-6 inline-flex text-sm font-medium text-[#e66239]" href="/dashboard"> Back to dashboard </Link>
        </div>
    </TenantLayout>
</template>
