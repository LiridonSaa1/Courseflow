<script setup>
import QuizCreateDrawer from '@/Components/QuizCreateDrawer.vue';
import QuizEditDrawer from '@/Components/QuizEditDrawer.vue';
import QuizFilterDrawer from '@/Components/QuizFilterDrawer.vue';
import QuizzesTableSkeleton from '@/Components/QuizzesTableSkeleton.vue';
import TableToolbar from '@/Components/TableToolbar.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { useInertiaVisitLoading } from '@/composables/useInertiaVisitLoading';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const { visitLoading } = useInertiaVisitLoading();

const page = usePage();

const isStaff = computed(() => {
    const r = page.props.auth?.user?.roles ?? [];
    return r.includes('owner') || r.includes('admin') || r.includes('teacher');
});

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

const removeInertiaSuccessListener = router.on('success', () => {
    nextTick(() => showFlashSuccess());
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
const editingQuizId = ref(null);

const quizPrefill = ref({ courseId: null });

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const create = params.get('create');
    const courseParam = params.get('course_id');
    if (courseParam != null && courseParam !== '') {
        const n = parseInt(courseParam, 10);
        if (!Number.isNaN(n)) {
            quizPrefill.value.courseId = n;
        }
    }
    if (create === '1' || create === 'true') {
        createDrawerOpen.value = true;
    }
});

const props = defineProps({
    quizzes: { type: Array, default: () => [] },
    courses: { type: Array, default: () => [] },
    modules: { type: Array, default: () => [] },
    lessons: { type: Array, default: () => [] },
    archivedQuizzesCount: { type: Number, default: 0 },
});

const filterDrawerOpen = ref(false);
const quizFilters = ref({
    search: '',
    title: null,
    course_id: null,
    module_id: null,
    type: null,
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
        module_id: q.lesson?.module_id ?? null,
        course_id_resolved: q.course_id ?? q.lesson?.module?.course_id ?? null,
        created_display: formatCreated(q.created_at),
        type_display: formatQuizType(q.type),
        questions_count_display: q.questions_count ?? 0,
        attempts_count_display: q.attempts_count ?? 0,
        avg_display: q.avg_score_percent != null ? `${q.avg_score_percent}%` : '—',
    })),
);

const filteredItems = computed(() => {
    const f = quizFilters.value;

    return items.value.filter((row) => {
        const search = f.search != null ? String(f.search).trim().toLowerCase() : '';
        if (search && !String(row.title ?? '').toLowerCase().includes(search)) {
            return false;
        }
        if (f.title != null && f.title !== '' && row.title !== f.title) {
            return false;
        }
        if (f.course_id != null && row.course_id_resolved !== f.course_id) {
            return false;
        }
        if (f.module_id != null && row.module_id !== f.module_id) {
            return false;
        }
        if (f.type != null && f.type !== '' && row.type !== f.type) {
            return false;
        }
        if (f.status != null && f.status !== '' && row.status !== f.status) {
            return false;
        }

        return true;
    });
});

