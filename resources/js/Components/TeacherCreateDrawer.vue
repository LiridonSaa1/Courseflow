<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const model = defineModel({ type: Boolean, default: false });

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    phone: '',
    title: '',
    profile_photo: null,
    status: 'active',
});

const photoFiles = ref([]);
const photoInputKey = ref(0);

const statusItems = [
    { title: 'Active', value: 'active' },
    { title: 'Inactive', value: 'inactive' },
    { title: 'Pending', value: 'pending' },
];

const canSubmit = computed(() => {
    const fn = String(form.first_name ?? '').trim();
    const ln = String(form.last_name ?? '').trim();
    const em = String(form.email ?? '').trim();
    const pw = String(form.password ?? '');
    const st = form.status;
    return (
        fn.length > 0 &&
        ln.length > 0 &&
        em.length > 0 &&
        pw.length >= 8 &&
        (st === 'active' || st === 'inactive' || st === 'pending')
    );
});

function close() {
    model.value = false;
}

function submit() {
    if (!canSubmit.value) {
        return;
    }

    const file = photoFiles.value?.[0] ?? null;
    form.profile_photo = file instanceof File ? file : null;

    form
        .transform((data) => ({
            ...data,
            phone: data.phone || null,
            title: data.title || null,
            profile_photo: data.profile_photo instanceof File ? data.profile_photo : null,
        }))
        .post('/teachers', {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                form.reset();
                form.status = 'active';
                form.profile_photo = null;
                photoFiles.value = [];
                photoInputKey.value += 1;
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
        class="course-create-drawer teacher-create-drawer"
        location="end"
        temporary
        width="480"
        scrim
    >
        <v-toolbar class="align-start py-4" color="primary" flat height="auto">
            <v-btn class="mt-1" color="white" icon="mdi-close" variant="text" @click="close" />
            <div class="ms-2 flex min-w-0 flex-1 flex-col gap-1 ps-0">
                <div class="d-flex align-center ga-2">
                    <v-icon color="white" icon="mdi-school-outline" size="28" />
                    <v-toolbar-title class="text-h6 font-weight-bold text-white pa-0"> New teacher </v-toolbar-title>
                </div>
                <p class="mb-0 text-body-2 text-white opacity-90">
                    Krijoni llogarinë e mësimdhënësit dhe rolin e tij.
                </p>
            </div>
        </v-toolbar>
        <v-divider />

        <v-container class="py-6" fluid>
            <v-form class="d-flex flex-column ga-4" @submit.prevent="submit">
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
                    v-model="form.password"
                    autocomplete="new-password"
                    density="comfortable"
                    :error-messages="form.errors.password"
                    hide-details="auto"
                    label="Password"
                    type="password"
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
                    label="Profile photo"
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
.teacher-create-drawer.v-navigation-drawer--temporary {
    position: fixed !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    left: auto !important;
    height: 100vh !important;
    max-height: 100vh !important;
    z-index: 11000 !important;
}

.v-navigation-drawer.teacher-create-drawer ~ .v-navigation-drawer__scrim,
.v-navigation-drawer.teacher-create-drawer ~ * .v-navigation-drawer__scrim {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 10999 !important;
}
</style>
