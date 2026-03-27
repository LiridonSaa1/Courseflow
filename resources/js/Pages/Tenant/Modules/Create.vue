<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ course: Object });

const form = useForm({ title: '', sort_order: 0 });

function submit() {
    form.post(`/courses/${props.course.id}/modules`);
}
</script>

<template>
    <TenantLayout>
        <Head title="New module" />
        <Link :href="`/courses/${course.id}`" class="text-sm text-indigo-400">← Back</Link>
        <h1 class="mt-4 text-2xl font-bold text-neutral-900">New module</h1>
        <form class="mt-6 max-w-md space-y-4" @submit.prevent="submit">
            <input v-model="form.title" placeholder="Title" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900" />
            <button type="submit" class="rounded-lg bg-indigo-500 px-4 py-2 text-white">Save</button>
        </form>
    </TenantLayout>
</template>
