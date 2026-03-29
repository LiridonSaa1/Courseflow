<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, onUnmounted, ref, watch } from 'vue';

const model = defineModel({ type: Boolean, default: false });

const props = defineProps({
    teacher: { type: Object, default: null },
});

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    title: '',
    profile_photo: null,
    status: 'active',
});

const photoFiles = ref([]);
const photoInputKey = ref(0);
const previewObjectUrl = ref(null);

const statusItems = [
    { title: 'Active', value: 'active' },
    { title: 'Inactive', value: 'inactive' },
    { title: 'Pending', value: 'pending' },
];

const canSubmit = computed(() => {
    const fn = String(form.first_name ?? '').trim();
    const ln = String(form.last_name ?? '').trim();
    const em = String(form.email ?? '').trim();
    const st = form.status;
    return (
        fn.length > 0 &&
        ln.length > 0 &&
        em.length > 0 &&
        (st === 'active' || st === 'inactive' || st === 'pending')
    );
});

const previewSrc = computed(() => {
    if (previewObjectUrl.value) {
        return previewObjectUrl.value;
    }
    return props.teacher?.profile_photo_url ?? null;
});

function applyTeacher(t) {
    if (!t?.id) {
        return;
    }
    form.first_name = t.first_name ?? '';
    form.last_name = t.last_name ?? '';
    form.email = t.user?.email ?? '';
    form.phone = t.phone ?? '';
    form.title = t.title ?? '';
    form.profile_photo = null;
    form.status = ['active', 'inactive', 'pending'].includes(t.status) ? t.status : 'active';
    photoFiles.value = [];
    photoInputKey.value += 1;
    revokePreviewUrl();
}

function revokePreviewUrl() {
    if (previewObjectUrl.value) {
        URL.revokeObjectURL(previewObjectUrl.value);
        previewObjectUrl.value = null;
    }
}

watch(
    () => photoFiles.value,
    (files) => {
        revokePreviewUrl();
        const f = files?.[0];
        if (f instanceof File) {
            previewObjectUrl.value = URL.createObjectURL(f);
        }
    },
    { deep: true },
);

function close() {
    model.value = false;
}

function submit() {
    if (!canSubmit.value || !props.teacher?.id) {
        return;
    }

    const file = photoFiles.value?.[0] ?? null;
    form.profile_photo = file instanceof File ? file : null;

    form
        .transform((data) => {
            const base = {
                first_name: data.first_name,
                last_name: data.last_name,
                email: data.email,
                phone: data.phone || null,
                title: data.title || null,
                status: data.status,
            };
            if (data.profile_photo instanceof File) {
                base.profile_photo = data.profile_photo;
            }
            return base;
        })
        .put(`/teachers/${props.teacher.id}`, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                form.clearErrors();
                revokePreviewUrl();
                close();
            },
        });
}

watch(
    () => [model.value, props.teacher],
    () => {
        if (model.value && props.teacher) {
            applyTeacher(props.teacher);
        }
        if (!model.value) {
            form.clearErrors();
            revokePreviewUrl();
        }
    },
    { deep: true, immediate: true },
);

onUnmounted(() => {
    revokePreviewUrl();
});
</script>

<template>
    <v-navigation-drawer
        v-model="model"
        class="course-create-drawer teacher-edit-drawer"
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
                    <v-toolbar-title class="text-h6 font-weight-bold text-white pa-0"> Edit teacher </v-toolbar-title>
                </div>
                <p class="mb-0 text-body-2 text-white opacity-90">Përditësoni profilin e mësimdhënësit.</p>
            </div>
        </v-toolbar>
        <v-divider />

        <v-container class="py-6" fluid>
            <v-form class="d-flex flex-column ga-4" @submit.prevent="submit">
                <div v-if="previewSrc" class="d-flex justify-center pb-2">
                    <v-avatar rounded="lg" size="120">
                        <v-img :src="previewSrc" cover />
                    </v-avatar>
                </div>

                <v-text-field
                    v-model="form.first_name"
                    autocomplete="given-name"
                    density="comfortable"
                    :error-messages="form.errors.first_name"
                    hide-details="auto"
                    label="First name"
                    variant="outlined"
                />
                <v-text-field
                    v-model="form.last_name"
                    autocomplete="family-name"
                    density="comfortable"
                    :error-messages="form.errors.last_name"
                    hide-details="auto"
                    label="Last name"
                    variant="outlined"
                />
                <v-text-field
                    v-model="form.email"
                    autocomplete="email"
                    density="comfortable"
                    :error-messages="form.errors.email"
                    hide-details="auto"
                    label="Email"
                    type="email"
                    variant="outlined"
                />
                <v-text-field
                    v-model="form.phone"
                    autocomplete="tel"
                    density="comfortable"
                    :error-messages="form.errors.phone"
                    hide-details="auto"
                    label="Phone"
                    variant="outlined"
                />
                <v-text-field
                    v-model="form.title"
                    autocomplete="organization-title"
                    density="comfortable"
                    :error-messages="form.errors.title"
                    hide-details="auto"
                    label="Title / role label"
                    variant="outlined"
                />
                <v-file-input
                    :key="photoInputKey"
                    v-model="photoFiles"
                    accept="image/png,image/jpeg,image/jpg,image/gif,image/webp"
                    density="comfortable"
                    :error-messages="form.errors.profile_photo"
                    hide-details="auto"
                    label="Upload new profile photo"
                    prepend-inner-icon="mdi-camera"
                    show-size
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
.teacher-edit-drawer.v-navigation-drawer--temporary {
    position: fixed !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    left: auto !important;
    height: 100vh !important;
    max-height: 100vh !important;
    z-index: 11000 !important;
}

.v-navigation-drawer.teacher-edit-drawer ~ .v-navigation-drawer__scrim,
.v-navigation-drawer.teacher-edit-drawer ~ * .v-navigation-drawer__scrim {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 10999 !important;
}
</style>
