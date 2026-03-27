<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    message: { type: String, default: '' },
});
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-white px-4 text-center">
        <Head title="Suspended" />
        <div class="max-w-md rounded-2xl border border-amber-500/30 bg-amber-500/10 p-8 text-amber-100">
            <h1 class="text-xl font-semibold">Workspace unavailable</h1>
            <p class="mt-3 text-sm text-amber-200/90">{{ message }}</p>
        </div>
    </div>
</template>
