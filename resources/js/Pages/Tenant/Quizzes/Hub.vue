<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    quizzes: { type: Array, default: () => [] },
});

function quizHref(quiz) {
    const lesson = quiz.lesson;
    if (!lesson?.id) {
        return '#';
    }

    return `/lessons/${lesson.id}/quizzes`;
}

function takeHref(quiz) {
    return `/quizzes/${quiz.id}/take`;
}
</script>

<template>
    <TenantLayout>
        <Head title="Quizzes" />
        <h1 class="text-2xl font-bold text-neutral-900">Quizzes</h1>
        <p class="mt-2 text-sm text-neutral-600">All quizzes in this workspace.</p>

        <v-card class="mt-6 overflow-hidden" rounded="lg">
            <v-data-table
                :headers="[
                    { title: 'Quiz', key: 'title', sortable: true },
                    { title: 'Lesson', key: 'lesson_title', sortable: true },
                    { title: 'Course', key: 'course_title', sortable: true },
                    { title: '', key: 'actions', sortable: false, align: 'end', width: '160px' },
                ]"
                :items="
                    quizzes.map((q) => ({
                        ...q,
                        lesson_title: q.lesson?.title ?? '—',
                        course_title: q.lesson?.module?.course?.title ?? '—',
                    }))
                "
                class="quizzes-hub-table"
                hover
                item-value="id"
                :items-per-page="20"
                :no-data-text="'No quizzes yet.'"
            >
                <template #item.title="{ item }">
                    <span class="font-medium text-neutral-900">{{ item.title }}</span>
                </template>
                <template #item.actions="{ item }">
                    <div class="d-flex flex-wrap justify-end ga-2">
                        <Link class="text-sm font-medium text-[#e66239]" :href="quizHref(item)"> Manage </Link>
                        <Link class="text-sm font-medium text-neutral-600" :href="takeHref(item)"> Take </Link>
                    </div>
                </template>
            </v-data-table>
        </v-card>
    </TenantLayout>
</template>
