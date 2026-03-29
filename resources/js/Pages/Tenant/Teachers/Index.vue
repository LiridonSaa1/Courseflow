<script setup>
import TeacherCreateDrawer from '@/Components/TeacherCreateDrawer.vue';
import TeacherEditDrawer from '@/Components/TeacherEditDrawer.vue';
import TeacherFilterDrawer from '@/Components/TeacherFilterDrawer.vue';
import TeachersTableSkeleton from '@/Components/TeachersTableSkeleton.vue';
import TableToolbar from '@/Components/TableToolbar.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { useInertiaVisitLoading } from '@/composables/useInertiaVisitLoading';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const { visitLoading } = useInertiaVisitLoading();

const page = usePage();

const SNACKBAR_MS = 4000;

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

const sessionFlashWarningSnackbar = ref(false);
const sessionFlashWarningMessage = ref('');

function showFlashWarningFromSession() {
    const msg = page.props.flash?.warning;
    if (typeof msg === 'string' && msg.length > 0) {
        sessionFlashWarningMessage.value = msg;
        sessionFlashWarningSnackbar.value = true;
    }
}

watch(
    () => page.props.flash?.warning,
    () => showFlashWarningFromSession(),
    { immediate: true },
);

const removeInertiaSuccessListener = router.on('success', () => {
    nextTick(() => {
        showFlashSuccess();
        showFlashWarningFromSession();
    });
});

const warningSnackbar = ref(false);
const warningMessage = ref('');
const warningSnackbarProgress = ref(100);

let warningSnackbarProgressRaf = null;

function clearWarningSnackbarProgressAnimation() {
    if (warningSnackbarProgressRaf !== null) {
        cancelAnimationFrame(warningSnackbarProgressRaf);
        warningSnackbarProgressRaf = null;
    }
}

watch(warningSnackbar, (open) => {
    clearWarningSnackbarProgressAnimation();
    if (!open) {
        return;
    }
    warningSnackbarProgress.value = 100;
    const start = performance.now();
    function tick(now) {
        const elapsed = now - start;
        warningSnackbarProgress.value = Math.max(0, 100 - (elapsed / SNACKBAR_MS) * 100);
        if (elapsed < SNACKBAR_MS && warningSnackbar.value) {
            warningSnackbarProgressRaf = requestAnimationFrame(tick);
        }
    }
    warningSnackbarProgressRaf = requestAnimationFrame(tick);
});

function showEditSelectionWarning(text) {
    warningMessage.value = text;
    warningSnackbar.value = true;
}

onUnmounted(() => {
    removeInertiaSuccessListener();
    clearSnackbarProgressAnimation();
    clearWarningSnackbarProgressAnimation();
});

const createDrawerOpen = ref(false);
const editDrawerOpen = ref(false);
const editingTeacherId = ref(null);

onMounted(() => {
    const q = new URLSearchParams(window.location.search).get('create');
    if (q === '1' || q === 'true') {
        createDrawerOpen.value = true;
    }
});

const props = defineProps({
    teachers: { type: Array, default: () => [] },
    archivedTeachersCount: { type: Number, default: 0 },
});

const filterDrawerOpen = ref(false);
const teacherFilters = ref({
    first_name: null,
    last_name: null,
    email: null,
    title: null,
    status: null,
});

function formatCreated(iso) {
    if (!iso) return '—';
    try {
        return new Date(iso).toLocaleString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    } catch {
        return '—';
    }
}

function statusLabel(s) {
    if (s === 'active') return 'Active';
    if (s === 'inactive') return 'Inactive';
    if (s === 'pending') return 'Pending';
    return s || '—';
}

function nameInitials(t) {
    const f = String(t.first_name ?? '').trim();
    const l = String(t.last_name ?? '').trim();
    const a = f.charAt(0);
    const b = l.charAt(0);
    const initials = `${a}${b}`.toUpperCase();
    if (initials.length > 0) {
        return initials;
    }
    const n = String(t.user?.name ?? '').trim();
    return n ? n.charAt(0).toUpperCase() : '?';
}

const items = computed(() =>
    props.teachers.map((t) => ({
        ...t,
        email: t.user?.email ?? '—',
        name_display:
            [t.first_name, t.last_name].filter(Boolean).join(' ').trim() || t.user?.name || '—',
        title_display: t.title && String(t.title).trim() ? String(t.title).trim() : '—',
        created_display: formatCreated(t.created_at),
        status_label: statusLabel(t.status),
    })),
);

const filteredItems = computed(() => {
    const f = teacherFilters.value;

    return items.value.filter((row) => {
        if (f.first_name != null && f.first_name !== '' && row.first_name !== f.first_name) {
            return false;
        }
        if (f.last_name != null && f.last_name !== '' && row.last_name !== f.last_name) {
            return false;
        }
        if (f.email != null && f.email !== '' && row.email !== f.email) {
            return false;
        }
        if (f.title != null && f.title !== '') {
            const rowTitle = row.title && String(row.title).trim() ? String(row.title).trim() : '';
            if (rowTitle !== f.title) {
                return false;
            }
        }
        if (f.status != null && f.status !== '' && row.status !== f.status) {
            return false;
        }

        return true;
    });
});

