<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onUnmounted, ref, watch } from 'vue';

const page = usePage();

const SNACKBAR_MS = 4000;

const props = defineProps({
    students: { type: Array, default: () => [] },
});

const flashSnackbar = ref(false);
const flashMessage = ref('');
const snackbarProgress = ref(100);

let snackbarProgressRaf = null;

function clearSnackbarProgressAnimation() {
    if (snackbarProgressRaf !== null) {
        cancelAnimationFrame(snackbarProgressRaf);
        snackbarProgressRaf = null;
    }
}

watch(flashSnackbar, (open) => {
    clearSnackbarProgressAnimation();
    if (!open) {
        return;
    }
    snackbarProgress.value = 100;
    const start = performance.now();
    function tick(now) {
        const elapsed = now - start;
        snackbarProgress.value = Math.max(0, 100 - (elapsed / SNACKBAR_MS) * 100);
        if (elapsed < SNACKBAR_MS && flashSnackbar.value) {
            snackbarProgressRaf = requestAnimationFrame(tick);
        }
    }
    snackbarProgressRaf = requestAnimationFrame(tick);
});

function showFlashSuccess() {
    const msg = page.props.flash?.success;
    if (typeof msg === 'string' && msg.length > 0) {
        flashMessage.value = msg;
        flashSnackbar.value = true;
    }
}

watch(
    () => page.props.flash?.success,
    () => showFlashSuccess(),
    { immediate: true },
);

const removeInertiaSuccessListener = router.on('success', () => {
    nextTick(() => showFlashSuccess());
});

onUnmounted(() => {
    removeInertiaSuccessListener();
    clearSnackbarProgressAnimation();
});

const selectedIds = ref([]);

function toggleRow(id, checked) {
    const set = new Set(selectedIds.value);
    if (checked) {
        set.add(id);
    } else {
        set.delete(id);
    }
    selectedIds.value = [...set];
}

function statusLabel(s) {
    if (s === 'active') return 'Active';
    if (s === 'inactive') return 'Inactive';
    if (s === 'pending') return 'Pending';
    return s || '—';
}

function nameInitials(s) {
    const f = String(s.first_name ?? '').trim();
    const l = String(s.last_name ?? '').trim();
    const a = f.charAt(0);
    const b = l.charAt(0);
    const initials = `${a}${b}`.toUpperCase();
    if (initials.length > 0) {
        return initials;
    }
    const n = String(s.user?.name ?? '').trim();
    return n ? n.charAt(0).toUpperCase() : '?';
}

const items = computed(() =>
    props.students.map((s) => ({
        ...s,
        email: s.user?.email ?? '—',
        full_name: [s.first_name, s.last_name].filter(Boolean).join(' ') || s.user?.name || '—',
        status_label: statusLabel(s.status),
        deleted_display: formatDeleted(s.deleted_at),
    })),
);

const allSelected = computed(() => {
    if (!items.value.length) {
        return false;
    }

    return items.value.every((i) => selectedIds.value.includes(i.id));
});

const someSelected = computed(() => {
    if (!items.value.length) {
        return false;
    }

    const any = items.value.some((i) => selectedIds.value.includes(i.id));

    return any && !allSelected.value;
});

function toggleSelectAll(checked) {
    if (checked) {
        selectedIds.value = items.value.map((i) => i.id);
    } else {
        selectedIds.value = [];
    }
}

function formatDeleted(iso) {
    if (!iso) return '—';
    try {
        return new Date(iso).toLocaleString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return '—';
    }
}

const headers = [
    { title: 'Name', key: 'full_name', sortable: true },
    { title: 'Email', key: 'email', sortable: true },
    { title: 'Status', key: 'status', sortable: true },
    { title: 'Removed', key: 'deleted_display', sortable: true },
    { title: '', key: 'select', sortable: false, align: 'center', width: '84px' },
];

const warningSnackbar = ref(false);
const warningMessage = ref('');

function showWarning(text) {
    warningMessage.value = text;
    warningSnackbar.value = true;
}

const restoreConfirmOpen = ref(false);
const forceDeleteConfirmOpen = ref(false);

function openRestoreDialog() {
    if (selectedIds.value.length === 0) {
        showWarning('Zgjidh të paktën një student me checkbox për ta rikthyer.');
        return;
    }
    restoreConfirmOpen.value = true;
}

function cancelRestoreConfirm() {
    restoreConfirmOpen.value = false;
}

function confirmRestore() {
    const ids = [...selectedIds.value];
    if (ids.length === 0) {
        restoreConfirmOpen.value = false;
        return;
    }

    router.post(
        '/students/bulk-restore',
        { ids },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedIds.value = [];
                restoreConfirmOpen.value = false;
            },
            onError: () => {
                restoreConfirmOpen.value = false;
            },
        },
    );
}

