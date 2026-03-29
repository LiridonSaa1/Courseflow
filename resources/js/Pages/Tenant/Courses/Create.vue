<script setup>
import CourseCreateDrawer from '@/Components/CourseCreateDrawer.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const open = ref(true);

watch(open, (v) => {
    if (!v) {
        router.visit('/courses');
    }
});
</script>

<template>
    <TenantLayout>
        <Head title="New course" />
        <CourseCreateDrawer v-model="open" />
    </TenantLayout>
</template>
