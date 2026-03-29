<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    lesson: { type: Object, required: true },
    prevLesson: { type: Object, default: null },
    nextLesson: { type: Object, default: null },
    progress: { type: Object, default: null },
    canManage: { type: Boolean, default: false },
});

const moduleId = computed(() => props.lesson.module_id);

const progressPercent = computed(() => {
    const p = props.progress;
    if (!p) {
        return 0;
    }
    if (p.completed_at) {
        return 100;
    }
    return Math.min(100, Math.max(0, Number(p.percent) || 0));
});

const contentForm = useForm({
    content_type: 'text',
    title: '',
    content: '',
    file_url: '',
});

function submitContentBlock() {
    contentForm.post(`/lessons/${props.lesson.id}/contents`, {
        preserveScroll: true,
        onSuccess: () => {
            contentForm.reset();
            contentForm.content_type = 'text';
        },
    });
}

function removeBlock(block) {
    if (!confirm('Remove this block?')) {
        return;
    }
    router.delete(`/lessons/${props.lesson.id}/contents/${block.id}`, {
        preserveScroll: true,
    });
}

function markComplete() {
    router.post(`/lessons/${props.lesson.id}/progress`, {
        completed: true,
    });
}

function lessonHref(l) {
    if (!l?.id) {
        return '#';
    }
    return `/modules/${moduleId.value}/lessons/${l.id}`;
}

function renderLegacyBody() {
    const c = props.lesson.content;
    if (c && typeof c === 'object' && c.body) {
        return String(c.body);
    }
    return '';
}
</script>

