<script setup>
import { computed } from 'vue';

const open = defineModel({ type: Boolean, default: false });

const filters = defineModel('filters', {
    type: Object,
    default: () => ({
        first_name: null,
        last_name: null,
        email: null,
        status: null,
        gender: null,
    }),
});

const props = defineProps({
    students: { type: Array, default: () => [] },
});

const statusItems = [
    { title: 'Active', value: 'active' },
    { title: 'Inactive', value: 'inactive' },
    { title: 'Pending', value: 'pending' },
];

const genderItems = [
    { title: 'Male', value: 'male' },
    { title: 'Female', value: 'female' },
    { title: 'Other', value: 'other' },
    { title: 'Prefer not to say', value: 'prefer_not_to_say' },
];

const firstNameItems = computed(() => {
    const set = new Set();
    for (const s of props.students) {
        const t = s.first_name;
        if (t && String(t).trim()) {
            set.add(String(t).trim());
        }
    }
    return [...set]
        .sort((a, b) => a.localeCompare(b))
        .map((first_name) => ({ title: first_name, value: first_name }));
});

const lastNameItems = computed(() => {
    const set = new Set();
    for (const s of props.students) {
        const t = s.last_name;
        if (t && String(t).trim()) {
            set.add(String(t).trim());
        }
    }
    return [...set]
        .sort((a, b) => a.localeCompare(b))
        .map((last_name) => ({ title: last_name, value: last_name }));
});

const emailItems = computed(() => {
    const set = new Set();
    for (const s of props.students) {
        const t = s.user?.email;
        if (t && String(t).trim()) {
            set.add(String(t).trim());
        }
    }
    return [...set]
        .sort((a, b) => a.localeCompare(b))
        .map((email) => ({ title: email, value: email }));
});

function resetFilters() {
    filters.value = {
        first_name: null,
        last_name: null,
        email: null,
        status: null,
        gender: null,
    };
}

function close() {
    open.value = false;
}
</script>

<template>
    <v-navigation-drawer
        v-model="open"
        class="course-create-drawer student-filter-drawer"
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
                    <v-toolbar-title class="text-h6 font-weight-bold text-white pa-0">
                        Filter students
                    </v-toolbar-title>
                </div>
                <p class="mb-0 text-body-2 text-white opacity-90">
                    Ngushtoni listën sipas emrit, email-it, statusit ose gjinitë.
                </p>
            </div>
        </v-toolbar>
        <v-divider />

        <v-container class="py-6" fluid>
            <div class="d-flex flex-column ga-4">
                <v-select
                    v-model="filters.first_name"
                    clearable
                    density="comfortable"
                    hide-details="auto"
                    :items="firstNameItems"
                    item-title="title"
                    item-value="value"
                    label="First name"
                    placeholder="All first names"
                    variant="outlined"
                />

                <v-select
                    v-model="filters.last_name"
                    clearable
                    density="comfortable"
                    hide-details="auto"
                    :items="lastNameItems"
                    item-title="title"
                    item-value="value"
                    label="Last name"
                    placeholder="All last names"
                    variant="outlined"
                />

                <v-select
                    v-model="filters.email"
                    clearable
                    density="comfortable"
                    hide-details="auto"
                    :items="emailItems"
                    item-title="title"
                    item-value="value"
                    label="Email"
                    placeholder="All emails"
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

                <v-select
                    v-model="filters.gender"
                    clearable
                    density="comfortable"
                    hide-details="auto"
                    :items="genderItems"
                    item-title="title"
                    item-value="value"
                    label="Gender"
                    placeholder="All"
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
.student-filter-drawer.v-navigation-drawer--temporary {
    position: fixed !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    left: auto !important;
    height: 100vh !important;
    max-height: 100vh !important;
    z-index: 11000 !important;
}

.v-navigation-drawer.student-filter-drawer ~ .v-navigation-drawer__scrim,
.v-navigation-drawer.student-filter-drawer ~ * .v-navigation-drawer__scrim {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 10999 !important;
}
</style>
