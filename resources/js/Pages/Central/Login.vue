<script setup>
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/admin/login');
}
</script>

<template>
    <MarketingLayout>
        <Head title="Platform login" />
        <div class="mx-auto max-w-md rounded-2xl border border-white/10 bg-slate-900/70 p-8">
            <h1 class="text-2xl font-bold text-white">Super admin</h1>
            <p class="mt-2 text-sm text-slate-400">Central dashboard access only.</p>
            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="block text-xs font-medium text-slate-400">Email</label>
                    <input v-model="form.email" type="email" class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 text-white" />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-rose-400">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">Password</label>
                    <input v-model="form.password" type="password" class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 text-white" />
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-400">
                    <input v-model="form.remember" type="checkbox" class="rounded border-white/20" />
                    Remember me
                </label>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg bg-indigo-500 py-2 font-semibold text-white hover:bg-indigo-400 disabled:opacity-50"
                >
                    Sign in
                </button>
            </form>
        </div>
    </MarketingLayout>
</template>
