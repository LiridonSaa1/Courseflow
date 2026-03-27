<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: { type: String, default: '' },
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950">
        <header class="border-b border-white/10 bg-slate-950/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                <Link href="/" class="text-xl font-semibold tracking-tight text-white">Courseflow</Link>
                <nav class="flex items-center gap-6 text-sm text-slate-300">
                    <Link href="/features" class="hover:text-white">Features</Link>
                    <Link href="/pricing" class="hover:text-white">Pricing</Link>
                    <Link href="/contact" class="hover:text-white">Contact</Link>
                    <Link
                        href="/subscribe"
                        class="rounded-lg bg-indigo-500 px-4 py-2 font-medium text-white hover:bg-indigo-400"
                    >
                        Start free trial
                    </Link>
                    <Link href="/admin/login" class="text-slate-400 hover:text-white">Platform login</Link>
                </nav>
            </div>
        </header>
        <main class="mx-auto max-w-6xl px-4 py-12">
            <slot />
        </main>
    </div>
</template>
