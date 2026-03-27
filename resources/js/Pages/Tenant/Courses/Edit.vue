<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ course: Object });

const form = useForm({
    title: props.course.title,
    language: props.course.language,
    level: props.course.level,
    description: props.course.description ?? '',
});

function submit() {
    form.put(`/courses/${props.course.id}`);
}
</script>

<template>
    <TenantLayout>
        <Head title="Edit course" />
        <h1 class="text-2xl font-bold text-neutral-900">Edit course</h1>
        <form class="mt-6 max-w-lg space-y-4" @submit.prevent="submit">
            <input v-model="form.title" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900" />
            <input v-model="form.language" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900" />
            <select v-model="form.level" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900">
                <option v-for="l in ['A1', 'A2', 'B1', 'B2', 'C1']" :key="l" :value="l">{{ l }}</option>
            </select>
            <textarea v-model="form.description" rows="3" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900" />
            <button type="submit" class="rounded-lg bg-indigo-500 px-4 py-2 text-white">Update</button>
        </form>
    </TenantLayout>
</template>
