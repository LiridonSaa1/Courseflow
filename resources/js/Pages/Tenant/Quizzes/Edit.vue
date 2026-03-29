<script setup>
import QuizQuestionBuilder from '@/Components/QuizQuestionBuilder.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ lesson: Object, quiz: Object });

function mapQuestionsFromServer(questions) {
    if (!questions?.length) {
        return [
            {
                type: 'multiple_choice',
                points: 1,
                payload: {
                    question: '',
                    options: ['', '', '', ''],
                    correct: '0',
                },
                explanation: '',
            },
        ];
    }
    return questions.map((q) => {
        const opts = [...(q.payload?.options ?? [])];
        while (opts.length < 2) {
            opts.push('');
        }
        return {
            type: q.type || 'multiple_choice',
            points: q.points ?? 1,
            payload: {
                question: q.payload?.question ?? '',
                options: opts.length ? opts : ['', ''],
                correct: String(q.payload?.correct ?? '0'),
            },
            explanation: q.explanation ?? '',
        };
    });
}

const clientError = ref('');

const form = useForm({
    title: props.quiz.title,
    description: props.quiz.description ?? '',
    time_limit_seconds: props.quiz.time_limit_seconds,
    randomize: props.quiz.is_shuffle_questions ?? props.quiz.randomize ?? false,
    questions: mapQuestionsFromServer(props.quiz.questions),
});

function normalizeQuestions() {
    return form.questions.map((q) => {
        const opts = (q.payload?.options ?? []).map((s) => String(s).trim()).filter(Boolean);
        let correctIdx = parseInt(String(q.payload?.correct ?? '0'), 10);
        if (Number.isNaN(correctIdx)) {
            correctIdx = 0;
        }
        if (opts.length > 0) {
            correctIdx = Math.min(Math.max(0, correctIdx), opts.length - 1);
        }
        return {
            type: 'multiple_choice',
            points: Number(q.points) >= 1 ? Number(q.points) : 1,
            payload: {
                question: String(q.payload?.question ?? '').trim(),
                options: opts,
                correct: String(opts.length ? correctIdx : 0),
            },
            explanation: String(q.explanation ?? '').trim() || null,
        };
    });
}

function validateQuestions() {
    for (let i = 0; i < form.questions.length; i++) {
        const q = form.questions[i];
        const opts = (q.payload?.options ?? []).map((s) => String(s).trim()).filter(Boolean);
        if (!String(q.payload?.question ?? '').trim()) {
            return `Question ${i + 1}: enter the question text.`;
        }
        if (opts.length < 2) {
            return `Question ${i + 1}: add at least two non-empty answer options.`;
        }
    }
    return null;
}

function submit() {
    clientError.value = '';
    const msg = validateQuestions();
    if (msg) {
        clientError.value = msg;
        return;
    }

    form
        .transform((data) => ({
            ...data,
            questions: normalizeQuestions(),
            is_shuffle_questions: data.randomize,
        }))
        .put(`/lessons/${props.lesson.id}/quizzes/${props.quiz.id}`);
}
</script>

<template>
    <TenantLayout>
        <Head title="Edit quiz" />

        <v-card class="mb-4 mx-auto" max-width="900" rounded="lg" variant="outlined">
            <v-card-text class="pa-6">
                <Link class="text-decoration-none text-primary text-body-2" :href="`/lessons/${lesson.id}/quizzes`">
                    ← Back to quizzes
                </Link>
                <h1 class="text-h5 font-weight-bold mt-4 mb-1">Edit quiz</h1>
                <p class="text-body-2 text-medium-emphasis mb-6">Update settings and questions.</p>

                <v-form class="d-flex flex-column ga-4" @submit.prevent="submit">
                    <v-text-field
                        v-model="form.title"
                        density="comfortable"
                        :error-messages="form.errors.title"
                        hide-details="auto"
                        label="Quiz title"
                        variant="outlined"
                    />

                    <v-textarea
                        v-model="form.description"
                        auto-grow
                        density="comfortable"
                        :error-messages="form.errors.description"
                        hide-details="auto"
                        label="Description (optional)"
                        rows="2"
                        variant="outlined"
                    />

                    <v-text-field
                        v-model.number="form.time_limit_seconds"
                        density="comfortable"
                        :error-messages="form.errors.time_limit_seconds"
                        hide-details="auto"
                        hint="Optional. Empty = no limit."
                        label="Time limit (seconds)"
                        min="0"
                        persistent-hint
                        type="number"
                        variant="outlined"
                    />

                    <v-checkbox
                        v-model="form.randomize"
                        density="comfortable"
                        hide-details
                        label="Shuffle question order"
                    />

                    <v-alert
                        v-if="clientError"
                        class="mb-0"
                        density="comfortable"
                        type="warning"
                        variant="tonal"
                    >
                        {{ clientError }}
                    </v-alert>

                    <QuizQuestionBuilder v-model="form.questions" :errors="form.errors" />

                    <div class="d-flex flex-wrap ga-3 pt-2">
                        <v-btn color="primary" :loading="form.processing" size="large" type="submit" variant="flat">
                            Save changes
                        </v-btn>
                        <Link class="text-decoration-none" :href="`/lessons/${lesson.id}/quizzes`">
                            <v-btn size="large" variant="text"> Cancel </v-btn>
                        </Link>
                    </div>
                </v-form>
            </v-card-text>
        </v-card>
    </TenantLayout>
</template>
