<script setup>
const props = defineProps({
    modelValue: { type: Array, required: true },
    errors: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:modelValue']);

function defaultQuestion() {
    return {
        type: 'multiple_choice',
        points: 1,
        payload: {
            question: '',
            options: ['', '', '', ''],
            correct: '0',
        },
        explanation: '',
    };
}

function updateAt(index, patch) {
    const next = props.modelValue.map((q, i) => (i === index ? { ...q, ...patch } : q));
    emit('update:modelValue', next);
}

function updatePayload(index, payloadPatch) {
    const q = props.modelValue[index];
    const next = [...props.modelValue];
    next[index] = {
        ...q,
        payload: { ...q.payload, ...payloadPatch },
    };
    emit('update:modelValue', next);
}

function addQuestion() {
    emit('update:modelValue', [...props.modelValue, defaultQuestion()]);
}

function removeQuestion(index) {
    const next = props.modelValue.filter((_, i) => i !== index);
    emit('update:modelValue', next.length ? next : [defaultQuestion()]);
}

function addOption(qIndex) {
    const q = props.modelValue[qIndex];
    const opts = [...(q.payload?.options ?? []), ''];
    updatePayload(qIndex, { options: opts });
}

function updateOption(qIndex, optIndex, value) {
    const q = props.modelValue[qIndex];
    const opts = [...(q.payload?.options ?? [])];
    opts[optIndex] = value;
    updatePayload(qIndex, { options: opts });
}

function removeOption(qIndex, optIndex) {
    const q = props.modelValue[qIndex];
    const opts = (q.payload?.options ?? []).filter((_, i) => i !== optIndex);
    updatePayload(qIndex, { options: opts.length ? opts : [''] });
    const maxIdx = Math.max(0, opts.length - 1);
    const cur = parseInt(q.payload?.correct ?? '0', 10);
    if (cur > maxIdx) {
        updatePayload(qIndex, { correct: String(maxIdx) });
    }
}

function correctItemsForIndex(qIndex) {
    const q = props.modelValue[qIndex];
    const opts = q?.payload?.options ?? [];
    return opts.map((text, i) => ({
        title: `${i + 1}. ${String(text).trim() || '(empty)'}`,
        value: String(i),
    }));
}
</script>

<template>
    <div class="d-flex flex-column ga-4">
        <div class="d-flex align-center justify-space-between flex-wrap ga-2">
            <h2 class="text-h6 font-weight-bold mb-0">Questions</h2>
            <v-btn color="primary" prepend-icon="mdi-plus" size="small" variant="tonal" @click="addQuestion">
                Add question
            </v-btn>
        </div>

        <p class="text-body-2 text-medium-emphasis mb-0">
            Each question is multiple choice: prompt, at least two answer options, and which option is correct.
        </p>

        <v-alert
            v-if="errors.questions"
            class="mb-0"
            density="compact"
            type="error"
            variant="tonal"
        >
            {{ errors.questions }}
        </v-alert>

        <v-card
            v-for="(q, qIndex) in modelValue"
            :key="qIndex"
            border
            class="quiz-question-card"
            rounded="lg"
            variant="outlined"
        >
            <v-card-item class="pb-2">
                <div class="d-flex align-center justify-space-between gap-2">
                    <v-card-title class="text-subtitle-1 pa-0"> Question {{ qIndex + 1 }} </v-card-title>
                    <v-btn
                        color="error"
                        density="comfortable"
                        icon="mdi-delete-outline"
                        size="small"
                        variant="text"
                        @click="removeQuestion(qIndex)"
                    />
                </div>
            </v-card-item>
            <v-card-text class="pt-0">
                <div class="d-flex flex-column ga-3">
                    <v-text-field
                        :model-value="q.points"
                        density="comfortable"
                        hide-details="auto"
                        label="Points"
                        min="1"
                        type="number"
                        variant="outlined"
                        @update:model-value="(v) => updateAt(qIndex, { points: v === '' ? 1 : Number(v) })"
                    />

                    <v-textarea
                        :model-value="q.payload?.question ?? ''"
                        auto-grow
                        density="comfortable"
                        hide-details="auto"
                        label="Question text"
                        rows="2"
                        variant="outlined"
                        @update:model-value="(v) => updatePayload(qIndex, { question: v })"
                    />

                    <div>
                        <div class="text-caption font-weight-medium text-medium-emphasis mb-2">Answer options</div>
                        <div class="d-flex flex-column ga-2">
                            <div
                                v-for="(opt, oIndex) in q.payload?.options ?? []"
                                :key="oIndex"
                                class="d-flex align-center ga-2"
                            >
                                <v-text-field
                                    :model-value="opt"
                                    class="flex-grow-1"
                                    density="comfortable"
                                    hide-details="auto"
                                    :label="`Option ${oIndex + 1}`"
                                    variant="outlined"
                                    @update:model-value="(v) => updateOption(qIndex, oIndex, v)"
                                />
                                <v-btn
                                    v-if="(q.payload?.options?.length ?? 0) > 2"
                                    color="error"
                                    density="comfortable"
                                    icon="mdi-close"
                                    size="small"
                                    variant="text"
                                    @click="removeOption(qIndex, oIndex)"
                                />
                            </div>
                        </div>
                        <v-btn class="mt-1" size="small" variant="text" @click="addOption(qIndex)"> + Add option </v-btn>
                    </div>

                    <v-select
                        :model-value="String(q.payload?.correct ?? '0')"
                        density="comfortable"
                        hide-details="auto"
                        item-title="title"
                        item-value="value"
                        :items="correctItemsForIndex(qIndex)"
                        label="Correct answer"
                        variant="outlined"
                        @update:model-value="(v) => updatePayload(qIndex, { correct: String(v) })"
                    />

                    <v-textarea
                        :model-value="q.explanation ?? ''"
                        auto-grow
                        density="comfortable"
                        hide-details="auto"
                        label="Explanation (optional, shown after submit)"
                        rows="2"
                        variant="outlined"
                        @update:model-value="(v) => updateAt(qIndex, { explanation: v })"
                    />
                </div>
            </v-card-text>
        </v-card>
    </div>
</template>

<style scoped>
.quiz-question-card {
    border-color: rgba(var(--v-theme-outline-variant), 0.9);
}
</style>
