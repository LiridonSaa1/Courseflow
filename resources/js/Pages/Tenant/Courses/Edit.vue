<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    course: { type: Object, required: true },
    teachers: { type: Array, default: () => [] },
});

const teacherItems = computed(() =>
    props.teachers.map((t) => ({
        title: t.user?.name ?? `Teacher #${t.id}`,
        value: t.id,
    })),
);

const statusItems = [
    { title: 'Draft', value: 'draft' },
    { title: 'Published', value: 'published' },
];

const form = useForm({
    title: props.course.title,
    language: props.course.language,
    level: props.course.level,
    description: props.course.description ?? '',
    teacher_id: props.course.teacher_id ?? null,
    thumbnail: props.course.thumbnail ?? '',
    status: props.course.status ?? 'draft',
});

function submit() {
    form.put(`/courses/${props.course.id}`);
}
</script>

<template>
    <TenantLayout>
        <Head title="Edit course" />

        <v-card class="mx-auto max-w-xl" rounded="lg">
            <v-card-title class="text-h5 font-weight-bold"> Edit course </v-card-title>
            <v-card-text>
                <v-form class="d-flex flex-column ga-4" @submit.prevent="submit">
                    <v-text-field
                        v-model="form.title"
                        :error-messages="form.errors.title"
                        hide-details="auto"
                        label="Title"
                        variant="outlined"
                    />

                    <v-text-field
                        v-model="form.language"
                        :error-messages="form.errors.language"
                        hide-details="auto"
                        label="Language"
                        variant="outlined"
                    />

                    <v-select
                        v-model="form.level"
                        :error-messages="form.errors.level"
                        hide-details="auto"
                        :items="['A1', 'A2', 'B1', 'B2', 'C1']"
                        label="Level"
                        variant="outlined"
                    />

                    <v-textarea
                        v-model="form.description"
                        :error-messages="form.errors.description"
                        hide-details="auto"
                        label="Description"
                        rows="3"
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

                    <v-text-field
                        v-model="form.thumbnail"
                        :error-messages="form.errors.thumbnail"
                        hide-details="auto"
                        hint="Image URL or path"
                        label="Thumbnail"
                        persistent-hint
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

                    <v-btn
                        block
                        color="primary"
                        :loading="form.processing"
                        size="large"
                        type="submit"
                        variant="flat"
                    >
                        Update
                    </v-btn>
                </v-form>
            </v-card-text>
        </v-card>
    </TenantLayout>
</template>