const tableNoDataText = computed(() => {
    if (items.value.length && !filteredItems.value.length) {
        return 'No quizzes match these filters.';
    }

    return 'No quizzes yet. Create one to get started.';
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

const editingQuiz = computed(() => {
    if (!editingQuizId.value) {
        return null;
    }

    return props.quizzes.find((q) => q.id === editingQuizId.value) ?? null;
});

function openEditDrawer() {
    const n = selectedIds.value.length;

    if (n === 0) {
        showEditSelectionWarning('Zgjidh një kuiz në tabelë me checkbox për ta përditësuar.');
        return;
    }

    if (n > 1) {
        showEditSelectionWarning('Zgjidh saktësisht një kuiz për ta përditësuar.');
        return;
    }

    const id = selectedIds.value[0];
    if (!props.quizzes.some((q) => q.id === id)) {
        showEditSelectionWarning('Kuizi i zgjedhur nuk u gjet.');
        return;
    }

    editingQuizId.value = id;
    editDrawerOpen.value = true;
}

watch(editDrawerOpen, (open) => {
    if (!open) {
        editingQuizId.value = null;
        selectedIds.value = [];
    }
});

const archiveConfirmOpen = ref(false);

function handleArchiveSelection() {
    if (selectedIds.value.length === 0) {
        showEditSelectionWarning('Zgjidh të paktën një kuiz me checkbox për ta zhvendosur në arkiv.');
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
        '/quizzes/bulk-archive',
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

function onToolbarCreate() {
    if (!isStaff.value) {
        return;
    }
    createDrawerOpen.value = true;
}

function onToolbarEdit() {
    if (!isStaff.value) {
        return;
    }
    openEditDrawer();
}

function onToolbarArchive() {
    if (!isStaff.value) {
        return;
    }
    handleArchiveSelection();
}

const headers = computed(() => {
    const base = [
        { title: 'ID', key: 'id', sortable: true, width: '72px' },
        { title: 'Title', key: 'title', sortable: true },
        { title: 'Course', key: 'course_title', sortable: true },
        { title: 'Type', key: 'type_display', sortable: true },
        { title: 'Questions', key: 'questions_count_display', sortable: true },
        { title: 'Attempts', key: 'attempts_count_display', sortable: true },
        { title: 'Avg score', key: 'avg_display', sortable: true },
        { title: 'Status', key: 'status', sortable: true },
        { title: 'Created', key: 'created_display', sortable: true },
    ];
    if (isStaff.value) {
        base.push({ title: '', key: 'select', sortable: false, align: 'center', width: '84px' });
    } else {
        base.push({ title: '', key: 'take_only', sortable: false, align: 'end', width: '100px' });
    }
    return base;
});
</script>

<template>
    <TenantLayout>
        <Head title="Quizzes" />

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
                        Ky kuiz do të zhvendoset në arkiv (mund ta rikthesh më vonë nga faqja e arkivit).
                    </template>
                    <template v-else>
                        {{ selectedIds.length }} kuize do të zhvendosen në arkiv (mund t’i rikthesh më vonë nga faqja e
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
                :archived-count="archivedQuizzesCount"
                archive-href="/quizzes/archive"
                archive-tooltip="Open archive (trash)"
                back-href="/dashboard"
                create-tooltip="Create quiz"
                delete-tooltip="Move to archive (trash)"
                edit-tooltip="Edit quiz"
                filter-tooltip="Filter quizzes"
                :list-title="'List of Quizzes'"
                :selected-count="selectedIds.length"
                @create="onToolbarCreate"
                @delete="onToolbarArchive"
                @edit="onToolbarEdit"
                @filter="filterDrawerOpen = true"
            />

            <QuizCreateDrawer
                v-if="isStaff"
                v-model="createDrawerOpen"
                :courses="courses"
                :lessons="lessons"
                :prefill-course-id="quizPrefill.courseId"
            />

            <QuizEditDrawer
                v-if="isStaff"
                v-model="editDrawerOpen"
                :courses="courses"
                :lessons="lessons"
                :quiz="editingQuiz"
            />

            <QuizFilterDrawer
                v-model="filterDrawerOpen"
                v-model:filters="quizFilters"
                :courses="courses"
                :modules="modules"
                :quizzes="quizzes"
            />

            <v-divider />

            <QuizzesTableSkeleton v-if="visitLoading" />

            <v-data-table
                v-else
                :headers="headers"
                :items="filteredItems"
                class="quizzes-table"
                hover
                item-value="id"
                :items-per-page="15"
                :no-data-text="tableNoDataText"
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

                <template #item.take_only="{ item }">
                    <Link class="text-decoration-none" :href="`/quizzes/${item.id}/take`">
                        <v-btn color="primary" density="compact" size="small" variant="text"> Take </v-btn>
                    </Link>
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
