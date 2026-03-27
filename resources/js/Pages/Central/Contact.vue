<script setup>
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <MarketingLayout>
        <Head title="Contact" />
        <h1 class="text-3xl font-bold text-white">Contact</h1>
        <p class="mt-4 max-w-xl text-slate-400">
            Reach your engineering team or support from your deployment configuration — wire this form to your CRM or
            support desk.
        </p>
        <form class="mt-8 max-w-md space-y-4" onsubmit="event.preventDefault()">
            <input type="email" placeholder="Email" class="w-full rounded-lg border border-white/10 bg-slate-900 px-3 py-2 text-white" />
            <textarea
                rows="4"
                placeholder="Message"
                class="w-full rounded-lg border border-white/10 bg-slate-900 px-3 py-2 text-white"
            />
            <button type="submit" class="rounded-lg bg-indigo-500 px-4 py-2 font-medium text-white">Send</button>
        </form>
    </MarketingLayout>
</template>