function openForceDeleteDialog() {
    if (selectedIds.value.length === 0) {
        showWarning('Zgjidh të paktën një student me checkbox për ta fshirë përgjithmonë.');
        return;
    }
    forceDeleteConfirmOpen.value = true;
}

function cancelForceDeleteConfirm() {
    forceDeleteConfirmOpen.value = false;
}

function confirmForceDelete() {
    const ids = [...selectedIds.value];
    if (ids.length === 0) {
        forceDeleteConfirmOpen.value = false;
        return;
    }

    router.post(
        '/students/bulk-force-delete',
        { ids },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedIds.value = [];
                forceDeleteConfirmOpen.value = false;
            },
            onError: () => {
                forceDeleteConfirmOpen.value = false;
            },
        },
    );
}

watch(
    () => props.students,
    (list) => {
        const valid = new Set(list.map((s) => s.id));
        selectedIds.value = selectedIds.value.filter((id) => valid.has(id));
    },
);
</script>

<template>
    <TenantLayout>
        <Head title="Student archive" />

        <v-snackbar
            v-model="flashSnackbar"
            class="flash-snackbar"
            color="success"
            elevation="8"
            location="top end"
            :timeout="SNACKBAR_MS"
            rounded="lg"
        >
            <div class="flash-snackbar__inner d-flex flex-column">
                <div class="d-flex align-center ga-3">
                    <v-icon color="white" icon="mdi-check-circle" size="28" />
                    <span class="text-body-1">{{ flashMessage }}</span>
                </div>
                <v-progress-linear
                    class="flash-snackbar__progress mt-3"
                    bg-opacity="0.25"
                    color="white"
                    height="3"
                    rounded
                    :model-value="snackbarProgress"
                />
            </div>
        </v-snackbar>

        <v-snackbar
            v-model="warningSnackbar"
            class="archive-snackbar archive-snackbar--warning"
            color="warning"
            elevation="8"
            location="top end"
            :timeout="SNACKBAR_MS"
            rounded="lg"
        >
            <div class="d-flex align-center ga-3">
                <v-icon color="white" icon="mdi-alert-circle-outline" size="28" />
                <span class="text-body-1 text-white">{{ warningMessage }}</span>
            </div>
        </v-snackbar>

        <v-dialog v-model="restoreConfirmOpen" max-width="440" persistent>
            <v-card rounded="lg">
                <v-card-title class="d-flex align-center ga-2 text-h6">
                    <v-icon color="primary" icon="mdi-backup-restore" size="28" />
                    Rikthe studentët?
                </v-card-title>
                <v-card-text class="text-body-1 text-medium-emphasis">
                    <template v-if="selectedIds.length === 1">
                        Ky student do të rikthehet te lista e studentëve aktive.
                    </template>
                    <template v-else>
                        {{ selectedIds.length }} studentë do të rikthehen te lista e studentëve aktive.
                    </template>
                </v-card-text>
                <v-card-actions class="px-4 pb-4">
                    <v-spacer />
                    <v-btn variant="text" @click="cancelRestoreConfirm"> Anulo </v-btn>
                    <v-btn color="primary" prepend-icon="mdi-backup-restore" variant="flat" @click="confirmRestore">
                        Rikthe
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="forceDeleteConfirmOpen" max-width="440" persistent>
            <v-card rounded="lg">
                <v-card-title class="d-flex align-center ga-2 text-h6">
                    <v-icon color="error" icon="mdi-delete-forever" size="28" />
                    Fshirje e përhershme?
                </v-card-title>
                <v-card-text class="text-body-1 text-medium-emphasis">
                    <template v-if="selectedIds.length === 1">
                        Ky student dhe llogaria e përdoruesit do të fshihen përgjithmonë. Ky veprim nuk mund të zhbëhet.
                    </template>
                    <template v-else>
                        {{ selectedIds.length }} studentë do të fshihen përgjithmonë bashkë me llogaritë e tyre. Ky veprim
                        nuk mund të zhbëhet.
                    </template>
                </v-card-text>
                <v-card-actions class="px-4 pb-4">
                    <v-spacer />
                    <v-btn variant="text" @click="cancelForceDeleteConfirm"> Anulo </v-btn>
                    <v-btn color="error" prepend-icon="mdi-delete-forever" variant="flat" @click="confirmForceDelete">
                        Fshi përgjithmonë
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-card class="overflow-hidden" rounded="lg">
            <v-toolbar color="primary" density="comfortable" flat>
                <Link class="d-inline-flex text-decoration-none" href="/students">
                    <v-btn color="white" icon="mdi-arrow-left" variant="text" />
                </Link>
                <v-toolbar-title class="text-white"> Student archive (trash) </v-toolbar-title>
                <v-spacer />

                <v-tooltip location="bottom" text="Rikthe studentë të zgjedhur (restore)">
                    <template #activator="{ props: tip }">
                        <span v-bind="tip" class="d-inline-flex align-center">
                            <v-btn
                                color="white"
                                icon="mdi-backup-restore"
                                variant="text"
                                @click="openRestoreDialog"
                            />
                        </span>
                    </template>
                </v-tooltip>

                <v-tooltip location="bottom" text="Fshi përgjithmonë studentë të zgjedhur">
                    <template #activator="{ props: tip }">
                        <span v-bind="tip" class="d-inline-flex align-center">
                            <v-btn
                                color="white"
                                icon="mdi-delete-forever"
                                variant="text"
                                @click="openForceDeleteDialog"
                            />
                        </span>
                    </template>
                </v-tooltip>
                <v-divider class="table-toolbar-divider mx-1 align-self-center" inset length="24" thickness="2" vertical />
                <Link class="d-inline-flex text-decoration-none" href="/students">
                    <v-btn class="ms-1" color="white" prepend-icon="mdi-view-list" variant="text"> All students </v-btn>
                </Link>
            </v-toolbar>

            <v-divider />

            <v-data-table
                :headers="headers"
                :items="items"
                class="students-archive-table"
                hover
                item-value="id"
                :items-per-page="15"
                :no-data-text="'No students in archive.'"
            >
                <template #item.full_name="{ item }">
                    <div class="d-flex align-center ga-3 py-1">
                        <v-avatar v-if="item.profile_photo_url" rounded="lg" size="40">
                            <v-img :src="item.profile_photo_url" cover />
                        </v-avatar>
                        <v-avatar v-else class="text-caption font-weight-medium text-white" color="primary" rounded="lg" size="40">
                            {{ nameInitials(item) }}
                        </v-avatar>
                        <span class="font-weight-medium text-truncate">{{ item.full_name }}</span>
                    </div>
                </template>

                <template #item.email="{ item }">
                    <span class="text-medium-emphasis">{{ item.email }}</span>
                </template>

                <template #item.status="{ item }">
                    <v-chip
                        :color="
                            item.status === 'active' ? 'success' : item.status === 'pending' ? 'warning' : 'default'
                        "
                        density="comfortable"
                        size="small"
                        variant="tonal"
                    >
                        {{ item.status_label }}
                    </v-chip>
                </template>

                <template #item.deleted_display="{ item }">
                    <span class="text-medium-emphasis text-body-2">{{ item.deleted_display }}</span>
                </template>

                <template #header.select>
                    <div class="d-flex justify-center">
                        <v-checkbox-btn
                            class="students-archive-checkbox"
                            :indeterminate="someSelected"
                            :model-value="allSelected"
                            @update:model-value="toggleSelectAll"
                        />
                    </div>
                </template>

                <template #item.select="{ item }">
                    <div class="d-flex justify-center">
                        <v-checkbox-btn
                            class="students-archive-checkbox"
                            :model-value="selectedIds.includes(item.id)"
                            @update:model-value="(v) => toggleRow(item.id, v)"
                        />
                    </div>
                </template>
            </v-data-table>
        </v-card>
    </TenantLayout>
