<script setup>
import CourseCreateDrawer from '@/Components/CourseCreateDrawer.vue';
import CourseEditDrawer from '@/Components/CourseEditDrawer.vue';
import CourseFilterDrawer from '@/Components/CourseFilterDrawer.vue';
import CoursesTableSkeleton from '@/Components/CoursesTableSkeleton.vue';
import TableToolbar from '@/Components/TableToolbar.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { useInertiaVisitLoading } from '@/composables/useInertiaVisitLoading';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
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
const editingCourseId = ref(null);

onMounted(() => {
    const q = new URLSearchParams(window.location.search).get('create');
    if (q === '1' || q === 'true') {
        createDrawerOpen.value = true;
    }
});

const props = defineProps({
    courses: { type: Array, default: () => [] },
    teachers: { type: Array, default: () => [] },
    archivedCoursesCount: { type: Number, default: 0 },
});

const filterDrawerOpen = ref(false);
const courseFilters = ref({
    title: null,
    language: null,
    level: null,
    teacher_id: null,
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

function truncate(text, len = 48) {
    if (!text) return '—';
    const s = String(text);

    return s.length <= len ? s : `${s.slice(0, len)}…`;
}

const items = computed(() =>
    props.courses.map((c) => ({
        ...c,
        modules_count: c.modules?.length ?? 0,
        teacher_name: c.teacher?.user?.name ?? '—',
        created_display: formatCreated(c.created_at),
        description_short: truncate(c.description, 40),
    })),
);

const filteredItems = computed(() => {
    const f = courseFilters.value;

    return items.value.filter((row) => {
        if (f.title != null && f.title !== '' && row.title !== f.title) {
            return false;
        }
        if (f.language != null && f.language !== '' && row.language !== f.language) {
            return false;
        }
        if (f.level != null && f.level !== '' && row.level !== f.level) {
            return false;
        }
        if (f.teacher_id != null && row.teacher_id !== f.teacher_id) {
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
        return 'No courses match these filters.';
    }

    return 'No courses yet. Create one to get started.';
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

const editingCourse = computed(() => {
    if (!editingCourseId.value) {
        return null;
    }

    return props.courses.find((c) => c.id === editingCourseId.value) ?? null;
});

function openEditDrawer() {
    const n = selectedIds.value.length;

    if (n === 0) {
        showEditSelectionWarning('Zgjidh një kurs në tabelë me checkbox për ta përditësuar.');
        return;
    }

    if (n > 1) {
        showEditSelectionWarning('Zgjidh saktësisht një kurs për ta përditësuar.');
        return;
    }

    const id = selectedIds.value[0];
    if (!props.courses.some((c) => c.id === id)) {
        showEditSelectionWarning('Kursi i zgjedhur nuk u gjet.');
        return;
    }

    editingCourseId.value = id;
    editDrawerOpen.value = true;
}

watch(editDrawerOpen, (open) => {
    if (!open) {
        editingCourseId.value = null;
        selectedIds.value = [];
    }
});

const archiveConfirmOpen = ref(false);

function handleArchiveSelection() {
    if (selectedIds.value.length === 0) {
        showEditSelectionWarning('Zgjidh të paktën një kurs me checkbox për ta zhvendosur në arkiv.');
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
        '/courses/bulk-archive',
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
    { title: 'Title', key: 'title', sortable: true },
    { title: 'Description', key: 'description_short', sortable: false },
    { title: 'Language', key: 'language', sortable: true },
    { title: 'Level', key: 'level', sortable: true },
    { title: 'Teacher', key: 'teacher_name', sortable: false },
    { title: 'Status', key: 'status', sortable: true },
    // { title: 'Thumbnail', key: 'thumbnail', sortable: false, align: 'center', width: '88px' },
    { title: 'Created', key: 'created_display', sortable: true },
    { title: 'Modules', key: 'modules_count', sortable: true },
    { title: '', key: 'select', sortable: false, align: 'center', width: '84px' },
];
</script>

<template>
    <TenantLayout>
        <Head title="Courses" />

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
                        Ky kurs do të zhvendoset në arkiv (mund ta rikthesh më vonë nga faqja e arkivit).
                    </template>
                    <template v-else>
                        {{ selectedIds.length }} kurse do të zhvendosen në arkiv (mund t’i rikthesh më vonë nga faqja e
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
                :archived-count="archivedCoursesCount"
                back-href="/dashboard"
                :selected-count="selectedIds.length"
                @create="createDrawerOpen = true"
                @edit="openEditDrawer"
                @delete="handleArchiveSelection"
                @filter="filterDrawerOpen = true"
            />

            <CourseCreateDrawer v-model="createDrawerOpen" :teachers="teachers" />

            <CourseEditDrawer v-model="editDrawerOpen" :course="editingCourse" :teachers="teachers" />

            <CourseFilterDrawer
                v-model="filterDrawerOpen"
                v-model:filters="courseFilters"
                :courses="courses"
                :teachers="teachers"
            />

            <v-divider />

            <CoursesTableSkeleton v-if="visitLoading" />

            <v-data-table
                v-else
                :headers="headers"
                :items="filteredItems"
                class="courses-table"
                hover
                item-value="id"
                :items-per-page="15"
                :no-data-text="tableNoDataText"
            >
                <template #item.title="{ item }">
                    <Link
                        :href="`/courses/${item.id}`"
                        class="text-decoration-none font-weight-medium text-primary"
                    >
                        {{ item.title }}
                    </Link>
                </template>

                <template #item.description_short="{ item }">
                    <span class="text-medium-emphasis text-body-2">{{ item.description_short }}</span>
                </template>

                <template #item.language="{ item }">
                    <span class="text-medium-emphasis">{{ item.language || '—' }}</span>
                </template>

                <template #item.level="{ item }">
                    <span class="text-medium-emphasis">{{ item.level || '—' }}</span>
                </template>

                <template #item.teacher_name="{ item }">
                    <span class="text-medium-emphasis">{{ item.teacher_name }}</span>
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

                <template #item.thumbnail="{ item }">
                    <div class="d-flex justify-center">
                        <v-avatar v-if="item.thumbnail" rounded size="40">
                            <v-img :src="item.thumbnail" cover />
                        </v-avatar>
                        <span v-else class="text-caption text-medium-emphasis">—</span>
                    </div>
                </template>

                <template #header.select>
                    <div class="d-flex justify-center">
                        <v-checkbox-btn
                            class="courses-table-checkbox"
                            :indeterminate="someSelected"
                            :model-value="allSelected"
                            @update:model-value="toggleSelectAll"
                        />
                    </div>
                </template>

                <template #item.select="{ item }">
                    <div class="d-flex justify-center">
                        <v-checkbox-btn
                            class="courses-table-checkbox"
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

.courses-table :deep(.v-data-table__th) {
    font-weight: 600;
}

/* Footer: "Items per page" on the left; range + pagination grouped on the right */
.courses-table :deep(.v-data-table-footer) {
    justify-content: flex-start;
}

.courses-table :deep(.v-data-table-footer__info) {
    margin-inline-start: auto;
}

/* Single primary border only (no double ring: border on overlay, not on input) */
.courses-table-checkbox {
    --courses-cb-size: 36px;
}

.courses-table-checkbox :deep(.v-selection-control) {
    min-height: var(--courses-cb-size);
}

/* CheckboxBtn wraps a v-btn — strip default surface/border so only overlay ring shows */
.courses-table-checkbox :deep(.v-btn) {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
}

.courses-table-checkbox :deep(.v-selection-control__wrapper) {
    width: var(--courses-cb-size);
    height: var(--courses-cb-size);
}

.courses-table-checkbox :deep(.v-selection-control__input) {
    width: var(--courses-cb-size);
    height: var(--courses-cb-size);
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
}

.courses-table-checkbox :deep(.v-checkbox-btn__overlay) {
    border-radius: 8px;
    border: 2px solid rgb(var(--v-theme-primary));
    background-color: #fff !important;
    box-shadow: none !important;
}

.courses-table-checkbox :deep(.v-icon) {
    color: rgb(var(--v-theme-primary)) !important;
    opacity: 1 !important;
}

.courses-table-checkbox :deep(.v-selection-control--dirty .v-checkbox-btn__overlay),
.courses-table-checkbox :deep(.v-selection-control--indeterminate .v-checkbox-btn__overlay) {
    background-color: #fff !important;
    border-color: rgb(var(--v-theme-primary));
}
</style>
