<script setup>
import { useTenantNavActive } from '@/composables/useTenantNavActive';
import { Link } from '@inertiajs/vue3';

defineProps({
    title: { type: String, required: true },
    /** @type {{ label: string, href: string, badge?: string }[]} */
    items: { type: Array, default: () => [] },
    showNavText: { type: Boolean, default: true },
    first: { type: Boolean, default: false },
});

const { navActive } = useTenantNavActive();
</script>

<template>
    <div :class="['px-3', first ? 'pt-3' : 'pt-5']">
        <p
            class="px-2 py-1 text-[11px] uppercase tracking-[0.08em] text-neutral-400"
            :class="showNavText ? '' : 'lg:opacity-0'"
        >
            {{ title }}
        </p>
        <nav class="mt-1 space-y-0.5">
            <Link
                v-for="item in items"
                :key="`${item.href}-${item.label}`"
                :href="item.href"
                :class="[
                    'group flex items-center rounded-lg px-3 py-2 text-sm transition',
                    navActive(item.href)
                        ? 'bg-[#e66239]/10 font-medium text-[#e66239]'
                        : 'text-neutral-700 hover:bg-[#e66239]/10 hover:text-[#e66239]',
                ]"
            >
                <span class="inline-flex size-5 shrink-0 items-center justify-center">
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.9">
                        <path d="M4 6h16" />
                        <path d="M4 12h16" />
                        <path d="M4 18h16" />
                    </svg>
                </span>
                <span v-if="showNavText" class="flex min-w-0 flex-1 items-center justify-between gap-2 truncate">
                    <span class="truncate">{{ item.label }}</span>
                    <span
                        v-if="item.badge"
                        class="shrink-0 rounded-full bg-neutral-200 px-1.5 py-0.5 text-[10px] font-semibold text-neutral-700"
                    >
                        {{ item.badge }}
                    </span>
                </span>
            </Link>
        </nav>
    </div>
</template>
