<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    title: { type: String, default: '' },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const urlPath = computed(() => {
    const u = page.url || '';

    return u.split(/[?#]/)[0] || '/';
});

const isDashboardRoute = computed(() => {
    const p = urlPath.value;

    return p === '/dashboard' || p === '/admin' || p === '/admin/';
});

const initials = computed(() => {
    const n = user.value?.name || user.value?.email || '?';
    const parts = String(n).trim().split(/\s+/);

    return parts.length >= 2
        ? (parts[0][0] + parts[1][0]).toUpperCase()
        : String(n).slice(0, 2).toUpperCase();
});

function logout() {
    router.post('/admin/logout');
}
</script>

<template>
    <div class="min-h-screen bg-[#ebebef] px-3 py-4 sm:px-5 sm:py-6 md:px-8 md:py-10">
        <div
            class="mx-auto flex min-h-[calc(100vh-2rem)] max-w-[1440px] flex-col overflow-hidden rounded-[2rem] bg-[#f6f6f8] shadow-[0_25px_60px_-15px_rgba(0,0,0,0.18)] ring-1 ring-black/5 md:min-h-[calc(100vh-5rem)] md:flex-row"
        >
            <!-- Sidebar — Figma-style near-black -->
            <aside
                class="flex w-full shrink-0 flex-col border-b border-white/5 bg-[#111111] md:w-[248px] md:border-b-0 md:border-r md:border-white/5"
            >
                <div class="flex items-start gap-3 px-5 pb-8 pt-9 md:px-7">
                    <div class="relative shrink-0">
                        <div
                            class="flex size-[52px] items-center justify-center rounded-[14px] bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-semibold text-white shadow-inner"
                        >
                            {{ initials }}
                        </div>
                        <span
                            class="absolute -right-0.5 -top-0.5 flex size-5 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white shadow-sm"
                            aria-hidden="true"
                        >
                            4
                        </span>
                    </div>
                    <div class="min-w-0 flex-1 pt-0.5">
                        <p class="truncate text-[15px] font-semibold leading-tight text-white">
                            {{ user?.name || 'Admin' }}
                        </p>
                        <p class="mt-1 truncate text-[13px] leading-tight text-[#999999]">{{ user?.email }}</p>
                    </div>
                </div>

                <nav class="flex flex-row gap-0.5 overflow-x-auto px-3 pb-5 md:flex-col md:gap-0 md:px-4 md:pb-10 md:pr-5">
                    <Link
                        href="/dashboard"
                        class="shrink-0 rounded-xl px-3.5 py-2.5 text-[14px] transition-colors md:px-3.5 md:py-3"
                        :class="
                            isDashboardRoute
                                ? 'font-bold text-white md:bg-white/[0.08]'
                                : 'font-medium text-[#999999] hover:text-white md:hover:bg-white/5'
                        "
                    >
                        Dashboard
                    </Link>
                    <a
                        href="#workspaces"
                        class="shrink-0 rounded-xl px-3.5 py-2.5 text-[14px] font-medium text-[#999999] transition-colors hover:text-white md:px-3.5 md:py-3 md:hover:bg-white/5"
                    >
                        Workspaces
                    </a>
                    <span
                        class="hidden shrink-0 rounded-xl px-3.5 py-3 text-[14px] font-medium text-[#666666] md:block"
                        title="Coming soon"
                    >
                        Plans
                    </span>
                    <span
                        class="hidden shrink-0 rounded-xl px-3.5 py-3 text-[14px] font-medium text-[#666666] md:block"
                        title="Coming soon"
                    >
                        Settings
                    </span>
                </nav>

                <div class="mt-auto hidden border-t border-white/[0.06] px-5 py-5 md:block">
                    <button
                        type="button"
                        class="w-full rounded-xl px-3 py-2.5 text-left text-[13px] font-medium text-[#999999] transition hover:bg-white/[0.06] hover:text-white"
                        @click="logout"
                    >
                        Sign out
                    </button>
                </div>
            </aside>

            <!-- Main (white) + right rail -->
            <div class="flex min-h-0 min-w-0 flex-1 flex-col bg-white lg:flex-row">
                <main class="min-w-0 flex-1 bg-white lg:rounded-bl-none lg:rounded-tl-none">
                    <slot />
                </main>
                <aside
                    class="w-full shrink-0 border-t border-[#e8e8ed] bg-[#f9f9fb] lg:w-[300px] lg:border-l lg:border-t-0"
                >
                    <slot name="aside" />
                </aside>
            </div>
        </div>

        <button
            type="button"
            class="mt-4 w-full rounded-xl py-2.5 text-center text-[13px] font-medium text-neutral-500 hover:text-neutral-800 md:hidden"
            @click="logout"
        >
            Sign out
        </button>
    </div>
</template>
