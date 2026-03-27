<script setup>
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <MarketingLayout>
        <Head title="Language learning platform for schools" />
        <section class="grid gap-12 md:grid-cols-2 md:items-center">
            <div>
                <p class="mb-2 text-sm font-medium uppercase tracking-widest text-indigo-400">Courseflow</p>
                <h1 class="text-4xl font-bold tracking-tight text-white md:text-5xl">
                    Multi-tenant LMS for modern language schools.
                </h1>
                <p class="mt-4 text-lg text-slate-400">
                    Launch branded academies with courses, quizzes, speaking practice, analytics, and billing — all
                    isolated per tenant.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <Link
                        href="/subscribe"
                        class="rounded-xl bg-indigo-500 px-6 py-3 font-semibold text-white shadow-lg shadow-indigo-500/30 hover:bg-indigo-400"
                    >
                        Create your workspace
                    </Link>
                    <Link href="/pricing" class="rounded-xl border border-white/20 px-6 py-3 font-medium text-white hover:bg-white/5">
                        View pricing
                    </Link>
                </div>
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-900/80 p-8 shadow-2xl">
                <ul class="space-y-4 text-slate-300">
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400" />
                        Separate database per school — true isolation.
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-sky-400" />
                        Subdomain routing with Stancl Tenancy.
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-amber-400" />
                        Lessons, quizzes, speaking hooks, gamification-ready.
                    </li>
                </ul>
            </div>
        </section>
    </MarketingLayout>
</template>
