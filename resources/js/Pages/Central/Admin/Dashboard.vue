<script setup>
import AdminShellLayout from '@/Layouts/AdminShellLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: { type: Object, required: true },
    tenants: { type: Array, default: () => [] },
    chartSignups: { type: Array, default: () => [0, 0, 0, 0, 0, 0, 0] },
});

const palette = ['bg-blue-500', 'bg-violet-500', 'bg-orange-500', 'bg-rose-500', 'bg-emerald-500', 'bg-amber-500'];

function rowIconClass(i) {
    return palette[i % palette.length];
}

const dateSubtitle = computed(() => {
    const now = new Date();
    const end = now.getDate();
    const month = now.toLocaleString('en', { month: 'long' });
    const year = now.getFullYear();

    return `01 – ${end} ${month}, ${year}`;
});

const barMeta = computed(() => {
    const raw = props.chartSignups.length ? props.chartSignups : [0, 0, 0, 0, 0, 0, 0];
    const max = Math.max(...raw, 1);
    const highlightIdx = raw.indexOf(max);

    return raw.map((n, idx) => ({
        h: 32 + Math.round((n / max) * 80),
        strong: idx === highlightIdx && n > 0,
    }));
});

const planDistribution = computed(() => {
    const counts = {};
    for (const t of props.tenants || []) {
        const label = t.plan?.name ?? 'No plan';
        counts[label] = (counts[label] || 0) + 1;
    }
    const entries = Object.entries(counts).sort((a, b) => b[1] - a[1]);
    const max = entries.length ? Math.max(...entries.map(([, c]) => c)) : 1;

    return entries.map(([name, count]) => ({
        name,
        count,
        pct: Math.round((count / max) * 100),
    }));
});

function tenantTitle(t) {
    return t.course_name || t.domains?.[0]?.domain || t.id?.slice(0, 8) || 'Workspace';
}

function tenantSubtitle(t) {
    const email = t.central_owner?.email ?? t.centralOwner?.email ?? '—';
    const created = t.created_at ? formatRowTime(t.created_at) : '';

    return created ? `${created} · ${email}` : email;
}

function formatRowTime(iso) {
    try {
        const d = new Date(iso);

        return d.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
    } catch {
        return '';
    }
}

function startOfDay(d) {
    const x = new Date(d);

    return new Date(x.getFullYear(), x.getMonth(), x.getDate());
}

