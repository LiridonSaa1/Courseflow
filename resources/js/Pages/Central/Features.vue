<script setup>
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <MarketingLayout>
        <Head title="Features" />
        <h1 class="text-3xl font-bold text-white">Everything in one stack</h1>
        <div class="mt-10 grid gap-6 md:grid-cols-2">
            <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-6">
                <h2 class="font-semibold text-indigo-300">Learning</h2>
                <p class="mt-2 text-slate-400">
                    Rich lessons (text, media, vocabulary, grammar), quiz engine with multiple question types, writing
                    with manual grading.
                </p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-6">
                <h2 class="font-semibold text-indigo-300">Operations</h2>
                <p class="mt-2 text-slate-400">
                    Students, teachers, classes, live session links, certificates with QR verification, and in-app
                    community threads.
                </p>
            </div>
        </div>
    </MarketingLayout>
</template>