const tableNoDataText = computed(() => {
    if (items.value.length && !filteredItems.value.length) {
        return 'No teachers match these filters.';
    }

    return 'No teachers yet. Create one to get started.';
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

const allSelected = computed(() => {
    if (!filteredItems.value.length) {
        return false;
    }

    return filteredItems.value.every((i) => selectedIds.value.includes(i.id));
});

const someSelected = computed(() => {
    if (!filteredItems.value.length) {
        return false;
    }

    const any = filteredItems.value.some((i) => selectedIds.value.includes(i.id));

    return any && !allSelected.value;
});

function toggleSelectAll(checked) {
    const next = new Set(selectedIds.value);
    const visibleIds = filteredItems.value.map((i) => i.id);

    if (checked) {
        visibleIds.forEach((id) => next.add(id));
    } else {
        visibleIds.forEach((id) => next.delete(id));
    }

    selectedIds.value = [...next];
}

const editingTeacher = computed(() => {
    if (!editingTeacherId.value) {
        return null;
    }

    return props.teachers.find((t) => t.id === editingTeacherId.value) ?? null;
});

function openEditDrawer() {
    const n = selectedIds.value.length;

    if (n === 0) {
        showEditSelectionWarning('Zgjidh një mësimdhënës në tabelë me checkbox për ta përditësuar.');
        return;
    }

    if (n > 1) {
        showEditSelectionWarning('Zgjidh saktësisht një mësimdhënës për ta përditësuar.');
        return;
    }

    const id = selectedIds.value[0];
    if (!props.teachers.some((t) => t.id === id)) {
        showEditSelectionWarning('Mësimdhënësi i zgjedhur nuk u gjet.');
        return;
    }

    editingTeacherId.value = id;
    editDrawerOpen.value = true;
}

watch(editDrawerOpen, (open) => {
    if (!open) {
        editingTeacherId.value = null;
        selectedIds.value = [];
    }
});

const archiveConfirmOpen = ref(false);

function handleArchiveSelection() {
    if (selectedIds.value.length === 0) {
        showEditSelectionWarning('Zgjidh të paktën një mësimdhënës me checkbox për ta zhvendosur në arkiv.');
        return;
    }

    archiveConfirmOpen.value = true;
}

function cancelArchiveConfirm() {
    archiveConfirmOpen.value = false;
}

function confirmArchiveSelection() {
    const ids = [...selectedIds.value];
    if (ids.length === 0) {
        archiveConfirmOpen.value = false;
        return;
    }

    router.post(
        '/teachers/bulk-archive',
        { ids },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedIds.value = [];
                archiveConfirmOpen.value = false;
            },
            onError: () => {
                archiveConfirmOpen.value = false;
            },
        },
    );
}

const headers = [
    { title: 'ID', key: 'id', sortable: true, width: '72px' },
    { title: 'Name', key: 'name_display', sortable: true },
    { title: 'Email', key: 'email', sortable: true },
    { title: 'Phone', key: 'phone', sortable: false },
    { title: 'Title', key: 'title_display', sortable: true },
    { title: 'Status', key: 'status', sortable: true },
    { title: 'Created', key: 'created_display', sortable: true },
    { title: '', key: 'select', sortable: false, align: 'center', width: '84px' },
];
</script>

