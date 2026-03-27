<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ module: Object, lesson: Object });

const form = useForm({
    title: props.lesson.title,
    type: props.lesson.type,
    content: props.lesson.content ?? { body: '' },
    vocabulary: props.lesson.vocabulary ?? [],
    grammar: props.lesson.grammar ?? [],
    examples: props.lesson.examples ?? [],
});

function submit() {
    form.put(`/modules/${props.module.id}/lessons/${props.lesson.id}`);
}
</script>

<template>
    <TenantLayout>
        <Head title="Edit lesson" />
        <Link :href="`/modules/${module.id}/lessons/${lesson.id}`" class="text-sm text-indigo-400">← Lesson</Link>
        <h1 class="mt-4 text-2xl font-bold text-neutral-900">Edit lesson</h1>
        <form class="mt-6 max-w-lg space-y-4" @submit.prevent="submit">
            <input v-model="form.title" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900" />
            <textarea v-model="form.content.body" rows="6" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900" />
            <button type="submit" class="rounded-lg bg-indigo-500 px-4 py-2 text-white">Update</button>
        </form>
    </TenantLayout>
</template>
