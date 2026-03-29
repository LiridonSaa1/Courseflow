<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const model = defineModel({ type: Boolean, default: false });

const tab = ref('create');

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    phone: '',
    date_of_birth: '',
    gender: null,
    profile_photo: null,
    status: 'active',
});

const photoFiles = ref([]);
const photoInputKey = ref(0);

const inviteForm = useForm({
    name: '',
    email: '',
});

const genderItems = [
    { title: 'Male', value: 'male' },
    { title: 'Female', value: 'female' },
    { title: 'Other', value: 'other' },
    { title: 'Prefer not to say', value: 'prefer_not_to_say' },
];

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

const canInvite = computed(() => {
    return (
        String(inviteForm.name ?? '').trim().length > 0 &&
        String(inviteForm.email ?? '').trim().length > 0
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
            date_of_birth: data.date_of_birth || null,
            gender: data.gender || null,
            profile_photo: data.profile_photo instanceof File ? data.profile_photo : null,
        }))
        .post('/students', {
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

function submitInvite() {
    if (!canInvite.value) {
        return;
    }

    inviteForm.post('/invites', {
        preserveScroll: true,
        onSuccess: () => {
            inviteForm.reset();
            inviteForm.clearErrors();
            close();
        },
    });
}

watch(model, (open) => {
    if (!open) {
        form.clearErrors();
        inviteForm.clearErrors();
        tab.value = 'create';
    }
});
</script>

<template>
    <v-navigation-drawer
        v-model="model"
        class="course-create-drawer student-create-drawer"
        location="end"
        temporary
        width="480"
        scrim
    >
        <v-toolbar class="align-start py-4" color="primary" flat height="auto">
            <v-btn class="mt-1" color="white" icon="mdi-close" variant="text" @click="close" />
            <div class="ms-2 flex min-w-0 flex-1 flex-col gap-1 ps-0">
                <div class="d-flex align-center ga-2">
                    <v-icon color="white" icon="mdi-account-school-outline" size="28" />
                    <v-toolbar-title class="text-h6 font-weight-bold text-white pa-0">
                        New student
                    </v-toolbar-title>
                </div>
                <p class="mb-0 text-body-2 text-white opacity-90">
                    Krijoni një llogari ose dërgoni një ftesë me email.
                </p>
            </div>
        </v-toolbar>
        <v-divider />

        <v-tabs v-model="tab" bg-color="surface" color="primary" grow>
            <v-tab value="create">Create account</v-tab>
            <v-tab value="invite">Invite by email</v-tab>
        </v-tabs>

        <v-window v-model="tab" class="flex-grow-1">
            <v-window-item value="create">
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
                            v-model="form.date_of_birth"
                            density="comfortable"
                            :error-messages="form.errors.date_of_birth"
                            hide-details="auto"
                            label="Date of birth"
                            type="date"
                            variant="outlined"
                        />
                        <v-select
                            v-model="form.gender"
                            clearable
                            :error-messages="form.errors.gender"
                            hide-details="auto"
                            :items="genderItems"
                            item-title="title"
                            item-value="value"
                            label="Gender"
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
            </v-window-item>

            <v-window-item value="invite">
                <v-container class="py-6" fluid>
                    <v-form class="d-flex flex-column ga-4" @submit.prevent="submitInvite">
                        <v-text-field
                            v-model="inviteForm.name"
                            autocomplete="name"
                            density="comfortable"
                            :error-messages="inviteForm.errors.name"
                            hide-details="auto"
                            label="Name"
                            variant="outlined"
                        />
                        <v-text-field
                            v-model="inviteForm.email"
                            autocomplete="email"
                            density="comfortable"
                            :error-messages="inviteForm.errors.email"
                            hide-details="auto"
                            label="Email"
                            type="email"
                            variant="outlined"
                        />
                        <p class="text-body-2 text-medium-emphasis mb-0">
                            Një fjalëkalim i përkohshëm do të dërgohet me email.
                        </p>
                        <div class="course-create-drawer__actions d-flex ga-3 pt-2">
                            <v-btn
                                class="flex-grow-1"
                                color="primary"
                                :disabled="!canInvite || inviteForm.processing"
                                height="48"
                                :loading="inviteForm.processing"
                                size="large"
                                type="submit"
                                variant="flat"
                            >
                                Send invite
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
            </v-window-item>
        </v-window>
    </v-navigation-drawer>
</template>

<style>
.student-create-drawer.v-navigation-drawer--temporary {
    position: fixed !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    left: auto !important;
    height: 100vh !important;
    max-height: 100vh !important;
    z-index: 11000 !important;
}

.v-navigation-drawer.student-create-drawer ~ .v-navigation-drawer__scrim,
.v-navigation-drawer.student-create-drawer ~ * .v-navigation-drawer__scrim {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 10999 !important;
}
</style>
