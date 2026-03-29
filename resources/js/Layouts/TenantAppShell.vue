<script setup>
import { useTenantNavActive } from '@/composables/useTenantNavActive';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, useSlots, watch } from 'vue';

const { navActive } = useTenantNavActive();

const page = usePage();
const slots = useSlots();
const logoutForm = useForm({});

const user = computed(() => page.props.auth?.user);
const workspace = computed(() => page.props.tenantContext?.workspace ?? 'Workspace');
const hasAside = computed(() => !!slots.aside);

const collapsed = ref(false);
const mobileOpen = ref(false);
const viewportWidth = ref(0);
const showNavText = computed(() => !collapsed.value || viewportWidth.value < 1024);

const displayName = computed(() => user.value?.name || 'Tenant User');
const username = computed(() => {
    const email = user.value?.email || '';
    if (!email.includes('@')) {
        return `@${String(displayName.value || '').toLowerCase().replace(/\s+/g, '') || 'user'}`;
    }

    return `@${email.split('@')[0]}`;
});

function closeMobile() {
    mobileOpen.value = false;
}

function toggleSidebar() {
    if (window.innerWidth < 1024) {
        mobileOpen.value = !mobileOpen.value;
        return;
    }

    collapsed.value = !collapsed.value;
}

function onResize() {
    viewportWidth.value = window.innerWidth;

    if (window.innerWidth >= 1024) {
        mobileOpen.value = false;
    }
}

function logout() {
    logoutForm.post('/logout');
}

watch(
    () => page.url,
    () => {
        mobileOpen.value = false;
    },
);

watch(collapsed, (value) => {
    try {
        localStorage.setItem('tenant.sidebar.collapsed', value ? '1' : '0');
    } catch {
        // Ignore localStorage failures.
    }
});

