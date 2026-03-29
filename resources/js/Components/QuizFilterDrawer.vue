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
    quizzes: { type: Array, default: () => [] },
    courses: { type: Array, default: () => [] },
    modules: { type: Array, default: () => [] },
});

const typeItems = [
    { title: 'Lesson quiz', value: 'lesson' },
    { title: 'Module quiz', value: 'module' },
    { title: 'Final exam', value: 'final_exam' },
    { title: 'Placement', value: 'placement' },
];

const statusItems = [
    { title: 'Draft', value: 'draft' },
    { title: 'Published', value: 'published' },
];

const titleItems = computed(() => {
    const set = new Set();
    for (const q of props.quizzes) {
        const t = q.title;
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
        class="quiz-filter-drawer"
        location="end"
        temporary
        width="420"
        scrim
    >
        <v-toolbar class="align-start py-4" color="primary" flat height="auto">
            <v-btn class="mt-1" color="white" icon="mdi-close" variant="text" @click="close" />
            <div class="ms-2 flex min-w-0 flex-1 flex-col gap-1 ps-0">
                <div class="d-flex align-center ga-2">
                    <v-icon color="white" icon="mdi-filter-variant" size="28" />
                    <v-toolbar-title class="text-h6 font-weight-bold text-white pa-0"> Filter quizzes </v-toolbar-title>
                </div>
            </div>
        </v-toolbar>
        <v-divider />

        <v-container class="py-6" fluid>
            <div class="d-flex flex-column ga-4">
                <v-text-field
                    v-model="filters.search"
                    autocomplete="off"
                    clearable
                    density="comfortable"
                    hide-details="auto"
                    label="Search title"
                    prepend-inner-icon="mdi-magnify"
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
                    label="Quiz type"
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
                    variant="outlined"
                />

                <div class="d-flex ga-3 pt-2">
                    <v-btn class="flex-grow-1" color="primary" size="large" variant="flat" @click="close"> Apply </v-btn>
                    <v-btn class="flex-grow-1" size="large" variant="outlined" @click="resetFilters"> Reset </v-btn>
                </div>
            </div>
        </v-container>
    </v-navigation-drawer>
</template>

<style>
.quiz-filter-drawer.v-navigation-drawer--temporary {
    position: fixed !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    left: auto !important;
    height: 100vh !important;
    max-height: 100vh !important;
    z-index: 11000 !important;
}
</style>