<template>
    <TenantLayout>
        <Head :title="lesson.title" />

        <div class="d-flex flex-wrap align-center ga-2 mb-4">
            <Link class="text-decoration-none text-primary" :href="`/courses/${lesson.module.course.id}`">
                ← {{ lesson.module.course.title }}
            </Link>
            <span class="text-medium-emphasis">/</span>
            <span class="text-body-2 text-medium-emphasis">{{ lesson.module.title }}</span>
        </div>

        <v-card class="mb-4" rounded="lg" variant="outlined">
            <v-card-text>
                <div class="d-flex flex-wrap justify-space-between align-start ga-4">
                    <div>
                        <h1 class="text-h4 font-weight-bold">{{ lesson.title }}</h1>
                        <p v-if="lesson.short_description" class="text-body-1 text-medium-emphasis mt-2">
                            {{ lesson.short_description }}
                        </p>
                        <div class="d-flex flex-wrap ga-2 mt-3">
                            <v-chip size="small" variant="tonal">{{ lesson.type }}</v-chip>
                            <v-chip v-if="lesson.level" size="small" variant="tonal">{{ lesson.level }}</v-chip>
                            <v-chip v-if="lesson.duration_minutes != null" size="small" variant="tonal">
                                {{ lesson.duration_minutes }} min
                            </v-chip>
                            <v-chip
                                :color="lesson.status === 'published' ? 'success' : 'default'"
                                size="small"
                                variant="tonal"
                            >
                                {{ lesson.status === 'published' ? 'Published' : 'Draft' }}
                            </v-chip>
                        </div>
                    </div>
                    <div class="flex-grow-1" style="min-width: 200px; max-width: 360px">
                        <div class="text-caption text-medium-emphasis mb-1">Your progress</div>
                        <v-progress-linear
                            color="primary"
                            height="10"
                            rounded
                            :model-value="progressPercent"
                        />
                    </div>
                </div>
            </v-card-text>
        </v-card>

        <div class="d-flex flex-wrap ga-3 mb-6">
            <v-btn
                v-if="prevLesson"
                :href="lessonHref(prevLesson)"
                prepend-icon="mdi-arrow-left"
                variant="tonal"
                @click.prevent="router.visit(lessonHref(prevLesson))"
            >
                Previous
            </v-btn>
            <v-btn
                v-if="nextLesson"
                :href="lessonHref(nextLesson)"
                append-icon="mdi-arrow-right"
                color="primary"
                variant="flat"
                @click.prevent="router.visit(lessonHref(nextLesson))"
            >
                Next lesson
            </v-btn>
            <v-spacer />
            <v-btn
                v-if="!progress?.completed_at"
                color="success"
                prepend-icon="mdi-check-circle"
                variant="flat"
                @click="markComplete"
            >
                Mark as completed
            </v-btn>
            <Link :href="`/modules/${lesson.module_id}/lessons/${lesson.id}/edit`">
                <v-btn v-if="canManage" prepend-icon="mdi-pencil" variant="outlined"> Full edit </v-btn>
            </Link>
            <Link :href="`/lessons/${lesson.id}/quizzes`">
                <v-btn prepend-icon="mdi-help-circle-outline" variant="outlined"> Quizzes </v-btn>
            </Link>
        </div>

        <v-row>
            <v-col cols="12" lg="8">
                <v-card rounded="lg" variant="outlined">
                    <v-card-title class="text-h6">Lesson content</v-card-title>
                    <v-card-text>
                        <div v-if="lesson.contents?.length" class="d-flex flex-column ga-6">
                            <div v-for="block in lesson.contents" :key="block.id" class="lesson-block">
                                <div class="d-flex justify-space-between align-center mb-2">
                                    <div class="text-subtitle-2 text-medium-emphasis">
                                        {{ block.title || block.content_type }}
                                    </div>
                                    <v-btn
                                        v-if="canManage"
                                        color="error"
                                        density="comfortable"
                                        icon="mdi-delete-outline"
                                        size="small"
                                        variant="text"
                                        @click="removeBlock(block)"
                                    />
                                </div>
                                <div v-if="block.content_type === 'text' || block.content_type === 'note'" class="text-body-1" style="white-space: pre-wrap">
                                    {{ block.content }}
                                </div>
                                <div v-else-if="block.content_type === 'video' && block.file_url" class="lesson-media">
                                    <video class="w-100 rounded" controls :src="block.file_url" />
                                </div>
                                <div v-else-if="block.content_type === 'audio' && block.file_url" class="lesson-media">
                                    <audio class="w-100" controls :src="block.file_url" />
                                </div>
                                <div v-else-if="block.file_url" class="lesson-media">
                                    <a class="text-primary" :href="block.file_url" rel="noopener" target="_blank">Open media</a>
                                </div>
                                <p v-else class="text-body-2 text-medium-emphasis">No preview for this block type.</p>
                            </div>
                        </div>

                        <div v-if="renderLegacyBody()" class="text-body-1 mb-4" style="white-space: pre-wrap">
                            {{ renderLegacyBody() }}
                        </div>

                        <p v-if="!lesson.contents?.length && !renderLegacyBody()" class="text-medium-emphasis">
                            No structured blocks yet. Staff can add blocks below.
                        </p>
                    </v-card-text>
                </v-card>

                <v-card v-if="canManage" class="mt-4" rounded="lg" variant="outlined">
                    <v-card-title class="text-h6">Add content block</v-card-title>
                    <v-card-text>
                        <v-form class="d-flex flex-column ga-3" @submit.prevent="submitContentBlock">
                            <v-select
                                v-model="contentForm.content_type"
                                :items="['text', 'video', 'audio', 'image', 'pdf', 'note', 'example', 'exercise']"
                                label="Type"
                                variant="outlined"
                            />
                            <v-text-field v-model="contentForm.title" label="Title (optional)" variant="outlined" />
                            <v-textarea v-model="contentForm.content" label="Text content" rows="4" variant="outlined" />
                            <v-text-field
                                v-model="contentForm.file_url"
                                hint="URL for media or file"
                                label="File URL"
                                persistent-hint
                                variant="outlined"
                            />
                            <v-btn color="primary" :loading="contentForm.processing" type="submit" variant="flat">
                                Add block
                            </v-btn>
                        </v-form>
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" lg="4">
                <v-card v-if="lesson.vocabulary && Object.keys(lesson.vocabulary).length" class="mb-4" rounded="lg" variant="outlined">
                    <v-card-title class="text-h6">Vocabulary</v-card-title>
                    <v-card-text>
                        <pre class="text-body-2">{{ JSON.stringify(lesson.vocabulary, null, 2) }}</pre>
                    </v-card-text>
                </v-card>

                <v-card v-if="lesson.quizzes?.length" rounded="lg" variant="outlined">
                    <v-card-title class="text-h6">Quizzes</v-card-title>
                    <v-card-text>
                        <ul class="pl-4">
                            <li v-for="q in lesson.quizzes" :key="q.id" class="mb-2">
                                <Link class="text-primary text-decoration-none" :href="`/quizzes/${q.id}/take`">
                                    {{ q.title }}
                                </Link>
                                <Link
                                    v-if="canManage"
                                    class="ml-2 text-caption text-medium-emphasis"
                                    :href="`/lessons/${lesson.id}/quizzes/${q.id}/edit`"
                                >
                                    Edit
                                </Link>
                            </li>
                        </ul>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </TenantLayout>
</template>

<style scoped>
.lesson-block {
    border-bottom: 1px solid rgb(var(--v-theme-outline-variant));
    padding-bottom: 1rem;
}

.lesson-block:last-child {
    border-bottom: none;
}

.lesson-media {
    max-width: 100%;
}
</style>
