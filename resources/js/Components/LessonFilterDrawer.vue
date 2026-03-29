<script setup>
import { computed, watch } from 'vue';

const open = defineModel({ type: Boolean, default: false });

const filters = defineModel('filters', {
    type: Object,
    default: () => ({
        search: '',
        title: null,
        course_id: null,
        module_id: null,
        type: null,
        status: null,
    }),
});

const props = defineProps({
    lessons: { type: Array, default: () => [] },
    courses: { type: Array, default: () => [] },
    modules: { type: Array, default: () => [] },
});

const typeItems = [
    { title: 'Reading', value: 'reading' },
    { title: 'Grammar', value: 'grammar' },
    { title: 'Vocabulary', value: 'vocabulary' },
    { title: 'Listening', value: 'listening' },
    { title: 'Speaking', value: 'speaking' },
    { title: 'Writing', value: 'writing' },
    { title: 'Mixed', value: 'mixed' },
];

const statusItems = [
    { title: 'Draft', value: 'draft' },
    { title: 'Published', value: 'published' },
];

const titleItems = computed(() => {
    const set = new Set();
    for (const l of props.lessons) {
        const t = l.title;
        if (t && String(t).trim()) {
            set.add(String(t).trim());
        }
    }
    return [...set]
        .sort((a, b) => a.localeCompare(b))
        .map((title) => ({ title, value: title }));
});

const courseItems = computed(() =>
    props.courses.map((c) => ({
        title: c.title,
        value: c.id,
    })),
);

const moduleItems = computed(() => {
    const cid = filters.value.course_id;
    let list = props.modules;
    if (cid != null && cid !== '') {
        list = list.filter((m) => m.course_id === cid);
    }
    return list.map((m) => ({
        title: `${m.course?.title ?? '—'} · ${m.title}`,
        value: m.id,
    }));
});

watch(
    () => filters.value.course_id,
    (courseId) => {
        if (filters.value.module_id == null || filters.value.module_id === '') {
            return;
        }
        const m = props.modules.find((x) => x.id === filters.value.module_id);
        if (m && courseId != null && m.course_id !== courseId) {
            filters.value.module_id = null;
        }
    },
);

function resetFilters() {
    filters.value = {
        search: '',
        title: null,
        course_id: null,
        module_id: null,
        type: null,
        status: null,
    };
}

function close() {
    open.value = false;
}
</script>

<template>
    <v-navigation-drawer
        v-model="open"
        class="course-create-drawer lesson-filter-drawer"
        location="end"
        temporary
        width="480"
        scrim
    >
        <v-toolbar class="align-start py-4" color="primary" flat height="auto">
            <v-btn class="mt-1" color="white" icon="mdi-close" variant="text" @click="close" />
            <div class="ms-2 flex min-w-0 flex-1 flex-col gap-1 ps-0">
                <div class="d-flex align-center ga-2">
                    <v-icon color="white" icon="mdi-filter-variant" size="28" />
                    <v-toolbar-title class="text-h6 font-weight-bold text-white pa-0"> Filter lessons </v-toolbar-title>
                </div>
                <p class="mb-0 text-body-2 text-white opacity-90">
                    Narrow by title, course, module, type, or status. Search matches the lesson title.
                </p>
            </div>
        </v-toolbar>
        <v-divider />

        <v-container class="py-6" fluid>
            <div class="d-flex flex-column ga-4">
                <v-text-field
                    v-model="filters.search"
                    clearable
                    density="comfortable"
                    hide-details="auto"
                    label="Search title"
                    placeholder="Contains…"
                    variant="outlined"
                />

                <v-select
                    v-model="filters.title"
                    clearable
                    density="comfortable"
                    hide-details="auto"
                    :items="titleItems"
                    item-title="title"
                    item-value="value"
                    label="Exact title"
                    placeholder="All titles"
                    variant="outlined"
                />

                <v-select
                    v-model="filters.course_id"
                    clearable
                    density="comfortable"
                    hide-details="auto"
                    :items="courseItems"
                    item-title="title"
                    item-value="value"
                    label="Course"
                    placeholder="All courses"
                    variant="outlined"
                />

                <v-select
                    v-model="filters.module_id"
                    clearable
                    density="comfortable"
                    hide-details="auto"
                    :items="moduleItems"
                    item-title="title"
                    item-value="value"
                    label="Module"
                    placeholder="All modules"
                    variant="outlined"
                />

                <v-select
                    v-model="filters.type"
                    clearable
                    density="comfortable"
                    hide-details="auto"
                    :items="typeItems"
                    item-title="title"
                    item-value="value"
                    label="Type"
                    placeholder="All types"
                    variant="outlined"
                />

                <v-select
                    v-model="filters.status"
                    clearable
                    density="comfortable"
                    hide-details="auto"
                    :items="statusItems"
                    item-title="title"
                    item-value="value"
                    label="Status"
                    placeholder="All statuses"
                    variant="outlined"
                />

                <div class="course-create-drawer__actions d-flex ga-3 pt-2">
                    <v-btn class="flex-grow-1" color="primary" height="48" size="large" variant="flat" @click="close">
                        Done
                    </v-btn>
                    <v-btn class="flex-grow-1" height="48" size="large" variant="outlined" @click="resetFilters">
                        Reset filters
                    </v-btn>
                </div>
            </div>
        </v-container>
    </v-navigation-drawer>
</template>

<style>
.lesson-filter-drawer.v-navigation-drawer--temporary {
    position: fixed !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    left: auto !important;
    height: 100vh !important;
    max-height: 100vh !important;
    z-index: 11000 !important;
}

.v-navigation-drawer.lesson-filter-drawer ~ .v-navigation-drawer__scrim,
.v-navigation-drawer.lesson-filter-drawer ~ * .v-navigation-drawer__scrim {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 10999 !important;
}
</style>
