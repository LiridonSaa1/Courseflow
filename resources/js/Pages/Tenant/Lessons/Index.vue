<script setup>
import LessonCreateDrawer from '@/Components/LessonCreateDrawer.vue';
import LessonEditDrawer from '@/Components/LessonEditDrawer.vue';
import LessonFilterDrawer from '@/Components/LessonFilterDrawer.vue';
import LessonsTableSkeleton from '@/Components/LessonsTableSkeleton.vue';
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
const editingLessonId = ref(null);

/** Prefill course/module when opening /lessons?create=1&course_id=&module_id= */
const lessonPrefill = ref({ courseId: null, moduleId: null });

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const create = params.get('create');
    const courseParam = params.get('course_id');
    const moduleParam = params.get('module_id');
    if (courseParam != null && courseParam !== '') {
        const n = parseInt(courseParam, 10);
        if (!Number.isNaN(n)) {
            lessonPrefill.value.courseId = n;
        }
    }
    if (moduleParam != null && moduleParam !== '') {
        const n = parseInt(moduleParam, 10);
        if (!Number.isNaN(n)) {
            lessonPrefill.value.moduleId = n;
        }
    }
    if (create === '1' || create === 'true') {
        createDrawerOpen.value = true;
    }
});

const props = defineProps({
    lessons: { type: Array, default: () => [] },
    courses: { type: Array, default: () => [] },
    modules: { type: Array, default: () => [] },
    archivedLessonsCount: { type: Number, default: 0 },
});

