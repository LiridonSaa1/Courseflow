<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onUnmounted, ref, watch } from 'vue';

const page = usePage();

const SNACKBAR_MS = 4000;

const props = defineProps({
    quizzes: { type: Array, default: () => [] },
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

function formatQuizType(t) {
    if (!t) return '—';
    const s = String(t).replace(/_/g, ' ');
    return s.charAt(0).toUpperCase() + s.slice(1);
}

function quizViewHref(item) {
    if (item.lesson_id && item.lesson?.id) {
        return `/lessons/${item.lesson.id}/quizzes/${item.id}/edit`;
    }
    return null;
}

const items = computed(() =>
    props.quizzes.map((q) => ({
        ...q,
        course_title: q.lesson?.module?.course?.title ?? q.course?.title ?? '—',
        created_display: formatCreated(q.created_at),
        deleted_display: formatDeleted(q.deleted_at),
        type_display: formatQuizType(q.type),
        questions_count_display: q.questions_count ?? 0,
        attempts_count_display: q.attempts_count ?? 0,
        avg_display: q.avg_score_percent != null ? `${q.avg_score_percent}%` : '—',
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
    const next = new Set(selectedIds.value);
    const visibleIds = items.value.map((i) => i.id);

    if (checked) {
        visibleIds.forEach((id) => next.add(id));
    } else {
        visibleIds.forEach((id) => next.delete(id));
    }

    selectedIds.value = [...next];
}

const headers = [
    { title: 'ID', key: 'id', sortable: true, width: '72px' },
    { title: 'Title', key: 'title', sortable: true },
    { title: 'Course', key: 'course_title', sortable: true },
    { title: 'Type', key: 'type_display', sortable: true },
    { title: 'Questions', key: 'questions_count_display', sortable: true },
    { title: 'Attempts', key: 'attempts_count_display', sortable: true },
    { title: 'Avg score', key: 'avg_display', sortable: true },
    { title: 'Status', key: 'status', sortable: true },
    { title: 'Created', key: 'created_display', sortable: true },
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
        showWarning('Zgjidh të paktën një kuiz me checkbox për ta rikthyer.');
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
        '/quizzes/bulk-restore',
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
        showWarning('Zgjidh të paktën një kuiz me checkbox për ta fshirë përgjithmonë.');
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
        '/quizzes/bulk-force-delete',
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
    () => props.quizzes,
    (list) => {
        const valid = new Set(list.map((q) => q.id));
        selectedIds.value = selectedIds.value.filter((id) => valid.has(id));
    },
);
</script>

<template>
    <TenantLayout>
        <Head title="Quiz archive" />

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
            class="flash-snackbar flash-snackbar--warning"
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
                    Rikthe kuizet?
                </v-card-title>
                <v-card-text class="text-body-1 text-medium-emphasis">
                    <template v-if="selectedIds.length === 1"> Ky kuiz do të rikthehet te lista aktive. </template>
                    <template v-else>
                        {{ selectedIds.length }} kuize do të rikthehen te lista aktive.
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
                        Ky kuiz do të fshihet përgjithmonë. Ky veprim nuk mund të zhbëhet.
                    </template>
                    <template v-else>
                        {{ selectedIds.length }} kuize do të fshihen përgjithmonë. Ky veprim nuk mund të zhbëhet.
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
            <v-toolbar class="table-toolbar px-2 px-sm-4" color="primary" density="comfortable" flat>
                <Link class="d-flex align-center ga-2 flex-shrink-0 text-decoration-none text-white" href="/quizzes">
                    <v-btn color="white" icon="mdi-arrow-left" variant="text" />
                    <span class="text-body-1 font-weight-medium">Quiz archive</span>
                </Link>
                <v-spacer />

                <v-tooltip location="top" text="Restore selected">
                    <template #activator="{ props: tip }">
                        <span v-bind="tip" class="d-inline-flex align-center table-toolbar-icon-wrap">
                            <v-badge
                                class="table-toolbar-action-badge"
                                color="warning"
                                dot
                                location="top end"
                                :model-value="selectedIds.length > 0"
                                offset-x="6"
                                offset-y="6"
                            >
                                <v-btn
                                    color="white"
                                    :disabled="selectedIds.length === 0"
                                    icon="mdi-backup-restore"
                                    variant="text"
                                    @click="openRestoreDialog"
                                />
                            </v-badge>
                        </span>
                    </template>
                </v-tooltip>

                <v-tooltip location="top" text="Delete forever">
                    <template #activator="{ props: tip }">
                        <span v-bind="tip" class="d-inline-flex align-center table-toolbar-icon-wrap">
                            <v-badge
                                class="table-toolbar-action-badge"
                                color="warning"
                                dot
                                location="top end"
                                :model-value="selectedIds.length > 0"
                                offset-x="6"
                                offset-y="6"
                            >
                                <v-btn
                                    color="white"
                                    :disabled="selectedIds.length === 0"
                                    icon="mdi-delete-forever"
                                    variant="text"
                                    @click="openForceDeleteDialog"
                                />
                            </v-badge>
                        </span>
                    </template>
                </v-tooltip>

                <v-divider
                    class="table-toolbar-divider mx-1 align-self-center"
                    inset
                    length="24"
                    thickness="2"
                    vertical
                />

                <Link class="d-inline-flex text-decoration-none" href="/quizzes">
                    <v-btn class="ms-1" color="white" prepend-icon="mdi-view-list" variant="text"> All quizzes </v-btn>
                </Link>
            </v-toolbar>

            <v-divider />

            <v-data-table
                :headers="headers"
                :items="items"
                class="quizzes-table"
                hover
                item-value="id"
                :items-per-page="15"
                no-data-text="No archived quizzes."
            >
                <template #item.title="{ item }">
                    <Link
                        class="text-decoration-none font-weight-medium text-primary"
                        :href="quizViewHref(item) ?? `/quizzes/${item.id}/take`"
                    >
                        {{ item.title }}
                    </Link>
                </template>

                <template #item.course_title="{ item }">
                    <span class="text-medium-emphasis">{{ item.course_title }}</span>
                </template>

                <template #item.type_display="{ item }">
                    <span class="text-medium-emphasis">{{ item.type_display }}</span>
                </template>

                <template #item.questions_count_display="{ item }">
                    <span class="text-medium-emphasis">{{ item.questions_count_display }}</span>
                </template>

                <template #item.attempts_count_display="{ item }">
                    <span class="text-medium-emphasis">{{ item.attempts_count_display }}</span>
                </template>

                <template #item.avg_display="{ item }">
                    <span class="text-medium-emphasis">{{ item.avg_display }}</span>
                </template>

                <template #item.status="{ item }">
                    <v-chip
                        :color="item.status === 'published' ? 'success' : 'default'"
                        density="comfortable"
                        size="small"
                        variant="tonal"
                    >
                        {{ item.status === 'published' ? 'Published' : 'Draft' }}
                    </v-chip>
                </template>

                <template #item.created_display="{ item }">
                    <span class="text-medium-emphasis">{{ item.created_display }}</span>
                </template>

                <template #item.deleted_display="{ item }">
                    <span class="text-medium-emphasis text-body-2">{{ item.deleted_display }}</span>
                </template>

                <template #header.select>
                    <div class="d-flex justify-center">
                        <v-checkbox-btn
                            class="quizzes-table-checkbox"
                            :indeterminate="someSelected"
                            :model-value="allSelected"
                            @update:model-value="toggleSelectAll"
                        />
                    </div>
                </template>

                <template #item.select="{ item }">
                    <div class="d-flex justify-center">
                        <v-checkbox-btn
                            class="quizzes-table-checkbox"
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

.table-toolbar {
    align-items: center;
}

.table-toolbar :deep(.v-toolbar__content) {
    align-items: center;
}

.table-toolbar-icon-wrap {
    line-height: 0;
}

.table-toolbar-action-badge :deep(.v-badge__badge) {
    min-width: 10px;
    min-height: 10px;
    height: 10px;
    width: 10px;
    border: 2px solid rgb(var(--v-theme-primary));
    box-sizing: content-box;
}

.table-toolbar-divider {
    opacity: 0.45;
    border-color: rgb(255 255 255 / 0.5);
}

.quizzes-table :deep(.v-data-table__th) {
    font-weight: 600;
}

.quizzes-table :deep(.v-data-table-footer) {
    justify-content: flex-start;
}

.quizzes-table :deep(.v-data-table-footer__info) {
    margin-inline-start: auto;
}

.quizzes-table-checkbox {
    --quizzes-cb-size: 36px;
}

.quizzes-table-checkbox :deep(.v-selection-control) {
    min-height: var(--quizzes-cb-size);
}

.quizzes-table-checkbox :deep(.v-btn) {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
}

.quizzes-table-checkbox :deep(.v-selection-control__wrapper) {
    width: var(--quizzes-cb-size);
    height: var(--quizzes-cb-size);
}

.quizzes-table-checkbox :deep(.v-selection-control__input) {
    width: var(--quizzes-cb-size);
    height: var(--quizzes-cb-size);
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
}

.quizzes-table-checkbox :deep(.v-checkbox-btn__overlay) {
    border-radius: 8px;
    border: 2px solid rgb(var(--v-theme-primary));
    background-color: #fff !important;
    box-shadow: none !important;
}

.quizzes-table-checkbox :deep(.v-icon) {
    color: rgb(var(--v-theme-primary)) !important;
    opacity: 1 !important;
}

.quizzes-table-checkbox :deep(.v-selection-control--dirty .v-checkbox-btn__overlay),
.quizzes-table-checkbox :deep(.v-selection-control--indeterminate .v-checkbox-btn__overlay) {
    background-color: #fff !important;
    border-color: rgb(var(--v-theme-primary));
}
</style>
