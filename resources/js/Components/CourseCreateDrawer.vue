<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const model = defineModel({ type: Boolean, default: false });

const props = defineProps({
    teachers: { type: Array, default: () => [] },
});

const form = useForm({
    title: '',
    language: '',
    level: 'A1',
    description: '',
    teacher_id: null,
    thumbnail: '',
    status: 'draft',
});

const levels = ['A1', 'A2', 'B1', 'B2', 'C1'];

/** Menus must stack above the drawer (z-index 11000) or clicks/options break. */
const selectMenuProps = {
    zIndex: 12050,
    scrollStrategy: 'close',
};

const statusItems = [
    { title: 'Draft', value: 'draft' },
    { title: 'Published', value: 'published' },
];

const teacherItems = computed(() =>
    props.teachers.map((t) => ({
        title: t.user?.name ?? `Teacher #${t.id}`,
        value: Number(t.id),
    })),
);

/** Mirrors CourseController@store required rules: title, language, level, status */
const canSubmit = computed(() => {
    const titleOk = String(form.title ?? '').trim().length > 0;
    const languageOk = String(form.language ?? '').trim().length > 0;
    const levelOk = String(form.level ?? '').trim().length > 0;
    const statusOk = form.status === 'draft' || form.status === 'published';
    return titleOk && languageOk && levelOk && statusOk;
});

function close() {
    model.value = false;
}

function submit() {
    if (!canSubmit.value) {
        return;
    }

    form.post('/courses', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.teacher_id = null;
            form.thumbnail = '';
            form.status = 'draft';
            form.clearErrors();
            close();
        },
    });
}

watch(model, (open) => {
    if (!open) {
        form.clearErrors();
    }
});
</script>

<template>
    <v-navigation-drawer
        v-model="model"
        class="course-create-drawer"
        location="end"
        temporary
        width="480"
        scrim
    >
        <v-toolbar class="align-start py-4" color="primary" flat height="auto">
            <v-btn class="mt-1" color="white" icon="mdi-close" variant="text" @click="close" />
            <div class="ms-2 flex min-w-0 flex-1 flex-col gap-1 ps-0">
                <div class="d-flex align-center ga-2">
                    <v-icon color="white" icon="mdi-book-plus-outline" size="28" />
                    <v-toolbar-title class="text-h6 font-weight-bold text-white pa-0">
                        New course
                    </v-toolbar-title>
                </div>
                <p class="mb-0 text-body-2 text-white opacity-90">
                    Plotësoni titullin, gjuhën dhe mësimdhënësin. Ruajeni si draft dhe publikojeni kur të jeni gati.
                </p>
            </div>
        </v-toolbar>
        <v-divider />

        <v-container class="py-6" fluid>
            <v-form class="d-flex flex-column ga-4" @submit.prevent="submit">
                <v-text-field
                    v-model="form.title"
                    autocomplete="off"
                    density="comfortable"
                    :error-messages="form.errors.title"
                    hide-details="auto"
                    label="Title"
                    variant="outlined"
                />

                <v-text-field
                    v-model="form.language"
                    autocomplete="off"
                    density="comfortable"
                    :error-messages="form.errors.language"
                    hide-details="auto"
                    label="Language"
                    variant="outlined"
                />

                <v-select
                    v-model="form.level"
                    :error-messages="form.errors.level"
                    hide-details="auto"
                    :items="levels"
                    :menu-props="selectMenuProps"
                    label="Level"
                    variant="outlined"
                />


                <v-select
                    v-model="form.teacher_id"
                    clearable
                    :error-messages="form.errors.teacher_id"
                    hide-details="auto"
                    :items="teacherItems"
                    item-title="title"
                    item-value="value"
                    :menu-props="selectMenuProps"
                    label="Teacher"
                    variant="outlined"
                />

                <!-- <v-text-field
                    v-model="form.thumbnail"
                    autocomplete="off"
                    density="comfortable"
                    :error-messages="form.errors.thumbnail"
                    hide-details="auto"
                    hint="Image URL or path"
                    label="Thumbnail"
                    persistent-hint
                    variant="outlined"
                /> -->

                <v-select
                    v-model="form.status"
                    :error-messages="form.errors.status"
                    hide-details="auto"
                    :items="statusItems"
                    item-title="title"
                    item-value="value"
                    :menu-props="selectMenuProps"
                    label="Status"
                    variant="outlined"
                />

                
                <v-textarea
                    v-model="form.description"
                    auto-grow
                    density="comfortable"
                    :error-messages="form.errors.description"
                    hide-details="auto"
                    label="Description"
                    rows="3"
                    variant="outlined"
                />

                <div class="course-create-drawer__actions d-flex ga-3 pt-2">
                    <v-btn
                        class="flex-grow-1"
                        color="primary"
                        :disabled="!canSubmit || form.processing"
                        height="48"
                        :loading="form.processing"
                        size="large"
                        type="submit"
                        variant="flat"
                    >
                        Save
                    </v-btn>
                    <v-btn
                        class="flex-grow-1"
                        height="48"
                        size="large"
                        type="button"
                        variant="outlined"
                        @click="close"
                    >
                        Cancel
                    </v-btn>
                </div>
            </v-form>
        </v-container>
    </v-navigation-drawer>
</template>

<style>
/*
 * TenantLayout: fixed header z-30, sidebar z-40. Default drawer is absolute in v-app layout,
 * so it sits under the top bar. Pin the panel + scrim to the viewport above app chrome.
 */
.course-create-drawer.v-navigation-drawer--temporary {
    position: fixed !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    left: auto !important;
    height: 100vh !important;
    max-height: 100vh !important;
    z-index: 11000 !important;
}

/* Scrim follows the drawer (may be wrapped by Transition) */
.v-navigation-drawer.course-create-drawer ~ .v-navigation-drawer__scrim,
.v-navigation-drawer.course-create-drawer ~ * .v-navigation-drawer__scrim {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 10999 !important;
}
</style>
