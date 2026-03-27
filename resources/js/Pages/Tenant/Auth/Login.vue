<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login');
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-slate-950 px-4">
        <Head title="Log in" />
        <div class="w-full max-w-md rounded-2xl border border-white/10 bg-slate-900/80 p-8">
            <h1 class="text-2xl font-bold text-white">Welcome back</h1>
            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="text-xs text-slate-400">Email</label>
                    <input v-model="form.email" type="email" class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 text-neutral-900" />
                    <p v-if="form.errors.email" class="text-sm text-rose-400">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label class="text-xs text-slate-400">Password</label>
                    <input v-model="form.password" type="password" class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 text-neutral-900" />
                </div>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg bg-indigo-500 py-2 font-semibold text-white hover:bg-indigo-400 disabled:opacity-50"
                >
                    Sign in
                </button>
            </form>
        </div>
    </div>
</template>
