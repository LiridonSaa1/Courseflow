<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({ teachers: { type: Array, default: () => [] } });

const form = useForm({ name: '', email: '', password: '', title: '' });

function submit() {
    form.post('/teachers');
}
</script>

<template>
    <TenantLayout>
        <Head title="Teachers" />
        <h1 class="text-2xl font-bold text-neutral-900">Teachers</h1>
        <form class="mt-6 max-w-md space-y-3 rounded-xl border border-neutral-200 bg-neutral-50 p-4" @submit.prevent="submit">
            <input v-model="form.name" placeholder="Name" class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900" />
            <input v-model="form.email" type="email" placeholder="Email" class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900" />
            <input v-model="form.password" type="password" placeholder="Password" class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900" />
            <input v-model="form.title" placeholder="Title" class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900" />
            <button type="submit" class="rounded-lg bg-indigo-500 px-3 py-2 text-sm text-white">Add teacher</button>
        </form>
        <ul class="mt-6 space-y-2 text-sm text-neutral-600">
            <li v-for="t in teachers" :key="t.id">{{ t.user?.name }} · {{ t.title }}</li>
        </ul>
    </TenantLayout>
</template>
