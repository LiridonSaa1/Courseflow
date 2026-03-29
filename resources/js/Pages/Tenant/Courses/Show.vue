<script setup>
import CourseEditDrawer from '@/Components/CourseEditDrawer.vue';
import ModuleCreateDrawer from '@/Components/ModuleCreateDrawer.vue';
import ModuleEditDrawer from '@/Components/ModuleEditDrawer.vue';
import ModuleFilterDrawer from '@/Components/ModuleFilterDrawer.vue';
import CoursesTableSkeleton from '@/Components/CoursesTableSkeleton.vue';
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

function showWarning(text) {
    warningMessage.value = text;
    warningSnackbar.value = true;
}

onUnmounted(() => {
    removeInertiaSuccessListener();
    clearSnackbarProgressAnimation();
    clearWarningSnackbarProgressAnimation();
});

const props = defineProps({
    course: { type: Object, required: true },
    teachers: { type: Array, default: () => [] },
});

const moduleCreateOpen = ref(false);
const moduleEditOpen = ref(false);
const courseEditOpen = ref(false);
const editingModuleId = ref(null);

const filterDrawerOpen = ref(false);
const moduleFilters = ref({
    search: '',
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

function formatDurationMinutes(total) {
    const n = Number(total);
    if (!Number.isFinite(n) || n <= 0) {
        return '—';
    }
    if (n < 60) {
        return `${Math.round(n)} min`;
    }
    const h = Math.floor(n / 60);
    const m = Math.round(n % 60);
    return m ? `${h}h ${m}m` : `${h}h`;
}

function truncateDescription(text, max = 72) {
    const t = String(text ?? '').trim();
    if (!t) {
        return '—';
    }
    return t.length > max ? `${t.slice(0, max - 1)}…` : t;
}

const items = computed(() =>
    (props.course.modules ?? []).map((m) => {
        const lessons = m.lessons ?? [];
        const durationMinutes = lessons.reduce((sum, l) => sum + (Number(l.duration_minutes) || 0), 0);
        return {
            ...m,
            lessons_count: lessons.length,
            created_display: formatCreated(m.created_at),
            duration_minutes: durationMinutes,
            duration_display: formatDurationMinutes(durationMinutes),
            description_preview: truncateDescription(m.description),
        };
    }),
);

const filteredItems = computed(() => {
    const f = moduleFilters.value;

    return items.value.filter((row) => {
        const search = f.search != null ? String(f.search).trim().toLowerCase() : '';
        if (search) {
            const title = String(row.title ?? '').toLowerCase();
            const desc = String(row.description ?? '').toLowerCase();
            if (!title.includes(search) && !desc.includes(search)) {
                return false;
            }
        }
        if (f.title != null && f.title !== '' && row.title !== f.title) {
            return false;
        }
        if (f.status != null && f.status !== '' && String(row.status ?? '') !== f.status) {
            return false;
        }

        return true;
    });
});

const tableNoDataText = computed(() => {
    if (items.value.length && !filteredItems.value.length) {
        return 'No modules match these filters.';
    }

    return 'No modules yet. Add one to organize lessons.';
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

const editingModule = computed(() => {
    if (!editingModuleId.value) {
        return null;
    }

    return items.value.find((m) => m.id === editingModuleId.value) ?? null;
});

function openEditModuleDrawer() {
    const n = selectedIds.value.length;

    if (n === 0) {
        showWarning('Select exactly one module with the checkbox to edit.');
        return;
    }

    if (n > 1) {
        showWarning('Select exactly one module to edit.');
        return;
    }

    const id = selectedIds.value[0];
    if (!items.value.some((m) => m.id === id)) {
        showWarning('Selected module not found.');
        return;
    }

    editingModuleId.value = id;
    moduleEditOpen.value = true;
}

watch(moduleEditOpen, (open) => {
    if (!open) {
        editingModuleId.value = null;
        selectedIds.value = [];
    }
});

const moduleDeleteConfirmOpen = ref(false);

function handleModuleDeleteToolbar() {
    if (selectedIds.value.length === 0) {
        showWarning('Select at least one module to delete.');
        return;
    }

    moduleDeleteConfirmOpen.value = true;
}

function cancelModuleDeleteConfirm() {
    moduleDeleteConfirmOpen.value = false;
}

function confirmModuleDelete() {
    const ids = [...selectedIds.value];
    if (ids.length === 0) {
        moduleDeleteConfirmOpen.value = false;
        return;
    }

    function deleteNext(i) {
        if (i >= ids.length) {
            selectedIds.value = [];
            moduleDeleteConfirmOpen.value = false;
            return;
        }

        router.delete(`/courses/${props.course.id}/modules/${ids[i]}`, {
            preserveScroll: true,
            onSuccess: () => deleteNext(i + 1),
            onError: () => {
                moduleDeleteConfirmOpen.value = false;
            },
        });
    }

    deleteNext(0);
}

const courseDeleteConfirmOpen = ref(false);

function confirmCourseDelete() {
    router.delete(`/courses/${props.course.id}`);
}

function onToolbarCreate() {
    if (!isStaff.value) {
        return;
    }
    moduleCreateOpen.value = true;
}

function onToolbarEdit() {
    if (!isStaff.value) {
        return;
    }
    openEditModuleDrawer();
}

function onToolbarDelete() {
    if (!isStaff.value) {
        return;
    }
    handleModuleDeleteToolbar();
}

const headers = computed(() => {
    const base = [
        { title: 'Title', key: 'title', sortable: true },
        { title: 'Description', key: 'description_preview', sortable: false, width: '220px' },
        { title: 'Order', key: 'sort_order', sortable: true, width: '96px' },
        { title: 'Lessons', key: 'lessons_count', sortable: true, width: '100px' },
        { title: 'Duration', key: 'duration_minutes', sortable: true, width: '112px' },
        { title: 'Status', key: 'status', sortable: true, width: '120px' },
        { title: 'Created', key: 'created_display', sortable: true },
    ];
    if (isStaff.value) {
        base.push({ title: '', key: 'select', sortable: false, align: 'center', width: '84px' });
    } else {
        base.push({ title: '', key: 'browse', sortable: false, align: 'end', width: '120px' });
    }
    return base;
});

onMounted(() => {
    const q = new URLSearchParams(window.location.search).get('create');
    if (q === '1' || q === 'true') {
        moduleCreateOpen.value = true;
    }
});
</script>

<template>
    <TenantLayout>
        <Head :title="course.title" />

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

        <v-dialog v-model="moduleDeleteConfirmOpen" max-width="440" persistent>
            <v-card rounded="lg">
                <v-card-title class="d-flex align-center ga-2 text-h6">
                    <v-icon color="error" icon="mdi-delete-alert-outline" size="28" />
                    Delete module(s)?
                </v-card-title>
                <v-card-text class="text-body-1 text-medium-emphasis">
                    <template v-if="selectedIds.length === 1"> This module will be removed. Lessons in it may be affected. </template>
                    <template v-else>
                        {{ selectedIds.length }} modules will be deleted. This cannot be undone.
                    </template>
                </v-card-text>
                <v-card-actions class="px-4 pb-4">
                    <v-spacer />
                    <v-btn variant="text" @click="cancelModuleDeleteConfirm"> Cancel </v-btn>
                    <v-btn color="error" prepend-icon="mdi-delete-forever" variant="flat" @click="confirmModuleDelete">
                        Delete
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="courseDeleteConfirmOpen" max-width="440" persistent>
            <v-card rounded="lg">
                <v-card-title class="d-flex align-center ga-2 text-h6">
                    <v-icon color="error" icon="mdi-delete-alert-outline" size="28" />
                    Delete course?
                </v-card-title>
                <v-card-text class="text-body-1 text-medium-emphasis">
                    This course will be moved to archive. You can restore it from the course archive.
                </v-card-text>
                <v-card-actions class="px-4 pb-4">
                    <v-spacer />
                    <v-btn variant="text" @click="courseDeleteConfirmOpen = false"> Cancel </v-btn>
                    <v-btn color="error" prepend-icon="mdi-delete-forever" variant="flat" @click="confirmCourseDelete">
                        Delete
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- <v-card class="course-show-header mb-4" border rounded="xl" elevation="1">
            <v-card-text class="pa-6 pa-sm-8">
                <v-row align="stretch">
                    <v-col cols="12" :md="isStaff ? 8 : 12">
                        <div class="d-flex align-start ga-4 ga-sm-6">
                            <v-avatar
                                class="flex-shrink-0 mt-1"
                                color="primary"
                                rounded="lg"
                                size="56"
                                variant="tonal"
                            >
                                <v-icon icon="mdi-book-education-outline" size="28" />
                            </v-avatar>
                            <div class="min-w-0 flex-grow-1">
                                <v-card-title class="pa-0 text-h5 font-weight-bold text-high-emphasis text-wrap">
                                    {{ course.title }}
                                </v-card-title>

                                <div class="d-flex flex-wrap align-center ga-2 mt-3">
                                    <v-chip
                                        class="font-weight-medium"
                                        density="comfortable"
                                        label
                                        prepend-icon="mdi-translate"
                                        size="small"
                                        variant="outlined"
                                    >
                                        {{ course.language || '—' }}
                                    </v-chip>
                                    <v-chip
                                        class="font-weight-medium"
                                        density="comfortable"
                                        label
                                        prepend-icon="mdi-stairs"
                                        size="small"
                                        variant="outlined"
                                    >
                                        {{ course.level || '—' }}
                                    </v-chip>
                                    <v-chip
                                        v-if="course.teacher?.user?.name"
                                        class="font-weight-medium"
                                        color="primary"
                                        density="comfortable"
                                        label
                                        prepend-icon="mdi-account-school-outline"
                                        size="small"
                                        variant="tonal"
                                    >
                                        {{ course.teacher.user.name }}
                                    </v-chip>
                                </div>

                                <p
                                    v-if="course.description"
                                    class="text-body-1 text-medium-emphasis mt-4 mb-0"
                                    style="max-width: 48rem; line-height: 1.55"
                                >
                                    {{ course.description }}
                                </p>

                                <div class="mt-4">
                                    <v-chip
                                        :color="course.status === 'published' ? 'success' : 'default'"
                                        density="comfortable"
                                        :prepend-icon="course.status === 'published' ? 'mdi-check-circle-outline' : 'mdi-file-edit-outline'"
                                        size="small"
                                        variant="tonal"
                                    >
                                        {{ course.status === 'published' ? 'Published' : 'Draft' }}
                                    </v-chip>
                                </div>
                            </div>
                        </div>
                    </v-col>

                    <v-col v-if="isStaff" class="d-flex align-stretch align-md-center justify-md-end pt-4 pt-md-0" cols="12" md="4">
                        <div class="course-show-header__actions d-flex flex-column flex-sm-row flex-md-column ga-2 w-100">
                            <v-btn
                                color="primary"
                                prepend-icon="mdi-pencil-outline"
                                rounded="lg"
                                size="large"
                                variant="tonal"
                                @click="courseEditOpen = true"
                            >
                                Edit course
                            </v-btn>
                            <v-btn
                                color="error"
                                prepend-icon="mdi-delete-outline"
                                rounded="lg"
                                size="large"
                                variant="tonal"
                                @click="courseDeleteConfirmOpen = true"
                            >
                                Delete course
                            </v-btn>
                        </div>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card> -->

        <CourseEditDrawer v-if="isStaff" v-model="courseEditOpen" :course="course" :teachers="teachers" />

        <v-card class="overflow-hidden" rounded="lg">
            <TableToolbar
                v-if="isStaff"
                back-href="/courses"
                :create-tooltip="'Add module'"
                :delete-tooltip="'Delete module'"
                :edit-tooltip="'Edit module'"
                :filter-tooltip="'Filter modules'"
                :list-title="`Modules · ${course.title}`"
                :selected-count="selectedIds.length"
                :show-archive="false"
                @create="onToolbarCreate"
                @delete="onToolbarDelete"
                @edit="onToolbarEdit"
                @filter="filterDrawerOpen = true"
            />

            <div v-else class="table-toolbar px-2 px-sm-4 py-3 text-body-1 font-weight-medium text-white bg-primary">
                <Link class="d-inline-flex align-center ga-2 text-decoration-none text-white" href="/courses">
                    <v-btn color="white" icon="mdi-arrow-left" variant="text" />
                    <span>Modules · {{ course.title }}</span>
                </Link>
            </div>

            <ModuleCreateDrawer v-if="isStaff" v-model="moduleCreateOpen" :course-id="course.id" />

            <ModuleEditDrawer
                v-if="isStaff"
                v-model="moduleEditOpen"
                :course-id="course.id"
                :module="editingModule"
            />

            <ModuleFilterDrawer v-model="filterDrawerOpen" v-model:filters="moduleFilters" :modules="items" />

            <v-divider />

            <CoursesTableSkeleton v-if="visitLoading" />

            <v-data-table
                v-else
                :headers="headers"
                :items="filteredItems"
                class="course-show-modules-table"
                hover
                item-value="id"
                :items-per-page="15"
                :no-data-text="tableNoDataText"
            >
                <template #item.title="{ item }">
                    <Link
                        :href="`/modules/${item.id}/lessons`"
                        class="text-decoration-none font-weight-medium text-primary"
                    >
                        {{ item.title }}
                    </Link>
                </template>

                <template #item.description_preview="{ item }">
                    <span
                        class="text-body-2 text-medium-emphasis text-truncate d-inline-block"
                        style="max-width: 220px"
                        :title="item.description && String(item.description).trim() ? item.description : undefined"
                    >
                        {{ item.description_preview }}
                    </span>
                </template>

                <template #item.sort_order="{ item }">
                    <span class="text-medium-emphasis">{{ item.sort_order ?? '—' }}</span>
                </template>

                <template #item.lessons_count="{ item }">
                    <span class="text-medium-emphasis">{{ item.lessons_count }}</span>
                </template>

                <template #item.duration_minutes="{ item }">
                    <span class="text-medium-emphasis">{{ item.duration_display }}</span>
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

                <template #item.browse="{ item }">
                    <Link class="text-decoration-none" :href="`/modules/${item.id}/lessons`">
                        <v-btn color="primary" density="compact" size="small" variant="text"> Open </v-btn>
                    </Link>
                </template>

                <template #header.select>
                    <div class="d-flex justify-center">
                        <v-checkbox-btn
                            class="course-show-modules-checkbox"
                            :indeterminate="someSelected"
                            :model-value="allSelected"
                            @update:model-value="toggleSelectAll"
                        />
                    </div>
                </template>

                <template #item.select="{ item }">
                    <div class="d-flex justify-center">
                        <v-checkbox-btn
                            class="course-show-modules-checkbox"
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
.course-show-header :deep(.v-card-title) {
    line-height: 1.35;
}

@media (min-width: 960px) {
    .course-show-header__actions {
        width: 100%;
        max-width: 220px;
        margin-inline-start: auto;
    }
}

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

.course-show-modules-table :deep(.v-data-table__th) {
    font-weight: 600;
}

.course-show-modules-table :deep(.v-data-table-footer) {
    justify-content: flex-start;
}

.course-show-modules-table :deep(.v-data-table-footer__info) {
    margin-inline-start: auto;
}

.course-show-modules-checkbox {
    --mod-cb-size: 36px;
}

.course-show-modules-checkbox :deep(.v-selection-control) {
    min-height: var(--mod-cb-size);
}

.course-show-modules-checkbox :deep(.v-btn) {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
}

.course-show-modules-checkbox :deep(.v-selection-control__wrapper) {
    width: var(--mod-cb-size);
    height: var(--mod-cb-size);
}

.course-show-modules-checkbox :deep(.v-selection-control__input) {
    width: var(--mod-cb-size);
    height: var(--mod-cb-size);
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
}

.course-show-modules-checkbox :deep(.v-checkbox-btn__overlay) {
    border-radius: 8px;
    border: 2px solid rgb(var(--v-theme-primary));
    background-color: #fff !important;
    box-shadow: none !important;
}

.course-show-modules-checkbox :deep(.v-icon) {
    color: rgb(var(--v-theme-primary)) !important;
    opacity: 1 !important;
}

.course-show-modules-checkbox :deep(.v-selection-control--dirty .v-checkbox-btn__overlay),
.course-show-modules-checkbox :deep(.v-selection-control--indeterminate .v-checkbox-btn__overlay) {
    background-color: #fff !important;
    border-color: rgb(var(--v-theme-primary));
}
</style>
