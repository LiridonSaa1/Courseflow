<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    discussions: { type: Array, default: () => [] },
    courses: { type: Array, default: () => [] },
});

const form = useForm({
    title: '',
    body: '',
    course_id: props.courses[0]?.id ?? null,
});

function submit() {
    form.post('/community');
}
</script>

<template>
    <TenantLayout>
        <Head title="Community" />
        <h1 class="text-2xl font-bold text-neutral-900">Discussions</h1>
        <form class="mt-6 max-w-lg space-y-3 rounded-xl border border-neutral-200 bg-neutral-50 p-4" @submit.prevent="submit">
            <input v-model="form.title" placeholder="Title" class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900" />
            <textarea v-model="form.body" rows="3" placeholder="Question or thread starter" class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900" />
            <select v-model="form.course_id" required class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900">
                <option disabled value="">Select course</option>
                <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
            </select>
            <button type="submit" class="rounded-lg bg-indigo-500 px-3 py-2 text-sm text-white">Post</button>
        </form>
        <ul class="mt-8 space-y-4">
            <li v-for="d in discussions" :key="d.id" class="rounded-xl border border-neutral-200 bg-slate-900/30 p-4">
                <h2 class="font-semibold text-neutral-900">{{ d.title }}</h2>
                <p class="mt-2 text-sm text-neutral-600">{{ d.body }}</p>
            </li>
        </ul>
    </TenantLayout>
</template>
