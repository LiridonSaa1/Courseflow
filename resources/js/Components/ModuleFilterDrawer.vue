<script setup>
import { computed } from 'vue';

const open = defineModel({ type: Boolean, default: false });

const filters = defineModel('filters', {
    type: Object,
    default: () => ({
        search: '',
        title: null,
        status: null,
    }),
});

const props = defineProps({
    modules: { type: Array, default: () => [] },
});

const titleItems = computed(() => {
    const set = new Set();
    for (const m of props.modules) {
        const t = m.title;
        if (t && String(t).trim()) {
            set.add(String(t).trim());
        }
    }
    return [...set]
        .sort((a, b) => a.localeCompare(b))
        .map((title) => ({ title, value: title }));
});

const statusItems = [
    { title: 'Draft', value: 'draft' },
    { title: 'Published', value: 'published' },
];

function resetFilters() {
    filters.value = {
        search: '',
        title: null,
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
        class="module-filter-drawer"
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
                    <v-toolbar-title class="text-h6 font-weight-bold text-white pa-0"> Filter modules </v-toolbar-title>
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
                    label="Search title or description"
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
.module-filter-drawer.v-navigation-drawer--temporary {
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
