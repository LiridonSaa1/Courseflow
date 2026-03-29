<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <TenantLayout>
        <Head title="Settings" />
        <h1 class="text-2xl font-bold text-neutral-900">Settings</h1>
        <p class="mt-2 text-sm text-neutral-600">
            Workspace and system settings will be available here. This section is under construction.
        </p>
        <v-card class="mt-6 rounded-lg border border-dashed border-neutral-300 bg-neutral-50 p-8 text-center">
            <p class="text-sm text-neutral-600">Coming soon: branding, domain, roles, billing, and more.</p>
        </v-card>
    </TenantLayout>
</template>
