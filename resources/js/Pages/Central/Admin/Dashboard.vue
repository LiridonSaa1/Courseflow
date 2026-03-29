<script setup>
import AdminShellLayout from '@/Layouts/AdminShellLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: { type: Object, required: true },
    tenants: { type: Array, default: () => [] },
    chartSignups: { type: Array, default: () => [0, 0, 0, 0, 0, 0, 0] },
});

const avatarColors = ['primary', 'secondary', 'teal', 'orange', 'pink', 'amber'];

function rowAvatarColor(i) {
    return avatarColors[i % avatarColors.length];
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

        <v-container class="px-6 pb-10 pt-8 sm:px-9 sm:pt-10 lg:px-11 lg:pt-11" fluid>
            <v-row align="start" class="mb-2" justify="space-between">
                <v-col cols="12" md="auto">
                    <h1 class="text-h4 font-weight-bold text-high-emphasis">Workspaces</h1>
                    <p class="mt-2 text-body-1 text-medium-emphasis">{{ dateSubtitle }}</p>
                </v-col>
                <v-col class="d-flex align-center ga-2" cols="12" md="auto">
                    <v-avatar-group class="me-1">
                        <v-avatar
                            v-for="i in 4"
                            :key="i"
                            class="border-md border-surface"
                            color="indigo-lighten-1"
                            size="40"
                        >
                            <span class="text-caption font-weight-semibold">{{ String.fromCharCode(64 + i) }}</span>
                        </v-avatar>
                    </v-avatar-group>
                    <v-btn aria-label="Add" color="grey-lighten-3" icon variant="flat">
                        <v-icon color="grey">mdi-plus</v-icon>
                    </v-btn>
                </v-col>
            </v-row>

            <div class="mt-10 d-flex align-end ga-2 px-1" style="height: 140px" aria-hidden="true">
                <v-sheet
                    v-for="(bar, idx) in barMeta"
                    :key="idx"
                    class="flex-grow-1 rounded-ts-sm transition-swing"
                    :color="bar.strong ? 'primary' : 'blue-lighten-5'"
                    :height="bar.h"
                    :style="{ opacity: bar.strong ? 1 : 0.65 }"
                    rounded="0"
                />
            </div>

            <section id="workspaces" class="mt-12">
                <template v-for="(group, gi) in tenantGroups" :key="gi">
                    <div class="d-flex align-center justify-space-between pb-3 pt-2">
                        <h2 class="text-subtitle-1 font-weight-bold text-high-emphasis">{{ group.label }}</h2>
                        <v-btn aria-label="More" color="grey" icon size="small" variant="text">
                            <v-icon>mdi-dots-horizontal</v-icon>
                        </v-btn>
                    </div>
                    <v-list bg-color="transparent" class="pa-0" lines="three">
                        <v-list-item
                            v-for="(t, ti) in group.items"
                            :key="t.id"
                            class="border-b px-0 py-4"
                            rounded="0"
                        >
                            <template #prepend>
                                <v-avatar
                                    :color="rowAvatarColor(gi * 20 + ti)"
                                    size="52"
                                    class="elevation-2"
                                >
                                    <v-icon color="white" icon="mdi-office-building-outline" size="22" />
                                </v-avatar>
                            </template>

                            <v-list-item-title class="text-body-1 font-weight-bold">
                                {{ tenantTitle(t) }}
                            </v-list-item-title>
                            <v-list-item-subtitle class="text-wrap mt-1">
                                {{ tenantSubtitle(t) }}
                                <span v-if="t.domains?.[0]?.domain"> · {{ t.domains[0].domain }}</span>
                            </v-list-item-subtitle>

                            <template #append>
                                <div class="d-flex flex-column align-end ga-2">
                                    <v-chip
                                        :color="t.suspended_at ? 'error' : 'success'"
                                        density="comfortable"
                                        size="small"
                                        variant="tonal"
                                    >
                                        {{ t.suspended_at ? 'Suspended' : 'Active' }}
                                    </v-chip>
                                    <v-btn
                                        v-if="!t.suspended_at"
                                        color="error"
                                        size="small"
                                        variant="text"
                                        @click="suspend(t.id)"
                                    >
                                        Suspend
                                    </v-btn>
                                    <v-btn
                                        v-else
                                        color="success"
                                        size="small"
                                        variant="text"
                                        @click="activate(t.id)"
                                    >
                                        Activate
                                    </v-btn>
                                </div>
                            </template>
                        </v-list-item>
                    </v-list>
                </template>
                <v-sheet
                    v-if="!tenants.length"
                    border="t"
                    class="py-16 text-center text-body-1 text-medium-emphasis"
                    color="transparent"
                >
                    No workspaces yet.
                </v-sheet>
            </section>

            <v-row class="mt-12" dense>
                <v-col cols="12" sm="4">
                    <v-card border="thin" class="bg-grey-lighten-4" flat rounded="xl">
                        <v-card-text>
                            <p class="text-caption font-weight-semibold text-uppercase text-medium-emphasis">Tenants</p>
                            <p class="text-h5 font-weight-bold mt-1">{{ stats.tenants }}</p>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col cols="12" sm="4">
                    <v-card border="thin" class="bg-grey-lighten-4" flat rounded="xl">
                        <v-card-text>
                            <p class="text-caption font-weight-semibold text-uppercase text-medium-emphasis">Active</p>
                            <p class="text-h5 font-weight-bold mt-1 text-success">{{ stats.activeTenants }}</p>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col cols="12" sm="4">
                    <v-card border="thin" class="bg-grey-lighten-4" flat rounded="xl">
                        <v-card-text>
                            <p class="text-caption font-weight-semibold text-uppercase text-medium-emphasis">
                                Revenue (¢)
                            </p>
                            <p class="text-h5 font-weight-bold mt-1 text-primary">{{ stats.revenue_cents }}</p>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>

        <template #aside>
            <v-container class="px-6 py-9 sm:px-8 sm:py-10" fluid>
                <h2 class="text-h6 font-weight-bold text-high-emphasis">Where do plans go?</h2>
                <p class="mt-2 text-body-2 text-medium-emphasis">
                    Share among your {{ stats.tenants }} workspaces (sample of 50).
                </p>

                <v-list bg-color="transparent" class="mt-8 pa-0">
                    <v-list-item v-for="row in planDistribution" :key="row.name" class="px-0 py-3">
                        <div class="d-flex align-baseline justify-space-between ga-2 w-100">
                            <span class="text-body-2 font-weight-semibold">{{ row.name }}</span>
                            <span class="text-body-2 font-weight-bold">{{ row.count }}</span>
                        </div>
                        <v-progress-linear
                            class="mt-3 rounded-pill"
                            color="success"
                            height="4"
                            :model-value="row.pct"
                            rounded
                        />
                    </v-list-item>
                    <v-list-item v-if="!planDistribution.length" class="px-0 text-body-2 text-medium-emphasis">
                        No plan data.
                    </v-list-item>
                </v-list>

                <v-card class="mt-11 overflow-hidden" color="blue-grey-lighten-4" elevation="1" rounded="xl">
                    <v-card-text class="pa-6">
                        <div class="d-flex justify-center text-blue-grey-lighten-2 mb-5">
                            <v-icon icon="mdi-monitor-dashboard" size="72" />
                        </div>
                        <h3 class="text-center text-subtitle-1 font-weight-bold">Save more time</h3>
                        <p class="mx-auto mt-2 text-center text-caption text-medium-emphasis" style="max-width: 220px">
                            Ensure MySQL users can create databases for new workspaces, and set Stripe price IDs for
                            checkout.
                        </p>
                        <v-btn
                            block
                            class="mt-5 text-caption font-weight-bold"
                            color="grey-darken-4"
                            href="/"
                            rounded="xl"
                            size="large"
                            variant="flat"
                        >
                            View tips
                        </v-btn>
                    </v-card-text>
                </v-card>
            </v-container>
        </template>
    </AdminShellLayout>
</template>
