<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    title: '',
    language: '',
    level: 'A1',
    description: '',
});

function submit() {
    form.post('/courses');
}
</script>

<template>
    <TenantLayout>
        <Head title="New course" />
        <h1 class="text-2xl font-bold text-neutral-900">New course</h1>
        <form class="mt-6 max-w-lg space-y-4" @submit.prevent="submit">
            <input v-model="form.title" placeholder="Title" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900" />
            <input v-model="form.language" placeholder="Language" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900" />
            <select v-model="form.level" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900">
                <option v-for="l in ['A1', 'A2', 'B1', 'B2', 'C1']" :key="l" :value="l">{{ l }}</option>
            </select>
            <textarea v-model="form.description" rows="3" placeholder="Description" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900" />
            <button type="submit" :disabled="form.processing" class="rounded-lg bg-indigo-500 px-4 py-2 text-white">Save</button>
        </form>
    </TenantLayout>
</template>
