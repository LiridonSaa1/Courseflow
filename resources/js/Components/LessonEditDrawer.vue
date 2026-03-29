<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const model = defineModel({ type: Boolean, default: false });

const props = defineProps({
    lesson: { type: Object, default: null },
    courses: { type: Array, default: () => [] },
    modules: { type: Array, default: () => [] },
});

const courseIdUi = ref(null);

const form = useForm({
    module_id: null,
    title: '',
    short_description: '',
    type: 'reading',
    level: 'A1',
    duration_minutes: null,
    sort_order: null,
    status: 'draft',
});

const levels = ['A1', 'A2', 'B1', 'B2', 'C1'];

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

const courseItems = computed(() =>
    props.courses.map((c) => ({
        title: c.title,
        value: c.id,
    })),
);

const moduleItems = computed(() => {
    const cid = courseIdUi.value;
    let list = props.modules;
    if (cid != null && cid !== '') {
        list = list.filter((m) => m.course_id === cid);
    }
    return list.map((m) => ({
        title: m.title,
        value: m.id,
    }));
});

watch(
    () => form.course_id,
    (courseId) => {
        if (form.module_id == null) {
            return;
        }
        const m = props.modules.find((x) => x.id === form.module_id);
        if (m && courseId != null && m.course_id !== courseId) {
            form.module_id = null;
        }
    },
);

function applyLesson(l) {
    if (!l?.id) {
        return;
    }
    form.module_id = l.module_id ?? null;
    const m = props.modules.find((x) => x.id === form.module_id);
    courseIdUi.value = m?.course_id ?? null;
    form.title = l.title ?? '';
    form.short_description = l.short_description ?? '';
    form.type = l.type ?? 'reading';
    form.level = l.level ?? 'A1';
    form.duration_minutes = l.duration_minutes ?? null;
    form.sort_order = l.sort_order ?? null;
    form.status = l.status === 'published' ? 'published' : 'draft';
}

const canSubmit = computed(() => {
    const titleOk = String(form.title ?? '').trim().length > 0;
    const moduleOk = form.module_id != null && form.module_id !== '';
    const typeOk = typeItems.some((t) => t.value === form.type);
    const statusOk = form.status === 'draft' || form.status === 'published';
    return titleOk && moduleOk && typeOk && statusOk;
});

function close() {
    model.value = false;
}

function submit() {
    if (!canSubmit.value || !props.lesson?.id) {
        return;
    }

    form.patch(`/lessons/${props.lesson.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            form.clearErrors();
            close();
        },
    });
}

watch(
    () => [model.value, props.lesson],
    () => {
        if (model.value && props.lesson) {
            applyLesson(props.lesson);
        }
        if (!model.value) {
            form.clearErrors();
        }
    },
    { deep: true, immediate: true },
);
</script>

<template>
    <v-navigation-drawer
        v-model="model"
        class="course-create-drawer lesson-edit-drawer"
        location="end"
        temporary
        width="480"
        scrim
    >
        <v-toolbar class="align-start py-4" color="primary" flat height="auto">
            <v-btn class="mt-1" color="white" icon="mdi-close" variant="text" @click="close" />
            <div class="ms-2 flex min-w-0 flex-1 flex-col gap-1 ps-0">
                <div class="d-flex align-center ga-2">
                    <v-icon color="white" icon="mdi-pencil-outline" size="28" />
                    <v-toolbar-title class="text-h6 font-weight-bold text-white pa-0"> Edit lesson </v-toolbar-title>
                </div>
                <p class="mb-0 text-body-2 text-white opacity-90">
                    Update basics, type, and publish state. Content blocks are managed on the lesson page.
                </p>
            </div>
        </v-toolbar>
        <v-divider />

        <v-container class="py-6" fluid>
            <v-form class="d-flex flex-column ga-4" @submit.prevent="submit">
                <v-select
                    v-model="courseIdUi"
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
                    v-model="form.module_id"
                    :error-messages="form.errors.module_id"
                    hide-details="auto"
                    :items="moduleItems"
                    item-title="title"
                    item-value="value"
                    label="Module"
                    variant="outlined"
                />

                <v-text-field
                    v-model="form.title"
                    autocomplete="off"
                    density="comfortable"
                    :error-messages="form.errors.title"
                    hide-details="auto"
                    label="Title"
                    variant="outlined"
                />

                <v-textarea
                    v-model="form.short_description"
                    auto-grow
                    density="comfortable"
                    :error-messages="form.errors.short_description"
                    hide-details="auto"
                    label="Short description"
                    rows="2"
                    variant="outlined"
                />

                <v-select
                    v-model="form.type"
                    :error-messages="form.errors.type"
                    hide-details="auto"
                    :items="typeItems"
                    item-title="title"
                    item-value="value"
                    label="Lesson type"
                    variant="outlined"
                />

                <v-select
                    v-model="form.level"
                    hide-details="auto"
                    :items="levels"
                    label="Level"
                    variant="outlined"
                />

                <v-text-field
                    v-model.number="form.duration_minutes"
                    density="comfortable"
                    :error-messages="form.errors.duration_minutes"
                    hide-details="auto"
                    label="Duration (minutes)"
                    min="0"
                    type="number"
                    variant="outlined"
                />

                <v-text-field
                    v-model.number="form.sort_order"
                    density="comfortable"
                    :error-messages="form.errors.sort_order"
                    hide-details="auto"
                    label="Order"
                    type="number"
                    variant="outlined"
                />

                <v-select
                    v-model="form.status"
                    :error-messages="form.errors.status"
                    hide-details="auto"
                    :items="statusItems"
                    item-title="title"
                    item-value="value"
                    label="Status"
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
                    <v-btn class="flex-grow-1" height="48" size="large" type="button" variant="outlined" @click="close">
                        Cancel
                    </v-btn>
                </div>
            </v-form>
        </v-container>
    </v-navigation-drawer>
</template>

<style>
.lesson-edit-drawer.v-navigation-drawer--temporary {
    position: fixed !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    left: auto !important;
    height: 100vh !important;
    max-height: 100vh !important;
    z-index: 11000 !important;
}

.v-navigation-drawer.lesson-edit-drawer ~ .v-navigation-drawer__scrim,
.v-navigation-drawer.lesson-edit-drawer ~ * .v-navigation-drawer__scrim {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 10999 !important;
}
</style>