onMounted(() => {
    try {
        collapsed.value = localStorage.getItem('tenant.sidebar.collapsed') === '1';
    } catch {
        collapsed.value = false;
    }

    onResize();
    window.addEventListener('resize', onResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', onResize);
});
</script>

<template>
    <div class="tenant-inapp min-h-screen bg-[#f6f7fb] text-[#171717]">
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-30 bg-black/45 backdrop-blur-[1px] lg:hidden"
            @click="closeMobile"
        />

        <header
            class="fixed top-0 z-auto flex h-[60px] items-center justify-between border-b border-neutral-200 bg-white px-3 transition-all duration-300 sm:px-4"
            :class="[collapsed ? 'lg:left-[60px]' : 'lg:left-[240px]', 'left-0 right-0']"
        >
            <button
                type="button"
                class="inline-flex size-8 items-center justify-center rounded-md border border-neutral-200 bg-neutral-50 text-neutral-600 transition hover:bg-neutral-100"
                aria-label="Toggle sidebar"
                @click="toggleSidebar"
            >
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 6h16" />
                    <path d="M4 12h16" />
                    <path d="M4 18h16" />
                </svg>
            </button>

            <div class="flex items-center gap-2 sm:gap-3">
                <button
                    type="button"
                    class="relative inline-flex size-8 items-center justify-center rounded-full border border-neutral-200 bg-neutral-50 text-neutral-600 transition hover:bg-neutral-100"
                    aria-label="Notifications"
                >
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M10 18a2 2 0 0 0 4 0" />
                        <path d="M18 16v-5a6 6 0 1 0-12 0v5l-2 2h16l-2-2Z" />
                    </svg>
                    <span class="absolute -right-1 -top-1 rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-semibold text-white">2</span>
                </button>

                <div class="hidden items-center gap-2 sm:flex">
                    <img :src="'/inapp/images/avatar-1.jpg'" alt="User avatar" class="size-8 rounded-full object-cover" />
                    <div class="text-right">
                        <p class="text-sm font-semibold leading-none text-neutral-900">{{ displayName }}</p>
                        <p class="mt-1 text-xs text-neutral-500">{{ username }}</p>
                    </div>
                </div>
            </div>
        </header>

        <aside
            class="fixed left-0 top-0 z-40 h-screen border-r border-neutral-200 bg-white pt-[60px] transition-all duration-300"
            :class="[
                collapsed ? 'w-[60px] lg:w-[60px]' : 'w-[240px]',
                mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            ]"
        >
            <div class="absolute left-0 top-0 flex h-[60px] w-full items-center border-b border-neutral-200 px-4">
                <Link href="/dashboard" class="inline-flex items-center gap-2">
                    <img :src="'/inapp/images/logo.svg'" alt="Inapp logo" class="h-6 w-auto shrink-0" />
                    <span v-if="showNavText" class="text-sm font-semibold tracking-wide text-neutral-900">{{ workspace }}</span>
                </Link>
            </div>

            <div class="h-full overflow-y-auto pb-6">
                <slot name="sidebar" :show-nav-text="showNavText" :collapsed="collapsed" />

                <div class="px-3 pt-5">
                    <p
                        class="px-2 py-1 text-[11px] uppercase tracking-[0.08em] text-neutral-400"
                        :class="collapsed ? 'lg:opacity-0' : ''"
                    >
                        Account
                    </p>
                    <nav class="mt-1 space-y-0.5">
                        <Link
                            href="/dashboard"
                            :class="[
                                'group flex items-center rounded-lg px-3 py-2 text-sm transition',
                                navActive('/dashboard')
                                    ? 'bg-[#e66239]/10 font-medium text-[#e66239]'
                                    : 'text-neutral-700 hover:bg-[#e66239]/10 hover:text-[#e66239]',
                            ]"
                        >
                            <span class="inline-flex size-5 shrink-0 items-center justify-center">
                                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.9">
                                    <circle cx="12" cy="8" r="3.5" />
                                    <path d="M4 20a8 8 0 0 1 16 0" />
                                </svg>
                            </span>
                            <span v-if="showNavText" class="truncate">Profile</span>
                        </Link>
                        <Link
                            href="/coming-soon/security"
                            :class="[
                                'group flex items-center rounded-lg px-3 py-2 text-sm transition',
                                navActive('/coming-soon/security')
                                    ? 'bg-[#e66239]/10 font-medium text-[#e66239]'
                                    : 'text-neutral-700 hover:bg-[#e66239]/10 hover:text-[#e66239]',
                            ]"
                        >
                            <span class="inline-flex size-5 shrink-0 items-center justify-center">
                                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.9">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                                </svg>
                            </span>
                            <span v-if="showNavText" class="truncate">Security</span>
                        </Link>
                    </nav>
                </div>

                <div class="mt-5 px-3">
                    <button
                        type="button"
                        class="flex w-full items-center rounded-lg px-3 py-2 text-left text-sm text-neutral-700 transition hover:bg-red-50 hover:text-red-600"
                        @click="logout"
                    >
                        <span class="inline-flex size-5 shrink-0 items-center justify-center">
                            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.9">
                                <path d="M15 16l4-4-4-4" />
                                <path d="M19 12H9" />
                                <path d="M13 20H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h7" />
                            </svg>
                        </span>
                        <span v-if="showNavText" class="truncate">Sign out</span>
                    </button>
                </div>
            </div>
        </aside>

        <main
            class="pt-[76px] transition-all duration-300"
            :class="[collapsed ? 'lg:ml-[60px]' : 'lg:ml-[240px]']"
        >
            <div class="px-4 pb-8 sm:px-5 lg:px-6">
                <div v-if="hasAside" class="grid gap-4 2xl:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="min-w-0">
                        <slot />
                    </div>
                    <aside class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm">
                        <slot name="aside" />
                    </aside>
                </div>
                <slot v-else />
            </div>
        </main>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

.tenant-inapp {
    font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
}
</style>