const filterDrawerOpen = ref(false);
const lessonFilters = ref({
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

function formatDuration(m) {
    if (m == null || m === '') {
        return '—';
    }
    return `${m} min`;
}

function formatType(t) {
    if (!t) return '—';
    return String(t).charAt(0).toUpperCase() + String(t).slice(1);
}

function lessonHref(item) {
    const mid = item.module_id;
    if (!mid || !item.id) {
        return '#';
    }
    return `/modules/${mid}/lessons/${item.id}`;
}

const items = computed(() =>
    props.lessons.map((l) => ({
        ...l,
        course_title: l.module?.course?.title ?? '—',
        module_title: l.module?.title ?? '—',
        course_id: l.module?.course_id ?? null,
        created_display: formatCreated(l.created_at),
        duration_display: formatDuration(l.duration_minutes),
        type_display: formatType(l.type),
    })),
);

const filteredItems = computed(() => {
    const f = lessonFilters.value;

    return items.value.filter((row) => {
        const search = f.search != null ? String(f.search).trim().toLowerCase() : '';
        if (search && !String(row.title ?? '').toLowerCase().includes(search)) {
            return false;
        }
        if (f.title != null && f.title !== '' && row.title !== f.title) {
            return false;
        }
        if (f.course_id != null && row.course_id !== f.course_id) {
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
        return 'No lessons match these filters.';
    }

    return 'No lessons yet. Create one to get started.';
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

const editingLesson = computed(() => {
    if (!editingLessonId.value) {
        return null;
    }

    return props.lessons.find((l) => l.id === editingLessonId.value) ?? null;
});

function openEditDrawer() {
    const n = selectedIds.value.length;

    if (n === 0) {
        showEditSelectionWarning('Zgjidh një mësim në tabelë me checkbox për ta përditësuar.');
        return;
    }

    if (n > 1) {
        showEditSelectionWarning('Zgjidh saktësisht një mësim për ta përditësuar.');
        return;
    }

    const id = selectedIds.value[0];
    if (!props.lessons.some((l) => l.id === id)) {
        showEditSelectionWarning('Mësimi i zgjedhur nuk u gjet.');
        return;
    }

    editingLessonId.value = id;
    editDrawerOpen.value = true;
}

watch(editDrawerOpen, (open) => {
    if (!open) {
        editingLessonId.value = null;
        selectedIds.value = [];
    }
});

const archiveConfirmOpen = ref(false);

function handleArchiveSelection() {
    if (selectedIds.value.length === 0) {
        showEditSelectionWarning('Zgjidh të paktën një mësim me checkbox për ta zhvendosur në arkiv.');
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
        '/lessons/bulk-archive',
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
        { title: 'Module', key: 'module_title', sortable: true },
        { title: 'Type', key: 'type_display', sortable: true },
        { title: 'Duration', key: 'duration_display', sortable: true },
        { title: 'Order', key: 'sort_order', sortable: true },
        { title: 'Status', key: 'status', sortable: true },
        { title: 'Created', key: 'created_display', sortable: true },
    ];
    if (isStaff.value) {
        base.push({ title: '', key: 'select', sortable: false, align: 'center', width: '84px' });
    }
    return base;
});
</script>

<template>
    <TenantLayout>
        <Head title="Lessons" />

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
                        Ky mësim do të zhvendoset në arkiv (mund ta rikthesh më vonë nga faqja e arkivit).
                    </template>
                    <template v-else>
                        {{ selectedIds.length }} mësimë do të zhvendosen në arkiv (mund t’i rikthesh më vonë nga faqja e
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
                :archived-count="archivedLessonsCount"
                archive-href="/lessons/archive"
                back-href="/dashboard"
                create-tooltip="Create lesson"
                delete-tooltip="Move to archive (trash)"
                edit-tooltip="Edit lesson"
                filter-tooltip="Filter lessons"
                :list-title="'List of Lessons'"
                :selected-count="selectedIds.length"
                @create="onToolbarCreate"
                @delete="onToolbarArchive"
                @edit="onToolbarEdit"
                @filter="filterDrawerOpen = true"
            />

            <LessonCreateDrawer
                v-if="isStaff"
                v-model="createDrawerOpen"
                :courses="courses"
                :modules="modules"
                :prefill-course-id="lessonPrefill.courseId"
                :prefill-module-id="lessonPrefill.moduleId"
            />

            <LessonEditDrawer
                v-if="isStaff"
                v-model="editDrawerOpen"
                :courses="courses"
                :lesson="editingLesson"
                :modules="modules"
            />

            <LessonFilterDrawer
                v-model="filterDrawerOpen"
                v-model:filters="lessonFilters"
                :courses="courses"
                :lessons="lessons"
                :modules="modules"
            />

            <v-divider />

            <LessonsTableSkeleton v-if="visitLoading" />

            <v-data-table
                v-else
                :headers="headers"
                :items="filteredItems"
                class="lessons-table"
                hover
                item-value="id"
                :items-per-page="15"
                :no-data-text="tableNoDataText"
            >
                <template #item.title="{ item }">
                    <Link
                        :href="lessonHref(item)"
                        class="text-decoration-none font-weight-medium text-primary"
                    >
                        {{ item.title }}
                    </Link>
                </template>

                <template #item.course_title="{ item }">
                    <span class="text-medium-emphasis">{{ item.course_title }}</span>
                </template>

                <template #item.module_title="{ item }">
                    <span class="text-medium-emphasis">{{ item.module_title }}</span>
                </template>

                <template #item.type_display="{ item }">
                    <span class="text-medium-emphasis">{{ item.type_display }}</span>
                </template>

                <template #item.duration_display="{ item }">
                    <span class="text-medium-emphasis">{{ item.duration_display }}</span>
                </template>

                <template #item.sort_order="{ item }">
                    <span class="text-medium-emphasis">{{ item.sort_order ?? '—' }}</span>
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

                <template #header.select>
                    <div class="d-flex justify-center">
                        <v-checkbox-btn
                            class="lessons-table-checkbox"
                            :indeterminate="someSelected"
                            :model-value="allSelected"
                            @update:model-value="toggleSelectAll"
                        />
                    </div>
                </template>

                <template #item.select="{ item }">
                    <div class="d-flex justify-center">
                        <v-checkbox-btn
                            class="lessons-table-checkbox"
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

.lessons-table :deep(.v-data-table__th) {
    font-weight: 600;
}

.lessons-table :deep(.v-data-table-footer) {
    justify-content: flex-start;
}

.lessons-table :deep(.v-data-table-footer__info) {
    margin-inline-start: auto;
}

.lessons-table-checkbox {
    --lessons-cb-size: 36px;
}

.lessons-table-checkbox :deep(.v-selection-control) {
    min-height: var(--lessons-cb-size);
}

.lessons-table-checkbox :deep(.v-btn) {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
}

.lessons-table-checkbox :deep(.v-selection-control__wrapper) {
    width: var(--lessons-cb-size);
    height: var(--lessons-cb-size);
}

.lessons-table-checkbox :deep(.v-selection-control__input) {
    width: var(--lessons-cb-size);
    height: var(--lessons-cb-size);
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
}

.lessons-table-checkbox :deep(.v-checkbox-btn__overlay) {
    border-radius: 8px;
    border: 2px solid rgb(var(--v-theme-primary));
    background-color: #fff !important;
    box-shadow: none !important;
}

.lessons-table-checkbox :deep(.v-icon) {
    color: rgb(var(--v-theme-primary)) !important;
    opacity: 1 !important;
}

.lessons-table-checkbox :deep(.v-selection-control--dirty .v-checkbox-btn__overlay),
.lessons-table-checkbox :deep(.v-selection-control--indeterminate .v-checkbox-btn__overlay) {
    background-color: #fff !important;
    border-color: rgb(var(--v-theme-primary));
}
</style>
