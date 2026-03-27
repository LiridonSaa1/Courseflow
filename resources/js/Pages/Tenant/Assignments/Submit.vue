<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ assignment: Object });

const form = useForm({ body: '' });

function submit() {
    form.post(`/assignments/${props.assignment.id}/submit`);
}
</script>

<template>
    <TenantLayout>
        <Head title="Assignment" />
        <h1 class="text-2xl font-bold text-neutral-900">{{ assignment.title }}</h1>
        <p class="mt-2 text-neutral-600">{{ assignment.instructions }}</p>
        <form class="mt-6 space-y-4" @submit.prevent="submit">
            <textarea v-model="form.body" rows="8" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900" />
            <button type="submit" class="rounded-lg bg-indigo-500 px-4 py-2 text-white">Submit</button>
        </form>
    </TenantLayout>
</template>
