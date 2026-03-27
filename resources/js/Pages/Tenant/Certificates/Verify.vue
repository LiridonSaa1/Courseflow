<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({ certificate: Object });
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-white px-4">
        <Head title="Verify certificate" />
        <div class="max-w-lg rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-8 text-center text-emerald-100">
            <h1 class="text-xl font-semibold text-neutral-900">Verified certificate</h1>
            <p class="mt-4">{{ certificate.user?.name }}</p>
            <p class="text-lg font-medium text-neutral-900">{{ certificate.course?.title }}</p>
            <p class="mt-2 text-sm">Score: {{ certificate.score }}%</p>
        </div>
    </div>
</template>
