<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const roles = computed(() => page.props.auth?.user?.roles ?? []);

const LayoutComponent = computed(() => {
    const r = roles.value;
    if (r.includes('owner') || r.includes('admin')) {
        return AdminLayout;
    }
    if (r.includes('teacher')) {
        return TeacherLayout;
    }

    return StudentLayout;
});
</script>

<template>
    <component :is="LayoutComponent">
        <slot />
        <template v-if="$slots.aside" #aside>
            <slot name="aside" />
        </template>
    </component>
</template>