function formatGroupLabel(date) {
    if (!date) return 'Unknown date';
    const d = startOfDay(date);
    const today = startOfDay(new Date());
    const diffMs = today.getTime() - d.getTime();
    const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';

    return d.toLocaleString('en', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
}

function dayKey(iso) {
    if (!iso) return '';

    try {
        return new Date(iso).toISOString().slice(0, 10);
    } catch {
        return '';
    }
}

const tenantGroups = computed(() => {
    const list = props.tenants || [];
    if (!list.length) return [];

    const groups = [];
    let lastKey = null;

    for (const t of list) {
        const k = dayKey(t.created_at) || 'unknown';
        if (k !== lastKey) {
            lastKey = k;
            const d = t.created_at ? new Date(t.created_at) : null;
            groups.push({ label: formatGroupLabel(d), items: [] });
        }
        groups[groups.length - 1].items.push(t);
    }

    return groups;
});

function suspend(id) {
    router.post(`/admin/tenants/${id}/suspend`);
}

function activate(id) {
    router.post(`/admin/tenants/${id}/activate`);
}
</script>

<template>
    <AdminShellLayout>
        <Head title="Admin" />

        <div class="px-6 pb-10 pt-8 sm:px-9 sm:pt-10 lg:px-11 lg:pt-11">
            <!-- Header row — Figma community style -->
            <div class="flex flex-wrap items-start justify-between gap-5">
                <div>
                    <h1 class="text-[28px] font-bold leading-tight tracking-tight text-[#222222] sm:text-[30px]">Workspaces</h1>
                    <p class="mt-2 text-[15px] text-[#999999]">{{ dateSubtitle }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex -space-x-2.5">
                        <div
                            v-for="i in 4"
                            :key="i"
                            class="flex size-10 items-center justify-center rounded-full border-[3px] border-white bg-gradient-to-br from-indigo-400 to-violet-600 text-[11px] font-semibold text-white shadow-sm"
                        >
                            {{ String.fromCharCode(64 + i) }}
                        </div>
                    </div>
                    <button
                        type="button"
                        class="flex size-10 shrink-0 items-center justify-center rounded-full border-2 border-dashed border-[#dddddd] bg-white text-lg font-light text-[#bbbbbb] transition hover:border-[#cccccc] hover:text-[#999999]"
                        aria-label="Add"
                    >
                        +
                    </button>
                </div>
            </div>

            <!-- Bar strip — pale bars + royal blue highlight -->
            <div class="mt-10 flex h-[140px] items-end justify-between gap-1.5 px-0.5 sm:gap-2" aria-hidden="true">
                <div
                    v-for="(bar, idx) in barMeta"
                    :key="idx"
                    class="flex-1 rounded-t-[6px] transition-all duration-500"
                    :class="bar.strong ? 'bg-[#1861ff]' : 'bg-[#dbeafe]'"
                    :style="{ height: bar.h + 'px', opacity: bar.strong ? 1 : 0.65 }"
                />
            </div>

            <!-- Grouped list — Today / dated sections -->
            <section id="workspaces" class="mt-12 scroll-mt-10">
                <template v-for="(group, gi) in tenantGroups" :key="gi">
                    <div class="flex items-center justify-between border-b border-transparent pb-3 pt-2 first:pt-0">
                        <h2 class="text-[15px] font-bold text-[#222222]">{{ group.label }}</h2>
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-[#bbbbbb] transition hover:bg-neutral-100 hover:text-[#666666]"
                            aria-label="More"
                        >
                            <span class="block text-xl leading-none tracking-tight">⋯</span>
                        </button>
                    </div>
                    <ul class="border-t border-[#f0f0f0]">
                        <li
                            v-for="(t, ti) in group.items"
                            :key="t.id"
                            class="flex items-center gap-4 border-b border-[#f5f5f5] py-5 last:border-b-0"
                        >
                            <div
                                class="flex size-[52px] shrink-0 items-center justify-center rounded-full text-white shadow-[0_4px_12px_rgba(0,0,0,0.12)]"
                                :class="rowIconClass(gi * 20 + ti)"
                            >
                                <svg class="size-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[16px] font-bold text-[#222222]">{{ tenantTitle(t) }}</p>
                                <p class="mt-1 text-[14px] leading-snug text-[#999999]">
                                    {{ tenantSubtitle(t) }}
                                    <span v-if="t.domains?.[0]?.domain"> · {{ t.domains[0].domain }}</span>
                                </p>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-2">
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="t.suspended_at ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700'"
                                >
                                    {{ t.suspended_at ? 'Suspended' : 'Active' }}
                                </span>
                                <button
                                    v-if="!t.suspended_at"
                                    type="button"
                                    class="text-xs font-semibold text-rose-600 hover:text-rose-700"
                                    @click="suspend(t.id)"
                                >
                                    Suspend
                                </button>
                                <button
                                    v-else
                                    type="button"
                                    class="text-xs font-semibold text-emerald-600 hover:text-emerald-700"
                                    @click="activate(t.id)"
                                >
                                    Activate
                                </button>
                            </div>
                        </li>
                    </ul>
                </template>
                <p v-if="!tenants.length" class="border-t border-[#f0f0f0] py-16 text-center text-[15px] text-[#999999]">
                    No workspaces yet.
                </p>
            </section>

            <!-- Compact stats — under list -->
            <div class="mt-12 grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-[#f0f0f0] bg-[#fafafa] px-4 py-3.5">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#999999]">Tenants</p>
                    <p class="mt-1 text-xl font-bold text-[#222222]">{{ stats.tenants }}</p>
                </div>
                <div class="rounded-2xl border border-[#f0f0f0] bg-[#fafafa] px-4 py-3.5">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#999999]">Active</p>
                    <p class="mt-1 text-xl font-bold text-emerald-600">{{ stats.activeTenants }}</p>
                </div>
                <div class="rounded-2xl border border-[#f0f0f0] bg-[#fafafa] px-4 py-3.5">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#999999]">Revenue (¢)</p>
                    <p class="mt-1 text-xl font-bold text-[#1861ff]">{{ stats.revenue_cents }}</p>
                </div>
            </div>
        </div>

        <template #aside>
            <div class="px-6 py-9 sm:px-8 sm:py-10">
                <h2 class="text-[17px] font-bold leading-snug text-[#222222]">Where do plans go?</h2>
                <p class="mt-1.5 text-[13px] leading-relaxed text-[#999999]">Share among your {{ stats.tenants }} workspaces (sample of 50).</p>

                <ul class="mt-8 space-y-6">
                    <li v-for="row in planDistribution" :key="row.name">
                        <div class="flex items-baseline justify-between gap-2 text-[14px]">
                            <span class="font-semibold text-[#222222]">{{ row.name }}</span>
                            <span class="font-bold text-[#222222]">{{ row.count }}</span>
                        </div>
                        <div class="mt-2.5 h-1 overflow-hidden rounded-full bg-[#e8e8ed]">
                            <div class="h-full rounded-full bg-emerald-500 transition-all" :style="{ width: row.pct + '%' }" />
                        </div>
                    </li>
                    <li v-if="!planDistribution.length" class="text-[14px] text-[#999999]">No plan data.</li>
                </ul>

                <div class="mt-11 overflow-hidden rounded-[20px] bg-[#e8eef6] p-6 shadow-sm">
                    <div class="mb-5 flex justify-center text-[#a8b8cc]">
                        <svg class="size-[72px]" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="12" y="20" width="40" height="36" rx="2" class="fill-[#c5d3e3]" />
                            <rect x="16" y="16" width="12" height="8" rx="1" class="fill-[#9dafc6]" />
                            <path d="M28 12c0-2 2-4 4-4s4 2 4 4v4H28v-4z" class="fill-emerald-600" />
                        </svg>
                    </div>
                    <h3 class="text-center text-[15px] font-bold text-[#222222]">Save more time</h3>
                    <p class="mx-auto mt-2 max-w-[220px] text-center text-[12px] leading-relaxed text-[#777777]">
                        Ensure MySQL users can create databases for new workspaces, and set Stripe price IDs for checkout.
                    </p>
                    <a
                        href="/"
                        class="mt-5 flex w-full items-center justify-center rounded-2xl bg-[#111111] py-3.5 text-[11px] font-bold uppercase tracking-[0.12em] text-white transition hover:bg-black"
                    >
                        View tips
                    </a>
                </div>
            </div>
        </template>
    </AdminShellLayout>
</template>
