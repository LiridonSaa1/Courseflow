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

const typeLabel = computed(() => {
    const t = props.lesson.type;
    if (!t) {
        return '—';
    }
    return String(t).charAt(0).toUpperCase() + String(t).slice(1);
});

const contentForm = useForm({
    content_type: 'text',
    title: '',
    content: '',
    file_url: '',
});

const selectMenuProps = {
    zIndex: 12050,
    scrollStrategy: 'close',
};

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

        <v-breadcrumbs class="lesson-show-breadcrumbs px-0 mb-4" color="primary" density="comfortable">
            <v-breadcrumbs-item>
                <Link class="text-decoration-none text-primary" :href="`/courses/${lesson.module.course.id}`">
                    {{ lesson.module.course.title }}
                </Link>
            </v-breadcrumbs-item>
            <v-breadcrumbs-item>
                <Link class="text-decoration-none text-primary" :href="`/modules/${lesson.module_id}/lessons`">
                    {{ lesson.module.title }}
                </Link>
            </v-breadcrumbs-item>
            <v-breadcrumbs-item disabled>
                {{ lesson.title }}
            </v-breadcrumbs-item>
        </v-breadcrumbs>

        <v-card class="lesson-show-hero mb-4" border rounded="xl" elevation="1">
            <v-card-text class="pa-6 pa-sm-8">
                <v-row align="stretch">
                    <v-col cols="12" lg="8">
                        <div class="d-flex align-start ga-4 ga-sm-6">
                            <v-avatar
                                class="flex-shrink-0 mt-1"
                                color="primary"
                                rounded="lg"
                                size="56"
                                variant="tonal"
                            >
                                <v-icon icon="mdi-play-box-outline" size="28" />
                            </v-avatar>
                            <div class="min-w-0 flex-grow-1">
                                <v-card-title class="pa-0 text-h5 font-weight-bold text-high-emphasis text-wrap">
                                    {{ lesson.title }}
                                </v-card-title>
                                <p
                                    v-if="lesson.short_description"
                                    class="text-body-1 text-medium-emphasis mt-3 mb-0"
                                    style="max-width: 48rem; line-height: 1.55"
                                >
                                    {{ lesson.short_description }}
                                </p>
                                <div class="d-flex flex-wrap align-center ga-2 mt-4">
                                    <v-chip
                                        density="comfortable"
                                        label
                                        prepend-icon="mdi-shape-outline"
                                        size="small"
                                        variant="outlined"
                                    >
                                        {{ typeLabel }}
                                    </v-chip>
                                    <v-chip
                                        v-if="lesson.level"
                                        density="comfortable"
                                        label
                                        prepend-icon="mdi-stairs"
                                        size="small"
                                        variant="outlined"
                                    >
                                        {{ lesson.level }}
                                    </v-chip>
                                    <v-chip
                                        v-if="lesson.duration_minutes != null"
                                        density="comfortable"
                                        label
                                        prepend-icon="mdi-timer-outline"
                                        size="small"
                                        variant="tonal"
                                        color="primary"
                                    >
                                        {{ lesson.duration_minutes }} min
                                    </v-chip>
                                    <v-chip
                                        :color="lesson.status === 'published' ? 'success' : 'default'"
                                        density="comfortable"
                                        :prepend-icon="
                                            lesson.status === 'published' ? 'mdi-check-circle-outline' : 'mdi-file-edit-outline'
                                        "
                                        size="small"
                                        variant="tonal"
                                    >
                                        {{ lesson.status === 'published' ? 'Published' : 'Draft' }}
                                    </v-chip>
                                </div>
                            </div>
                        </div>
                    </v-col>
                    <v-col cols="12" lg="4">
                        <v-sheet border class="lesson-show-progress h-100 rounded-lg" color="surface-variant" elevation="0">
                            <div class="pa-4 pa-sm-5">
                                <div class="d-flex align-center justify-space-between mb-2">
                                    <span class="text-caption font-weight-medium text-medium-emphasis text-uppercase letter-spacing-1">
                                        Your progress
                                    </span>
                                    <span class="text-caption font-weight-bold text-primary">{{ Math.round(progressPercent) }}%</span>
                                </div>
                                <v-progress-linear
                                    bg-opacity="0.2"
                                    class="rounded-lg"
                                    color="primary"
                                    height="12"
                                    rounded
                                    :model-value="progressPercent"
                                />
                                <p v-if="progress?.completed_at" class="text-caption text-success mt-2 mb-0">
                                    <v-icon icon="mdi-check-decagram" size="small" start />
                                    Completed
                                </p>
                            </div>
                        </v-sheet>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <v-card class="mb-6" border rounded="xl" variant="tonal" color="surface">
            <v-card-text class="d-flex flex-wrap align-center ga-2 py-3 py-sm-4">
                <v-btn
                    v-if="prevLesson"
                    :href="lessonHref(prevLesson)"
                    prepend-icon="mdi-arrow-left"
                    rounded="lg"
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
                    rounded="lg"
                    variant="flat"
                    @click.prevent="router.visit(lessonHref(nextLesson))"
                >
                    Next lesson
                </v-btn>
                <v-spacer class="d-none d-sm-flex" />
                <v-btn
                    v-if="!progress?.completed_at"
                    color="success"
                    prepend-icon="mdi-check-circle"
                    rounded="lg"
                    variant="flat"
                    @click="markComplete"
                >
                    Mark as completed
                </v-btn>
                <Link v-if="canManage" class="text-decoration-none" :href="`/modules/${lesson.module_id}/lessons/${lesson.id}/edit`">
                    <v-btn prepend-icon="mdi-pencil-outline" rounded="lg" variant="outlined"> Full edit </v-btn>
                </Link>
                <Link class="text-decoration-none" :href="`/lessons/${lesson.id}/quizzes`">
                    <v-btn prepend-icon="mdi-help-circle-outline" rounded="lg" variant="outlined"> Quizzes </v-btn>
                </Link>
            </v-card-text>
        </v-card>

        <v-row>
            <v-col cols="12" lg="8">
                <v-card border class="lesson-show-content" rounded="xl" elevation="1">
                    <v-card-item class="pb-2">
                        <template #prepend>
                            <v-avatar color="primary" rounded="lg" size="40" variant="tonal">
                                <v-icon icon="mdi-book-open-variant" size="22" />
                            </v-avatar>
                        </template>
                        <v-card-title class="text-h6 font-weight-bold"> Lesson content </v-card-title>
                        <v-card-subtitle class="text-wrap"> Blocks, media, and legacy text </v-card-subtitle>
                    </v-card-item>
                    <v-divider class="mb-2" />
                    <v-card-text class="pt-4">
                        <div v-if="lesson.contents?.length" class="d-flex flex-column ga-4">
                            <v-sheet
                                v-for="block in lesson.contents"
                                :key="block.id"
                                border
                                class="lesson-block-sheet pa-4 pa-sm-5 rounded-xl"
                                color="surface"
                            >
                                <div class="d-flex justify-space-between align-center gap-2 mb-3">
                                    <div class="text-subtitle-1 font-weight-medium text-high-emphasis">
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
                                <v-chip
                                    class="mb-3"
                                    density="comfortable"
                                    size="small"
                                    variant="tonal"
                                    color="primary"
                                >
                                    {{ block.content_type }}
                                </v-chip>
                                <div
                                    v-if="block.content_type === 'text' || block.content_type === 'note'"
                                    class="text-body-1"
                                    style="white-space: pre-wrap"
                                >
                                    {{ block.content }}
                                </div>
                                <div v-else-if="block.content_type === 'video' && block.file_url" class="lesson-media">
                                    <video class="w-100 rounded-lg" controls :src="block.file_url" />
                                </div>
                                <div v-else-if="block.content_type === 'audio' && block.file_url" class="lesson-media">
                                    <audio class="w-100" controls :src="block.file_url" />
                                </div>
                                <div v-else-if="block.file_url" class="lesson-media">
                                    <v-btn
                                        :href="block.file_url"
                                        prepend-icon="mdi-open-in-new"
                                        rel="noopener"
                                        target="_blank"
                                        variant="tonal"
                                    >
                                        Open media
                                    </v-btn>
                                </div>
                                <p v-else class="text-body-2 text-medium-emphasis mb-0">No preview for this block type.</p>
                            </v-sheet>
                        </div>

                        <v-sheet
                            v-if="renderLegacyBody()"
                            border
                            class="pa-4 pa-sm-5 rounded-xl mb-4"
                            color="surface-variant"
                        >
                            <div class="text-caption font-weight-medium text-medium-emphasis mb-2">Legacy content</div>
                            <div class="text-body-1" style="white-space: pre-wrap">
                                {{ renderLegacyBody() }}
                            </div>
                        </v-sheet>

                        <v-alert
                            v-if="!lesson.contents?.length && !renderLegacyBody()"
                            border="start"
                            class="text-body-2"
                            color="primary"
                            density="comfortable"
                            icon="mdi-information-outline"
                            variant="tonal"
                        >
                            No structured blocks yet. Staff can add blocks below.
                        </v-alert>
                    </v-card-text>
                </v-card>

                <v-card v-if="canManage" border class="mt-4" rounded="xl" elevation="1">
                    <v-card-item>
                        <v-card-title class="text-h6 font-weight-bold">Add content block</v-card-title>
                        <v-card-subtitle>Optional title, text, or media URL</v-card-subtitle>
                    </v-card-item>
                    <v-divider />
                    <v-card-text class="pt-4">
                        <v-form class="d-flex flex-column ga-4" @submit.prevent="submitContentBlock">
                            <v-select
                                v-model="contentForm.content_type"
                                :items="['text', 'video', 'audio', 'image', 'pdf', 'note', 'example', 'exercise']"
                                label="Type"
                                :menu-props="selectMenuProps"
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
                            <v-btn color="primary" :loading="contentForm.processing" rounded="lg" size="large" type="submit" variant="flat">
                                Add block
                            </v-btn>
                        </v-form>
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" lg="4">
                <v-card
                    v-if="lesson.vocabulary && Object.keys(lesson.vocabulary).length"
                    border
                    class="mb-4"
                    rounded="xl"
                    elevation="1"
                >
                    <v-card-item>
                        <template #prepend>
                            <v-avatar color="secondary" rounded="lg" size="36" variant="tonal">
                                <v-icon icon="mdi-alphabetical" size="20" />
                            </v-avatar>
                        </template>
                        <v-card-title class="text-subtitle-1 font-weight-bold"> Vocabulary </v-card-title>
                    </v-card-item>
                    <v-divider />
                    <v-card-text>
                        <v-sheet class="pa-3 rounded-lg" color="surface-variant" max-height="320" style="overflow: auto">
                            <pre class="text-body-2 ma-0 font-mono">{{ JSON.stringify(lesson.vocabulary, null, 2) }}</pre>
                        </v-sheet>
                    </v-card-text>
                </v-card>

                <v-card v-if="lesson.quizzes?.length" border rounded="xl" elevation="1">
                    <v-card-item>
                        <template #prepend>
                            <v-avatar color="primary" rounded="lg" size="36" variant="tonal">
                                <v-icon icon="mdi-help-circle-outline" size="20" />
                            </v-avatar>
                        </template>
                        <v-card-title class="text-subtitle-1 font-weight-bold"> Quizzes </v-card-title>
                    </v-card-item>
                    <v-divider />
                    <v-list class="py-2" density="comfortable" lines="two">
                        <v-list-item
                            v-for="q in lesson.quizzes"
                            :key="q.id"
                            border
                            class="mx-3 mb-2 rounded-lg"
                            rounded="lg"
                        >
                            <template #prepend>
                                <v-avatar color="primary" size="36" variant="tonal">
                                    <v-icon icon="mdi-clipboard-text-outline" size="20" />
                                </v-avatar>
                            </template>
                            <v-list-item-title>
                                <Link class="text-primary text-decoration-none font-weight-medium" :href="`/quizzes/${q.id}/take`">
                                    {{ q.title }}
                                </Link>
                            </v-list-item-title>
                            <v-list-item-subtitle v-if="canManage">
                                <Link
                                    class="text-caption text-medium-emphasis text-decoration-none"
                                    :href="`/lessons/${lesson.id}/quizzes/${q.id}/edit`"
                                >
                                    Edit quiz
                                </Link>
                            </v-list-item-subtitle>
                        </v-list-item>
                    </v-list>
                </v-card>
            </v-col>
        </v-row>
    </TenantLayout>
</template>

<style scoped>
.lesson-show-breadcrumbs :deep(.v-breadcrumbs-item--disabled) {
    opacity: 1;
}

.lesson-show-breadcrumbs :deep(.v-breadcrumbs-item:last-child) {
    color: rgba(var(--v-theme-on-surface), 0.75);
    font-weight: 500;
}

.lesson-media {
    max-width: 100%;
}

.lesson-block-sheet {
    border-color: rgba(var(--v-theme-outline-variant), 0.9);
}
</style>
