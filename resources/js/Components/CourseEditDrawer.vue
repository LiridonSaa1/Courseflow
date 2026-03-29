<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const model = defineModel({ type: Boolean, default: false });

const props = defineProps({
    course: { type: Object, default: null },
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

const canSubmit = computed(() => {
    const titleOk = String(form.title ?? '').trim().length > 0;
    const languageOk = String(form.language ?? '').trim().length > 0;
    const levelOk = String(form.level ?? '').trim().length > 0;
    const statusOk = form.status === 'draft' || form.status === 'published';
    return titleOk && languageOk && levelOk && statusOk;
});

function applyCourse(c) {
    if (!c?.id) {
        return;
    }
    form.title = c.title ?? '';
    form.language = c.language ?? '';
    form.level = c.level ?? 'A1';
    form.description = c.description ?? '';
    form.teacher_id = c.teacher_id ?? null;
    form.thumbnail = c.thumbnail ?? '';
    form.status = c.status === 'published' ? 'published' : 'draft';
}

function close() {
    model.value = false;
}

function submit() {
    if (!canSubmit.value || !props.course?.id) {
        return;
    }

    form.put(`/courses/${props.course.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            form.clearErrors();
            close();
        },
    });
}

watch(
    () => [model.value, props.course],
    () => {
        if (model.value && props.course) {
            applyCourse(props.course);
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
                    <v-icon color="white" icon="mdi-pencil-outline" size="28" />
                    <v-toolbar-title class="text-h6 font-weight-bold text-white pa-0"> Edit course </v-toolbar-title>
                </div>
                <p class="mb-0 text-body-2 text-white opacity-90">
                    Përditësoni të dhënat e kursit. Ruajeni ndryshimet kur të jeni gati.
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
                    label="Teacher"
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

.v-navigation-drawer.course-create-drawer ~ .v-navigation-drawer__scrim,
.v-navigation-drawer.course-create-drawer ~ * .v-navigation-drawer__scrim {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 10999 !important;
}
</style>
