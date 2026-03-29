<script setup>
import { computed } from 'vue';

const open = defineModel({ type: Boolean, default: false });

const filters = defineModel('filters', {
    type: Object,
    default: () => ({
        title: null,
        language: null,
        level: null,
        teacher_id: null,
        status: null,
    }),
});

const props = defineProps({
    teachers: { type: Array, default: () => [] },
    courses: { type: Array, default: () => [] },
});

const levels = ['A1', 'A2', 'B1', 'B2', 'C1'];

const statusItems = [
    { title: 'Draft', value: 'draft' },
    { title: 'Published', value: 'published' },
];

const teacherItems = computed(() =>
    props.teachers.map((t) => ({
        title: t.user?.name ?? `Teacher #${t.id}`,
        value: t.id,
    })),
);

const titleItems = computed(() => {
    const set = new Set();
    for (const c of props.courses) {
        const t = c.title;
        if (t && String(t).trim()) {
            set.add(String(t).trim());
        }
    }
    return [...set]
        .sort((a, b) => a.localeCompare(b))
        .map((title) => ({ title, value: title }));
});

const languageItems = computed(() => {
    const set = new Set();
    for (const c of props.courses) {
        const lang = c.language;
        if (lang && String(lang).trim()) {
            set.add(String(lang).trim());
        }
    }
    return [...set]
        .sort((a, b) => a.localeCompare(b))
        .map((language) => ({ title: language, value: language }));
});

function resetFilters() {
    filters.value = {
        title: null,
        language: null,
        level: null,
        teacher_id: null,
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
        class="course-create-drawer course-filter-drawer"
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
                    <v-toolbar-title class="text-h6 font-weight-bold text-white pa-0"> Filter courses </v-toolbar-title>
                </div>
                <p class="mb-0 text-body-2 text-white opacity-90">
                    Zgjidhni titullin, gjuhën, nivelin, mësimdhënësin ose statusin për të ngushtuar listën në tabelë.
                </p>
            </div>
        </v-toolbar>
        <v-divider />

        <v-container class="py-6" fluid>
            <div class="d-flex flex-column ga-4">
                <v-select
                    v-model="filters.title"
                    clearable
                    density="comfortable"
                    hide-details="auto"
                    :items="titleItems"
                    item-title="title"
                    item-value="value"
                    label="Title"
                    placeholder="All titles"
                    variant="outlined"
                />

                <v-select
                    v-model="filters.language"
                    clearable
                    density="comfortable"
                    hide-details="auto"
                    :items="languageItems"
                    item-title="title"
                    item-value="value"
                    label="Language"
                    placeholder="All languages"
                    variant="outlined"
                />

                <v-select
                    v-model="filters.level"
                    clearable
                    density="comfortable"
                    hide-details="auto"
                    :items="levels"
                    label="Level"
                    placeholder="All levels"
                    variant="outlined"
                />

                <v-select
                    v-model="filters.teacher_id"
                    clearable
                    density="comfortable"
                    hide-details="auto"
                    :items="teacherItems"
                    item-title="title"
                    item-value="value"
                    label="Teacher"
                    placeholder="All teachers"
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
                    <v-btn
                        class="flex-grow-1"
                        color="primary"
                        height="48"
                        size="large"
                        variant="flat"
                        @click="close"
                    >
                        Done
                    </v-btn>
                    <v-btn
                        class="flex-grow-1"
                        height="48"
                        size="large"
                        variant="outlined"
                        @click="resetFilters"
                    >
                        Reset filters
                    </v-btn>
                </div>
            </div>
        </v-container>
    </v-navigation-drawer>
</template>

<style>
.course-filter-drawer.v-navigation-drawer--temporary {
    position: fixed !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    left: auto !important;
    height: 100vh !important;
    max-height: 100vh !important;
    z-index: 11000 !important;
}

.v-navigation-drawer.course-filter-drawer ~ .v-navigation-drawer__scrim,
.v-navigation-drawer.course-filter-drawer ~ * .v-navigation-drawer__scrim {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 10999 !important;
}
</style>
