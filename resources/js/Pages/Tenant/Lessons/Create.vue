<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ module: Object });

const form = useForm({
    title: '',
    type: 'reading',
    content: { body: '' },
    vocabulary: [],
    grammar: [],
    examples: [],
});

function submit() {
    form.post(`/modules/${props.module.id}/lessons`);
}
</script>

<template>
    <TenantLayout>
        <Head title="New lesson" />
        <Link :href="`/courses/${module.course.id}`" class="text-sm text-indigo-400">← Back</Link>
        <h1 class="mt-4 text-2xl font-bold text-neutral-900">New lesson</h1>
        <form class="mt-6 max-w-lg space-y-4" @submit.prevent="submit">
            <input v-model="form.title" placeholder="Title" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900" />
            <select v-model="form.type" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900">
                <option value="reading">Reading</option>
                <option value="listening">Listening</option>
                <option value="grammar">Grammar</option>
                <option value="speaking">Speaking</option>
                <option value="writing">Writing</option>
            </select>
            <textarea v-model="form.content.body" rows="6" placeholder="Lesson body" class="w-full rounded-lg border border-neutral-200 bg-slate-900 px-3 py-2 text-neutral-900" />
            <button type="submit" class="rounded-lg bg-indigo-500 px-4 py-2 text-white">Save</button>
        </form>
    </TenantLayout>
</template>
