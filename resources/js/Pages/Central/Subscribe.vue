<script setup>
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    plans: { type: Array, default: () => [] },
    stripePublishableKey: { type: String, default: null },
    baseDomain: { type: String, default: 'courseflow.test' },
});

const plansList = computed(() => (Array.isArray(props.plans) ? props.plans : []));

function formatPlanLabel(p) {
    if (!p?.name) {
        return 'Plan';
    }
    const mo = p.monthly_price_cents != null ? (Number(p.monthly_price_cents) / 100).toFixed(0) : null;

    return mo != null ? `${p.name} — $${mo}/mo` : p.name;
}

const form = useForm({
    course_name: '',
    teacher_name: '',
    email: '',
    phone: '',
    language: 'English',
    tenant_type: 'teacher',
    subdomain: '',
    plan_id: plansList.value[0]?.id ?? null,
    password: '',
    password_confirmation: '',
    billing_interval: 'monthly',
});

watch(
    plansList,
    (list) => {
        if (!list.length) {
            form.plan_id = null;

            return;
        }
        const ids = new Set(list.map((p) => p.id));
        if (form.plan_id == null || !ids.has(form.plan_id)) {
            form.plan_id = list[0].id;
        }
    },
    { immediate: true },
);

/** Match SubdomainAllocator heuristics loosely for preview only; server is authoritative. */
function normalizeCourseNameForPreview(name) {
    let s = String(name || '').trim();
    if (!s) return '';

    if (/\:\/\//.test(s)) {
        try {
            const host = new URL(s).hostname;
            s = host.split('.')[0] || host;
        } catch {
            /* ignore */
        }
    } else if (!/\s/.test(s) && s.includes('.')) {
        s = s.split('.')[0] || s;
    }

    s = s
        .replace(/([a-z\d])([A-Z])/g, '$1 $2')
        .replace(/([A-Z]+)([A-Z][a-z])/g, '$1 $2')
        .replace(/\bcourseflow\b/gi, '')
        .replace(/\.(?:com|org|net|io|co|dev|app)\b/gi, '')
        .replace(/\s+/g, ' ')
        .trim();

    return s;
}

function finalizePreviewSlug(slug) {
    let out = String(slug || '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    out = out.replace(/-courseflow(?=-|$)/g, '').replace(/-com$/g, '');
    out = out.replace(/^-+|-+$/g, '');

    return out || 'school';
}

function clientSlugFromCourseName(name) {
    return finalizePreviewSlug(normalizeCourseNameForPreview(name));
}

function clientSlug(s) {
    return finalizePreviewSlug(s);
}

const previewSubdomain = computed(() => {
    const raw = form.subdomain?.trim()
        ? clientSlug(form.subdomain)
        : clientSlugFromCourseName(form.course_name);

    return `${raw}.${props.baseDomain}`;
});

function submit() {
    form.post('/subscribe');
}
</script>

<template>
    <MarketingLayout>
        <Head title="Subscribe" />
        <div class="mx-auto max-w-xl rounded-2xl border border-white/10 bg-slate-900/70 p-8">
            <h1 class="text-2xl font-bold text-white">Create your workspace</h1>
            <p class="mt-2 text-sm text-slate-400">
                Your site URL is generated from your course name (e.g. <span class="text-indigo-300">my-english-course.{{ baseDomain }}</span>
                ). A dedicated tenant database is created automatically. Stripe checkout runs when keys are configured.
            </p>
            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="text-xs text-slate-400">Course / school name</label>
                    <input v-model="form.course_name" class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 text-white" />
                    <p class="mt-1 text-xs text-slate-500">Preview: <span class="font-mono text-indigo-300">{{ previewSubdomain }}</span></p>
                    <p v-if="form.errors.course_name" class="text-sm text-rose-400">{{ form.errors.course_name }}</p>
                </div>
                <div>
                    <label class="text-xs text-slate-400">Teacher / owner name</label>
                    <input v-model="form.teacher_name" class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 text-white" />
                </div>
                <div>
                    <label class="text-xs text-slate-400">Email</label>
                    <input v-model="form.email" type="email" class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 text-white" />
                    <p v-if="form.errors.email" class="text-sm text-rose-400">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label class="text-xs text-slate-400">Phone</label>
                    <input v-model="form.phone" class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 text-white" />
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-xs text-slate-400">Language focus</label>
                        <input v-model="form.language" class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 text-white" />
                    </div>
                    <div>
                        <label class="text-xs text-slate-400">Tenant type</label>
                        <select v-model="form.tenant_type" class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 text-white">
                            <option value="teacher">Teacher</option>
                            <option value="school">School</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-slate-400">Custom subdomain (optional)</label>
                    <input
                        v-model="form.subdomain"
                        class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 text-white"
                        placeholder="Leave blank to use course name"
                    />
                    <p v-if="form.errors.subdomain" class="text-sm text-rose-400">{{ form.errors.subdomain }}</p>
                    <p class="mt-1 text-xs text-slate-500">If set, this overrides the course-name slug. Uniqueness and reserved names are handled on the server.</p>
                </div>
                <div>
                    <label class="text-xs text-slate-400">Plan</label>
                    <select
                        v-model="form.plan_id"
                        class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 text-white [color-scheme:dark]"
                    >
                        <option v-if="!plansList.length" disabled value="">No plans — seed the database</option>
                        <option
                            v-for="p in plansList"
                            :key="p.id"
                            class="bg-slate-900 text-slate-100"
                            :value="p.id"
                        >
                            {{ formatPlanLabel(p) }}
                        </option>
                    </select>
                    <p v-if="!plansList.length" class="mt-1 text-xs text-amber-400/90">
                        Run <span class="font-mono">php artisan db:seed --class=PlanSeeder</span> (or full <span class="font-mono">db:seed</span>) so plans exist in your central database.
                    </p>
                    <p v-if="form.errors.plan_id" class="text-sm text-rose-400">{{ form.errors.plan_id }}</p>
                </div>
                <div>
                    <label class="text-xs text-slate-400">Billing (Stripe)</label>
                    <select v-model="form.billing_interval" class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 text-white">
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-400">Password</label>
                    <input v-model="form.password" type="password" class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 text-white" />
                </div>
                <div>
                    <label class="text-xs text-slate-400">Confirm password</label>
                    <input v-model="form.password_confirmation" type="password" class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 text-white" />
                </div>
                <p v-if="stripePublishableKey" class="text-xs text-emerald-400/90">Stripe publishable key loaded — checkout will open when secret + price IDs are set.</p>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg bg-indigo-500 py-2 font-semibold text-white hover:bg-indigo-400 disabled:opacity-50"
                >
                    Create workspace
                </button>
            </form>
        </div>
    </MarketingLayout>
</template>
