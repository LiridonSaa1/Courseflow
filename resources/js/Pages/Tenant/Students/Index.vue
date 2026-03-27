<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({ students: { type: Array, default: () => [] } });

const inviteForm = useForm({ email: '', name: '' });
const createForm = useForm({ name: '', email: '', password: '' });

function sendInvite() {
    inviteForm.post('/invites');
}

function createStudent() {
    createForm.post('/students');
}
</script>

<template>
    <TenantLayout>
        <Head title="Students" />
        <h1 class="text-2xl font-bold text-neutral-900">Students</h1>
        <div class="mt-8 grid gap-8 md:grid-cols-2">
            <form class="space-y-3 rounded-xl border border-neutral-200 bg-neutral-50 p-4" @submit.prevent="createStudent">
                <h2 class="font-semibold text-neutral-900">Create account</h2>
                <input v-model="createForm.name" placeholder="Name" class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900" />
                <input v-model="createForm.email" type="email" placeholder="Email" class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900" />
                <input v-model="createForm.password" type="password" placeholder="Password" class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900" />
                <button type="submit" class="rounded-lg bg-indigo-500 px-3 py-2 text-sm text-white">Add</button>
            </form>
            <form class="space-y-3 rounded-xl border border-neutral-200 bg-neutral-50 p-4" @submit.prevent="sendInvite">
                <h2 class="font-semibold text-neutral-900">Invite by email</h2>
                <input v-model="inviteForm.name" placeholder="Name" class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900" />
                <input v-model="inviteForm.email" type="email" placeholder="Email" class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-neutral-900" />
                <button type="submit" class="rounded-lg border border-indigo-500/50 px-3 py-2 text-sm text-indigo-300">Send invite</button>
            </form>
        </div>
        <ul class="mt-8 space-y-2 text-sm text-neutral-600">
            <li v-for="s in students" :key="s.id">{{ s.user?.name }} · {{ s.user?.email }}</li>
        </ul>
    </TenantLayout>
</template>
