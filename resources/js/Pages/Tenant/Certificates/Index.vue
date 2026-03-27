<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    certificates: { type: Array, default: () => [] },
    courses: { type: Array, default: () => [] },
});

const form = useForm({
    course_id: props.courses[0]?.id ?? null,
    score: 85,
});

function submit() {
    form.post('/certificates/generate');
}
</script>

<template>
    <TenantLayout>
        <Head title="Certificates" />
        <h1 class="text-2xl font-bold text-neutral-900">Certificates</h1>
        <form class="mt-6 max-w-md space-y-3 rounded-xl border border-neutral-200 bg-neutral-50 p-4" @submit.prevent="submit">
            <select v-model="form.course_id" required class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900">
                <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
            </select>
            <input v-model.number="form.score" type="number" min="0" max="100" class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900" />
            <button type="submit" class="rounded-lg bg-indigo-500 px-3 py-2 text-sm text-white">Generate PDF</button>
        </form>
        <ul class="mt-6 text-sm text-neutral-600">
            <li v-for="c in certificates" :key="c.id">Course #{{ c.course_id }} — {{ c.score }}%</li>
        </ul>
    </TenantLayout>
</template>
