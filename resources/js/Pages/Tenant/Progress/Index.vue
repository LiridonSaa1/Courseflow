<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    progress: { type: Array, default: () => [] },
    recentAttempts: { type: Array, default: () => [] },
    chartActivity: { type: Array, default: () => [] },
});

const weekLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

const maxActivity = computed(() => Math.max(1, ...props.chartActivity.map((n) => Number(n) || 0)));
</script>

<template>
    <TenantLayout>
        <Head title="Progress" />
        <h1 class="text-2xl font-bold text-neutral-900">My progress</h1>
        <p class="mt-2 text-sm text-neutral-600">Lesson completion and recent quiz activity.</p>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <v-card rounded="lg">
                <v-card-title class="text-h6">Lesson progress</v-card-title>
                <v-card-text>
                    <ul v-if="progress.length" class="space-y-2 text-sm text-neutral-700">
                        <li v-for="p in progress" :key="p.id">
                            {{ p.lesson?.title ?? 'Lesson' }} — {{ p.percent ?? 0 }}%
                        </li>
                    </ul>
                    <p v-else class="text-sm text-neutral-500">No lesson progress recorded yet.</p>
                </v-card-text>
            </v-card>

            <v-card rounded="lg">
                <v-card-title class="text-h6">Recent quiz attempts</v-card-title>
                <v-card-text>
                    <ul v-if="recentAttempts.length" class="space-y-2 text-sm text-neutral-700">
                        <li v-for="a in recentAttempts" :key="a.id">
                            Quiz #{{ a.quiz_id }} — score {{ a.score }} / {{ a.max_score }}
                        </li>
                    </ul>
                    <p v-else class="text-sm text-neutral-500">No attempts yet.</p>
                </v-card-text>
            </v-card>
        </div>

        <v-card class="mt-6" rounded="lg">
            <v-card-title class="text-h6">Activity (last 7 days)</v-card-title>
            <v-card-text>
                <div class="flex h-32 items-end gap-1 sm:gap-2">
                    <div
                        v-for="(n, i) in chartActivity"
                        :key="i"
                        class="flex min-w-0 flex-1 flex-col items-center justify-end gap-1"
                    >
                        <div
                            class="w-full rounded-t bg-[#e66239]/80"
                            :style="{ height: `${(Number(n) / maxActivity) * 100}%`, minHeight: Number(n) > 0 ? '8px' : '0' }"
                        />
                        <span class="text-[10px] text-neutral-500 sm:text-xs">{{ i + 1 }}</span>
                    </div>
                </div>
            </v-card-text>
        </v-card>
    </TenantLayout>
</template>
