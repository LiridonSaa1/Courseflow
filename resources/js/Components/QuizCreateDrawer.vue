<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const model = defineModel({ type: Boolean, default: false });

const props = defineProps({
    courses: { type: Array, default: () => [] },
    lessons: { type: Array, default: () => [] },
    prefillCourseId: { type: Number, default: null },
});

const form = useForm({
    course_id: null,
    lesson_id: null,
    title: '',
    description: '',
    type: 'lesson',
    time_limit_seconds: null,
    pass_mark: 50,
    max_attempts: null,
    is_shuffle_questions: false,
    is_shuffle_answers: false,
    show_results_instantly: true,
    allow_retry: true,
    negative_marking: false,
    show_correct_after_finish: true,
    status: 'draft',
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

const courseItems = computed(() =>
    props.courses.map((c) => ({
        title: c.title,
        value: c.id,
    })),
);

const courseIdUi = ref(null);

const lessonItems = computed(() => {
    const cid = courseIdUi.value;
    let list = props.lessons;
    if (cid != null && cid !== '') {
        list = list.filter((l) => l.module?.course_id === cid);
    }
    return list.map((l) => ({
        title: `${l.title} · ${l.module?.course?.title ?? '—'}`,
        value: l.id,
    }));
});

watch(courseIdUi, (courseId) => {
    if (form.lesson_id == null) {
        return;
    }
    const le = props.lessons.find((x) => x.id === form.lesson_id);
    if (le && courseId != null && le.module?.course_id !== courseId) {
        form.lesson_id = null;
    }
});

watch(
    () => form.lesson_id,
    (lid) => {
        if (lid == null) {
            return;
        }
        const le = props.lessons.find((x) => x.id === lid);
        if (le?.module?.course_id) {
            courseIdUi.value = le.module.course_id;
        }
    },
);

function applyPrefill() {
    const cid = props.prefillCourseId;
    if (cid != null && cid !== '') {
        courseIdUi.value = cid;
    }
}

watch(
    () => model.value,
    (open) => {
        if (open) {
            applyPrefill();
        } else {
            form.clearErrors();
        }
    },
);

const canSubmit = computed(() => {
    const titleOk = String(form.title ?? '').trim().length > 0;
    const scopeOk =
        (form.lesson_id != null && form.lesson_id !== '') ||
        (courseIdUi.value != null && courseIdUi.value !== '');
    return titleOk && scopeOk;
});

function close() {
    model.value = false;
}

function submit() {
    if (!canSubmit.value) {
        return;
    }

    form.course_id = form.lesson_id ? null : courseIdUi.value;
    form.post('/quizzes', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.type = 'lesson';
            form.status = 'draft';
            form.pass_mark = 50;
            form.show_results_instantly = true;
            form.allow_retry = true;
            form.show_correct_after_finish = true;
            courseIdUi.value = null;
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
        class="quiz-create-drawer"
        location="end"
        temporary
        width="480"
        scrim
    >
        <v-toolbar class="align-start py-4" color="primary" flat height="auto">
            <v-btn class="mt-1" color="white" icon="mdi-close" variant="text" @click="close" />
            <div class="ms-2 flex min-w-0 flex-1 flex-col gap-1 ps-0">
                <div class="d-flex align-center ga-2">
                    <v-icon color="white" icon="mdi-help-circle-outline" size="28" />
                    <v-toolbar-title class="text-h6 font-weight-bold text-white pa-0"> New quiz </v-toolbar-title>
                </div>
                <p class="mb-0 text-body-2 text-white opacity-90">
                    Choose a course (or a specific lesson). Add questions from the lesson quiz editor when linked to a
                    lesson.
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
                    v-model="form.lesson_id"
                    clearable
                    density="comfortable"
                    hide-details="auto"
                    :items="lessonItems"
                    item-title="title"
                    item-value="value"
                    label="Lesson (optional)"
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
                    v-model="form.description"
                    auto-grow
                    density="comfortable"
                    :error-messages="form.errors.description"
                    hide-details="auto"
                    label="Description"
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
                    label="Quiz type"
                    variant="outlined"
                />

                <v-text-field
                    v-model.number="form.time_limit_seconds"
                    density="comfortable"
                    :error-messages="form.errors.time_limit_seconds"
                    hide-details="auto"
                    hint="Seconds (empty = no limit)"
                    label="Time limit (seconds)"
                    min="0"
                    persistent-hint
                    type="number"
                    variant="outlined"
                />

                <v-text-field
                    v-model.number="form.pass_mark"
                    density="comfortable"
                    :error-messages="form.errors.pass_mark"
                    hide-details="auto"
                    label="Pass mark (%)"
                    max="100"
                    min="0"
                    type="number"
                    variant="outlined"
                />

                <v-text-field
                    v-model.number="form.max_attempts"
                    density="comfortable"
                    :error-messages="form.errors.max_attempts"
                    hide-details="auto"
                    hint="Empty = unlimited"
                    label="Max attempts"
                    min="1"
                    persistent-hint
                    type="number"
                    variant="outlined"
                />

                <v-switch v-model="form.is_shuffle_questions" color="primary" density="compact" hide-details inset label="Shuffle questions" />
                <v-switch v-model="form.is_shuffle_answers" color="primary" density="compact" hide-details inset label="Shuffle answers" />
                <v-switch v-model="form.show_results_instantly" color="primary" density="compact" hide-details inset label="Show results instantly" />
                <v-switch v-model="form.allow_retry" color="primary" density="compact" hide-details inset label="Allow retry" />
                <v-switch v-model="form.negative_marking" color="primary" density="compact" hide-details inset label="Negative marking" />
                <v-switch
                    v-model="form.show_correct_after_finish"
                    color="primary"
                    density="compact"
                    hide-details
                    inset
                    label="Show correct answers after finish"
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

                <div class="d-flex ga-3 pt-2">
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
.quiz-create-drawer.v-navigation-drawer--temporary {
    position: fixed !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    left: auto !important;
    height: 100vh !important;
    max-height: 100vh !important;
    z-index: 11000 !important;
}

.v-navigation-drawer.quiz-create-drawer ~ .v-navigation-drawer__scrim,
.v-navigation-drawer.quiz-create-drawer ~ * .v-navigation-drawer__scrim {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 10999 !important;
}
</style>