<template>
    <TenantLayout>
        <Head title="Teachers" />

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
            v-model="sessionFlashWarningSnackbar"
            class="flash-snackbar flash-snackbar--session-warning"
            color="deep-orange"
            elevation="8"
            location="top end"
            :timeout="SNACKBAR_MS"
            rounded="lg"
        >
            <div class="d-flex align-center ga-3">
                <v-icon color="white" icon="mdi-email-alert-outline" size="28" />
                <span class="text-body-1 text-white">{{ sessionFlashWarningMessage }}</span>
            </div>
        </v-snackbar>

        <v-snackbar
            v-model="warningSnackbar"
            class="flash-snackbar flash-snackbar--warning"
            color="warning"
            elevation="8"
            location="top end"
            :timeout="SNACKBAR_MS"
            rounded="lg"
        >
            <div class="flash-snackbar__inner d-flex flex-column">
                <div class="d-flex align-center ga-3">
                    <v-icon color="white" icon="mdi-alert-circle-outline" size="28" />
                    <span class="text-body-1 text-white">{{ warningMessage }}</span>
                </div>
                <v-progress-linear
                    class="flash-snackbar__progress mt-3"
                    bg-opacity="0.25"
                    color="white"
                    height="3"
                    rounded
                    :model-value="warningSnackbarProgress"
                />
            </div>
        </v-snackbar>

        <v-dialog v-model="archiveConfirmOpen" max-width="440" persistent>
            <v-card rounded="lg">
                <v-card-title class="d-flex align-center ga-2 text-h6">
                    <v-icon color="warning" icon="mdi-archive-alert-outline" size="28" />
                    Zhvendos në arkiv?
                </v-card-title>
                <v-card-text class="text-body-1 text-medium-emphasis">
                    <template v-if="selectedIds.length === 1">
                        Ky mësimdhënës do të zhvendoset në arkiv (mund ta rikthesh më vonë nga faqja e arkivit).
                    </template>
                    <template v-else>
                        {{ selectedIds.length }} mësimdhënës do të zhvendosen në arkiv (mund t’i rikthesh më vonë nga faqja e
                        arkivit).
                    </template>
                </v-card-text>
                <v-card-actions class="px-4 pb-4">
                    <v-spacer />
                    <v-btn variant="text" @click="cancelArchiveConfirm"> Anulo </v-btn>
                    <v-btn color="warning" prepend-icon="mdi-archive-arrow-down" variant="flat" @click="confirmArchiveSelection">
                        Zhvendos në arkiv
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-card class="overflow-hidden" rounded="lg">
            <TableToolbar
                :archived-count="archivedTeachersCount"
                archive-href="/teachers/archive"
                archive-tooltip="Open archive (trash)"
                back-href="/dashboard"
                create-tooltip="Create teacher"
                delete-tooltip="Move to archive (trash)"
                edit-tooltip="Edit teacher"
                filter-tooltip="Filter teachers"
                list-title="List of Teachers"
                :selected-count="selectedIds.length"
                @create="createDrawerOpen = true"
                @edit="openEditDrawer"
                @delete="handleArchiveSelection"
                @filter="filterDrawerOpen = true"
            />

            <TeacherCreateDrawer v-model="createDrawerOpen" />

            <TeacherEditDrawer v-model="editDrawerOpen" :teacher="editingTeacher" />

            <TeacherFilterDrawer v-model="filterDrawerOpen" v-model:filters="teacherFilters" :teachers="teachers" />

            <v-divider />

            <TeachersTableSkeleton v-if="visitLoading" />

            <v-data-table
                v-else
                :headers="headers"
                :items="filteredItems"
                class="teachers-table"
                hover
                item-value="id"
                :items-per-page="15"
                :no-data-text="tableNoDataText"
            >
                <template #item.name_display="{ item }">
                    <div class="d-flex align-center ga-3 py-1">
                        <v-avatar v-if="item.profile_photo_url" rounded="lg" size="40">
                            <v-img :src="item.profile_photo_url" cover />
                        </v-avatar>
                        <v-avatar v-else class="text-caption font-weight-medium text-white" color="primary" rounded="lg" size="40">
                            {{ nameInitials(item) }}
                        </v-avatar>
                        <span class="font-weight-medium text-truncate">{{ item.name_display }}</span>
                    </div>
                </template>

                <template #item.email="{ item }">
                    <span class="text-medium-emphasis text-body-2">{{ item.email }}</span>
                </template>

                <template #item.phone="{ item }">
                    <span class="text-medium-emphasis">{{ item.phone || '—' }}</span>
                </template>

                <template #item.title_display="{ item }">
                    <span class="text-medium-emphasis text-body-2">{{ item.title_display }}</span>
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

                <template #header.select>
                    <div class="d-flex justify-center">
                        <v-checkbox-btn
                            class="teachers-table-checkbox"
                            :indeterminate="someSelected"
                            :model-value="allSelected"
                            @update:model-value="toggleSelectAll"
                        />
                    </div>
                </template>

                <template #item.select="{ item }">
                    <div class="d-flex justify-center">
                        <v-checkbox-btn
                            class="teachers-table-checkbox"
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

.teachers-table :deep(.v-data-table__th) {
    font-weight: 600;
}

.teachers-table :deep(.v-data-table-footer) {
    justify-content: flex-start;
}

.teachers-table :deep(.v-data-table-footer__info) {
    margin-inline-start: auto;
}

.teachers-table-checkbox {
    --teachers-cb-size: 36px;
}

.teachers-table-checkbox :deep(.v-selection-control) {
    min-height: var(--teachers-cb-size);
}

.teachers-table-checkbox :deep(.v-btn) {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
}

.teachers-table-checkbox :deep(.v-selection-control__wrapper) {
    width: var(--teachers-cb-size);
    height: var(--teachers-cb-size);
}

.teachers-table-checkbox :deep(.v-selection-control__input) {
    width: var(--teachers-cb-size);
    height: var(--teachers-cb-size);
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
}

.teachers-table-checkbox :deep(.v-checkbox-btn__overlay) {
    border-radius: 8px;
    border: 2px solid rgb(var(--v-theme-primary));
    background-color: #fff !important;
    box-shadow: none !important;
}

.teachers-table-checkbox :deep(.v-icon) {
    color: rgb(var(--v-theme-primary)) !important;
    opacity: 1 !important;
}

.teachers-table-checkbox :deep(.v-selection-control--dirty .v-checkbox-btn__overlay),
.teachers-table-checkbox :deep(.v-selection-control--indeterminate .v-checkbox-btn__overlay) {
    background-color: #fff !important;
    border-color: rgb(var(--v-theme-primary));
}
</style>
