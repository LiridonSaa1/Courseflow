<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

defineProps({
    sessions: { type: Array, default: () => [] },
    classes: { type: Array, default: () => [] },
});

const form = useForm({
    title: '',
    class_id: null,
    starts_at: '',
    meeting_url: '',
});

function submit() {
    form.post('/live-sessions');
}

function join(id) {
    router.visit(`/live-sessions/${id}/join`);
}
</script>

<template>
    <TenantLayout>
        <Head title="Live classes" />
        <h1 class="text-2xl font-bold text-neutral-900">Live classes</h1>
        <form class="mt-6 max-w-md space-y-3 rounded-xl border border-neutral-200 bg-neutral-50 p-4" @submit.prevent="submit">
            <input v-model="form.title" placeholder="Session title" class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900" />
            <select v-model="form.class_id" class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900">
                <option :value="null">No class</option>
                <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <input v-model="form.starts_at" type="datetime-local" class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900" />
            <input v-model="form.meeting_url" placeholder="Meet / Zoom URL" class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900" />
            <button type="submit" class="rounded-lg bg-indigo-500 px-3 py-2 text-sm text-white">Schedule</button>
        </form>
        <ul class="mt-6 space-y-2">
            <li v-for="s in sessions" :key="s.id" class="flex justify-between rounded-lg border border-neutral-200 px-3 py-2 text-sm text-neutral-700">
                <span>{{ s.title }}</span>
                <button type="button" class="text-indigo-400 hover:underline" @click="join(s.id)">Join</button>
            </li>
        </ul>
    </TenantLayout>
</template>
