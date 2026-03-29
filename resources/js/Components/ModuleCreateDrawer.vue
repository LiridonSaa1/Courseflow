<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const model = defineModel({ type: Boolean, default: false });

const props = defineProps({
    courseId: { type: Number, required: true },
});

const form = useForm({
    title: '',
    description: '',
    sort_order: null,
    status: 'draft',
});

const statusItems = [
    { title: 'Draft', value: 'draft' },
    { title: 'Published', value: 'published' },
];

const canSubmit = computed(() => String(form.title ?? '').trim().length > 0);

function close() {
    model.value = false;
}

function submit() {
    if (!canSubmit.value) {
        return;
    }

    form.post(`/courses/${props.courseId}/modules`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.sort_order = null;
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
        class="module-create-drawer"
        location="end"
        temporary
        width="480"
        scrim
    >
        <v-toolbar class="align-start py-4" color="primary" flat height="auto">
            <v-btn class="mt-1" color="white" icon="mdi-close" variant="text" @click="close" />
            <div class="ms-2 flex min-w-0 flex-1 flex-col gap-1 ps-0">
                <div class="d-flex align-center ga-2">
                    <v-icon color="white" icon="mdi-view-grid-plus-outline" size="28" />
                    <v-toolbar-title class="text-h6 font-weight-bold text-white pa-0"> New module </v-toolbar-title>
                </div>
                <p class="mb-0 text-body-2 text-white opacity-90">
                    Add a module to organize lessons inside this course.
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

                <v-text-field
                    v-model.number="form.sort_order"
                    density="comfortable"
                    :error-messages="form.errors.sort_order"
                    hide-details="auto"
                    hint="Leave empty to append at the end"
                    label="Order"
                    min="0"
                    persistent-hint
                    type="number"
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
.module-create-drawer.v-navigation-drawer--temporary {
    position: fixed !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    left: auto !important;
    height: 100vh !important;
    max-height: 100vh !important;
    z-index: 11000 !important;
}

.v-navigation-drawer.module-create-drawer ~ .v-navigation-drawer__scrim,
.v-navigation-drawer.module-create-drawer ~ * .v-navigation-drawer__scrim {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 10999 !important;
}
</style>