</template>

<style scoped>
.flash-snackbar {
    margin-top: 12px !important;
    margin-inline-end: 12px !important;
}

.flash-snackbar :deep(.v-snackbar__wrapper) {
    min-width: 280px;
    max-width: min(420px, 92vw);
}

.flash-snackbar__inner {
    width: 100%;
}

.archive-snackbar {
    margin-top: 12px !important;
    margin-inline-end: 12px !important;
}

.archive-snackbar :deep(.v-snackbar__wrapper) {
    min-width: 280px;
    max-width: min(420px, 92vw);
}

.students-archive-table :deep(.v-data-table__th) {
    font-weight: 600;
}

.students-archive-table :deep(.v-data-table-footer) {
    justify-content: flex-start;
}

.students-archive-table :deep(.v-data-table-footer__info) {
    margin-inline-start: auto;
}

.table-toolbar-divider {
    opacity: 0.45;
    border-color: rgb(255 255 255 / 0.5);
}

.students-archive-checkbox {
    --students-cb-size: 36px;
}

.students-archive-checkbox :deep(.v-selection-control) {
    min-height: var(--students-cb-size);
}

.students-archive-checkbox :deep(.v-btn) {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
}

.students-archive-checkbox :deep(.v-selection-control__wrapper) {
    width: var(--students-cb-size);
    height: var(--students-cb-size);
}

.students-archive-checkbox :deep(.v-selection-control__input) {
    width: var(--students-cb-size);
    height: var(--students-cb-size);
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
}
</style>
