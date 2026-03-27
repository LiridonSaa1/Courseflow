<script setup>
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <MarketingLayout>
        <Head title="Pricing" />
        <h1 class="text-3xl font-bold text-white">Simple plans</h1>
        <p class="mt-2 text-slate-400">Basic, Pro, and School — monthly or yearly via Stripe when configured.</p>
        <div class="mt-10 grid gap-6 md:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-6">
                <h2 class="text-xl font-semibold text-white">Basic</h2>
                <p class="mt-2 text-3xl font-bold text-indigo-400">$19<span class="text-base font-normal text-slate-500">/mo</span></p>
                <p class="mt-4 text-sm text-slate-400">Solo teachers, up to 50 learners.</p>
            </div>
            <div class="rounded-2xl border border-indigo-500/40 bg-indigo-500/10 p-6 ring-1 ring-indigo-500/30">
                <h2 class="text-xl font-semibold text-white">Pro</h2>
                <p class="mt-2 text-3xl font-bold text-indigo-300">$49<span class="text-base font-normal text-slate-500">/mo</span></p>
                <p class="mt-4 text-sm text-slate-300">Growing programs, deeper analytics.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-6">
                <h2 class="text-xl font-semibold text-white">School</h2>
                <p class="mt-2 text-3xl font-bold text-indigo-400">$149<span class="text-base font-normal text-slate-500">/mo</span></p>
                <p class="mt-4 text-sm text-slate-400">Institutions at scale.</p>
            </div>
        </div>
        <Link href="/subscribe" class="mt-10 inline-block rounded-xl bg-white px-6 py-3 font-semibold text-slate-900 hover:bg-slate-200">
            Start a trial
        </Link>
    </MarketingLayout>
</template>
