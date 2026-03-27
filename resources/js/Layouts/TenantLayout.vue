<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, useSlots } from 'vue';

const page = usePage();
const slots = useSlots();
const user = computed(() => page.props.auth?.user);
const workspace = computed(() => page.props.tenantContext?.workspace ?? 'Workspace');
const logoutForm = useForm({});

const urlPath = computed(() => String(page.url || '').split('?')[0].split('#')[0] || '');

function navActive(href) {
    const path = urlPath.value || '';

    if (href === '/dashboard') {
        return path === '/dashboard' || path === '/';
    }

    return path === href || path.startsWith(`${href}/`);
}

const initials = computed(() => {
    const n = user.value?.name || user.value?.email || '?';
    const parts = String(n).trim().split(/\s+/);

    return parts.length >= 2
        ? (parts[0][0] + parts[1][0]).toUpperCase()
        : String(n).slice(0, 2).toUpperCase();
});

function logout() {
    logoutForm.post('/logout');
}

const nav = [
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Courses', href: '/courses' },
    { label: 'Students', href: '/students' },
    { label: 'Teachers', href: '/teachers' },
    { label: 'Analytics', href: '/analytics' },
    { label: 'Live classes', href: '/live-sessions' },
    { label: 'Community', href: '/community' },
    { label: 'Certificates', href: '/certificates' },
];

const hasAside = computed(() => !!slots.aside);
</script>

<template>
    <div class="min-h-screen bg-neutral-100 px-3 py-4 sm:px-5 sm:py-6 md:px-8 md:py-8">
        <div
            class="mx-auto flex min-h-[calc(100vh-2rem)] max-w-[1440px] flex-col overflow-hidden rounded-[1.75rem] bg-white shadow-xl ring-1 ring-black/5 md:min-h-[calc(100vh-4rem)] md:flex-row"
        >
            <aside
                class="flex w-full shrink-0 flex-col border-b border-white/5 bg-[#1a1a1a] md:w-60 md:border-b-0 md:border-r md:border-white/5"
            >
                <div class="px-5 pb-2 pt-6 md:px-6">
                    <p class="truncate text-xs font-medium uppercase tracking-wide text-neutral-500">{{ workspace }}</p>
                </div>

                <div class="flex items-start gap-3 px-5 pb-6 md:px-6">
                    <div class="relative shrink-0">
                        <div
                            class="flex size-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-semibold text-white"
                        >
                            {{ initials }}
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-white">{{ user?.name || 'User' }}</p>
                        <p class="truncate text-xs text-neutral-400">{{ user?.email }}</p>
                        <p v-if="user?.roles?.length" class="mt-1 truncate text-[10px] text-neutral-500">
                            {{ user.roles.join(', ') }}
                        </p>
                    </div>
                </div>

                <nav class="flex flex-row gap-1 overflow-x-auto px-3 pb-4 md:flex-col md:px-4 md:pb-6">
                    <Link
                        v-for="item in nav"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'shrink-0 rounded-lg px-3 py-2.5 text-sm md:px-3.5',
                            navActive(item.href)
                                ? 'bg-white/10 font-semibold text-white'
                                : 'font-medium text-neutral-400 hover:bg-white/5 hover:text-white',
                        ]"
                    >
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="mt-auto hidden border-t border-white/5 px-4 py-4 md:block">
                    <button
                        type="button"
                        class="w-full rounded-lg px-3 py-2 text-left text-xs font-medium text-neutral-400 transition hover:bg-white/5 hover:text-white"
                        @click="logout"
                    >
                        Sign out
                    </button>
                </div>
            </aside>

            <div class="flex min-h-0 min-w-0 flex-1 flex-col lg:flex-row">
                <main class="min-w-0 flex-1 overflow-auto bg-white text-neutral-900">
                    <div class="p-4 md:p-8">
                        <slot />
                    </div>
                </main>
                <aside
                    v-if="hasAside"
                    class="w-full shrink-0 border-t border-neutral-200/80 bg-neutral-50 text-neutral-900 lg:w-80 lg:border-l lg:border-t-0"
                >
                    <div class="p-4 md:p-8">
                        <slot name="aside" />
                    </div>
                </aside>
            </div>
        </div>

        <button
            type="button"
            class="mt-4 w-full rounded-lg py-2 text-center text-xs font-medium text-neutral-500 hover:text-neutral-800 md:hidden"
            @click="logout"
        >
            Sign out
        </button>
    </div>
</template>
