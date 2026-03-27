<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ course: Object, module: Object });

const form = useForm({ title: props.module.title, sort_order: props.module.sort_order });

function submit() {
    form.put(`/courses/${props.course.id}/modules/${props.module.id}`);
}
</script>

<template>
    <TenantLayout>
        <Head title="Edit module" />
        <Link :href="`/courses/${course.id}`" class="text-sm text-indigo-400">← Back</Link>
        <h1 class="mt-4 text-2xl font-bold text-neutral-900">Edit module</h1>
        <form class="mt-6 max-w-md space-y-4" @submit.prevent="submit">
            <input v-model="form.title" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900" />
            <button type="submit" class="rounded-lg bg-indigo-500 px-4 py-2 text-white">Update</button>
        </form>
    </TenantLayout>
</template>
