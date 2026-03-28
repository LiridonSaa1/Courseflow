<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const isSticky = ref(false);
const currentYear = new Date().getFullYear();
const aboutSection = ref(null);
const aboutVisible = ref(false);
let aboutObserver = null;

const navLinks = [
    { label: 'Features', href: '#features' },
    { label: 'Si funksionon', href: '#how-it-works' },
    { label: 'Pricing', href: '#pricing' },
    { label: 'Reviews', href: '#reviews' },
    { label: 'FAQ', href: '#faq' },
];

const heroPills = ['Kurse online', 'Progress tracking', 'Dashboard i thjeshte'];

const features = [
    {
        title: 'Kurse online',
        description: 'Organizo module, leksione dhe materiale ne nje strukture te qarte.',
    },
    {
        title: 'Quiz interaktiv',
        description: 'Teste me pyetje te ndryshme per te matur dijen ne kohe reale.',
    },
    {
        title: 'Speaking practice',
        description: 'Praktike e te folurit me ushtrime qe e bejne mesimin me aktiv.',
    },
    {
        title: 'Teacher dashboard',
        description: 'Panel i unifikuar per menaxhimin e kurseve, studenteve dhe klasave.',
    },
    {
        title: 'Analytics',
        description: 'Shiko performancen, completion rate dhe progresin e cdo kursi.',
    },
    {
        title: 'Certificates',
        description: 'Lesho certifikata automatike pas perfundimit te kursit nga studentet.',
    },
];

const howItWorks = [
    {
        step: 'Regjistrohu',
        description: 'Hape llogarine dhe vendos informacionin baze te akademise tende.',
    },
    {
        step: 'Krijo tenant',
        description: 'Krijo hapesiren tende te dedikuar me subdomain te vecante.',
    },
    {
        step: 'Shto kurse',
        description: 'Ngarko leksione text, video dhe audio ne module te organizuara.',
    },
    {
        step: 'Regjistro studente',
        description: 'Fto studentet ne platforme dhe caktoji ne klasa ose kurse.',
    },
    {
        step: 'Fillo mesimin',
        description: 'Aktivizo quiz, speaking dhe monitoro progresin nga dashboard-i.',
        // 3D spatial scroll zoom
    },
];

const plans = [
    {
        name: 'Basic',
        badge: 'Per profesor individual',
        audience: ['1 profesor', 'Kurse te vogla', 'Fillestare'],
        includes: [
            '1 teacher account',
            'Deri ne 20 studente',
            'Deri ne 3 kurse',
            'Leksione (text, video, audio)',
            'Quiz bazik',
            'Progress tracking',
            'Dashboard',
        ],
        excludes: ['Pa speaking AI', 'Pa live classes', 'Pa advanced analytics'],
        limits: ['Storage i kufizuar', 'Pa custom domain'],
        bonus: [],
        range: 'EUR 9 - EUR 19 / muaj',
        suggested: 'EUR 12.99 / muaj',
        cta: 'Fillo me Basic',
        featured: false,
    },
    {
        name: 'Pro',
        badge: 'Per profesor serioz',
        audience: ['Profesor qe ka studente reale', 'Akademi te vogla'],
        includes: [
            'Deri ne 5 teachers',
            'Deri ne 100 studente',
            'Kurse pa limit',
            'Leksione multimedia',
            'Quiz advanced',
            'Speaking module (basic AI)',
            'Analytics',
            'Certificates',
            'Student management',
            'Community (discussion)',
        ],
        excludes: ['Live classes (optional ose limited)'],
        limits: [],
        bonus: ['Custom domain (optional)', 'Branding (logo + ngjyra)'],
        range: 'EUR 29 - EUR 49 / muaj',
        suggested: 'EUR 34.99 / muaj',
        cta: 'Zgjidh Pro',
        featured: true,
    },
    {
        name: 'School',
        badge: 'Per shkolla / akademi',
        audience: ['Shkolla gjuhe', 'Akademi serioze', 'Organizata'],
        includes: [
            'Teachers pa limit (ose 20+)',
            'Studente pa limit (ose 1000+)',
            'Kurse pa limit',
            'Gjitha llojet e leksioneve',
            'Quiz advanced + exams',
            'Speaking AI advanced',
            'Live classes (Zoom style)',
            'Analytics te avancuara',
            'Certificate system',
            'Full student management',
            'Class management',
            'Role system (admin, teacher, student)',
            'API access (optional)',
            'Custom domain',
            'Full branding',
        ],
        excludes: [],
        limits: [],
        bonus: ['Priority support', 'Onboarding', 'White-label opsion'],
        range: 'EUR 79 - EUR 149 / muaj',
        suggested: 'EUR 89 / muaj',
        cta: 'Kontakto per School',
        featured: false,
    },
];

const reviews = [
    {
        name: 'Arta H.',
        role: 'Profesore e gjuhes angleze',
        text: 'Procesi nga krijimi i tenant-it deri te publikimi i kursit ishte shume i thjeshte. Studentet jane me aktiv ne quiz dhe speaking.',
    },
    {
        name: 'Blend K.',
        role: 'Drejtor akademie',
        text: 'Dashboard-i i mesuesit na ka kursyer kohe ne menaxhimin e kurseve dhe performances se klasave. Pro plani ishte zgjidhja ideale.',
    },
    {
        name: 'Drita M.',
        role: 'Menaxhere shkolle',
        text: 'Me School plan kemi role system, analytics te avancuara dhe branding te plote. Platforma duket profesionale per cdo dege.',
    },
];

const faqs = [
    {
        question: 'Si krijohet kursi?',
        answer: 'Nga teacher dashboard krijon kurs te ri, shton module dhe leksione (text, video, audio), pastaj aktivizon quiz-et dhe e publikon kursin.',
    },
    {
        question: 'Si funksionon abonimi?',
        answer: 'Abonimi eshte mujor. Zgjedh planin Basic, Pro ose School dhe mund te kalosh ne plan me te larte kur rritet akademia.',
    },
    {
        question: 'Si krijohet subdomain?',
        answer: 'Gjithcka behet ne krijimin e tenant-it: vendos emrin e workspace dhe sistemi gjeneron subdomain automatikisht. Ne plane te caktuara mund te lidhesh custom domain.',
    },
];

const onScroll = () => {
    isSticky.value = window.scrollY >= 80;
};

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll);

    aboutObserver = new IntersectionObserver(
        (entries) => {
            const [entry] = entries;
            if (entry?.isIntersecting) {
                aboutVisible.value = true;
                aboutObserver?.disconnect();
                aboutObserver = null;
            }
        },
        { threshold: 0.3 },
    );

    if (aboutSection.value) {
        aboutObserver.observe(aboutSection.value);
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
    aboutObserver?.disconnect();
    aboutObserver = null;
});
</script>

<template>
    <Head title="Courseflow" />

    <div class="elearning-page min-h-screen bg-white text-slate-900">
        <header
            class="fixed top-0 z-40 w-full border-b border-slate-100 bg-white transition-all duration-300"
            :class="isSticky ? 'py-4 shadow-lg' : 'py-6 shadow-none'"
        >
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <a href="#home-section" class="shrink-0">
                    <img :src="'/images/logo/logo.svg'" alt="Courseflow logo" class="h-9 w-auto" />
                </a>

                <nav class="hidden items-center gap-8 text-base font-medium text-[#222c44] lg:flex">
                    <a
                        v-for="item in navLinks"
                        :key="item.label"
                        :href="item.href"
                        class="transition hover:text-[#6556ff]"
                    >
                        {{ item.label }}
                    </a>
                </nav>

                <div class="flex items-center gap-3">
                    <Link
                        href="/admin/login"
                        class="hidden rounded-full bg-[#6556ff]/15 px-6 py-2.5 text-sm font-medium text-[#6556ff] transition hover:bg-[#6556ff] hover:text-white sm:inline-flex"
                    >
                        Hyr
                    </Link>
                    <Link
                        href="/subscribe"
                        class="rounded-full bg-[#6556ff] px-6 py-2.5 text-sm font-medium text-white transition hover:bg-[#5849f2]"
                    >
                        Fillo tani
                    </Link>
                </div>
            </div>
        </header>

        <main class="pt-28">
            <section id="home-section" class="relative overflow-hidden bg-[#f6faff] py-20">
                <div class="hero-orb hero-orb-left" />
                <div class="hero-orb hero-orb-right" />

                <div class="relative mx-auto grid w-full max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-12 lg:items-center lg:px-8">
                    <div class="section-fade lg:col-span-6">
                        <div class="inline-flex items-center gap-2 rounded-full bg-[#6556ff]/10 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-[#6556ff]">
                            Learning platform per shkolla dhe akademi
                        </div>

                        <h1 class="pt-6 text-4xl font-semibold leading-tight text-[#222c44] sm:text-5xl">
                            Platforma e kurseve qe e kthen mesimin online ne eksperience profesionale.
                        </h1>

                        <p class="pt-5 text-lg text-slate-600">
                            Krijo kurse, menaxho studente, organizo quiz dhe speaking practice ne nje panel te vetem.
                        </p>

                        <div class="pt-8 flex flex-wrap gap-3">
                            <Link
                                href="/subscribe"
                                class="rounded-full bg-[#6556ff] px-7 py-3 text-sm font-semibold text-white transition hover:bg-[#5849f2]"
                            >
                                Fillo tani
                            </Link>
                            <a
                                href="#contact"
                                class="rounded-full border border-[#1a21bc]/20 bg-white px-7 py-3 text-sm font-semibold text-[#1a21bc] transition hover:border-[#6556ff]/40 hover:text-[#6556ff]"
                            >
                                Kerko demo
                            </a>
                            <a
                                href="#pricing"
                                class="rounded-full border border-[#6556ff]/35 bg-[#6556ff]/10 px-7 py-3 text-sm font-semibold text-[#6556ff] transition hover:bg-[#6556ff]/20"
                            >
                                Shiko planet
                            </a>
                        </div>

                        <div class="flex flex-wrap items-center gap-5 pt-10">
                            <div v-for="pill in heroPills" :key="pill" class="flex items-center gap-2">
                                <span class="inline-block h-2.5 w-2.5 rounded-full bg-[#43c639]" />
                                <p class="text-sm text-[#222c44] sm:text-base">{{ pill }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="section-fade lg:col-span-6" style="animation-delay: 0.15s">
                        <div class="hero-scene">
                            <span class="scene-bar scene-bar-1" />
                            <span class="scene-bar scene-bar-2" />
                            <span class="scene-bar scene-bar-3" />
                            <span class="scene-bar scene-bar-4" />

                            <div class="scene-stack scene-float-up">
                                <span class="scene-book scene-book-1" />
                                <span class="scene-book scene-book-2" />
                                <span class="scene-book scene-book-3" />
                                <span class="scene-book scene-book-4" />
                            </div>

                            <div class="scene-laptop scene-float-down">
                                <div class="scene-laptop-screen">
                                    <span class="scene-line scene-line-1" />
                                    <span class="scene-line scene-line-2" />
                                    <span class="scene-line scene-line-3" />
                                </div>
                                <div class="scene-laptop-keyboard" />
                            </div>

                            <img :src="'/images/banner/mahila.png'" alt="Hero education visual" class="scene-figure" />

                            <div class="scene-badge scene-badge-left scene-drift-x">
                                <img :src="'/images/courses/book-open.svg'" alt="Book icon" class="h-5 w-5" />
                                <span>Kurse aktive</span>
                            </div>

                            <div class="scene-badge scene-badge-right scene-drift-x-reverse">
                                <img :src="'/images/courses/users.svg'" alt="Users icon" class="h-5 w-5" />
                                <span>+1200 studente</span>
                            </div>

                            <img :src="'/images/mentor/user2.png'" alt="Student avatar" class="scene-avatar scene-avatar-1" />
                            <img :src="'/images/mentor/user3.png'" alt="Teacher avatar" class="scene-avatar scene-avatar-2" />
                        </div>
                    </div>
                </div>
            </section>

            <section id="about-us" ref="aboutSection" class="py-20">
                <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-10 lg:grid-cols-12 lg:items-center">
                        <div :class="['about-media lg:col-span-6', { 'is-visible': aboutVisible }]">
                            <div class="about-frame">
                                <span class="about-glow about-glow-1" />
                                <span class="about-glow about-glow-2" />
                                <img :src="'/images/about/about-digital-learning.jpg'" alt="Students learning on laptops" class="about-image" />

                                <div class="about-quiz-card">
                                    <p class="about-quiz-title">Quiz live</p>
                                    <div class="about-quiz-row">
                                        <span>Grammar test</span>
                                        <span>87%</span>
                                    </div>
                                    <div class="about-quiz-track">
                                        <span class="about-quiz-fill about-quiz-fill-1" />
                                    </div>
                                    <div class="about-quiz-row">
                                        <span>Listening test</span>
                                        <span>73%</span>
                                    </div>
                                    <div class="about-quiz-track">
                                        <span class="about-quiz-fill about-quiz-fill-2" />
                                    </div>
                                </div>

                                <div class="about-chip about-chip-1">
                                    <img :src="'/images/courses/book-open.svg'" alt="Book icon" class="h-4 w-4" />
                                    <span>+250 kurse aktive</span>
                                </div>
                                <div class="about-chip about-chip-2">
                                    <img :src="'/images/courses/users.svg'" alt="Users icon" class="h-4 w-4" />
                                    <span>98% completion rate</span>
                                </div>
                            </div>
                        </div>

                        <div :class="['about-content lg:col-span-6', { 'is-visible': aboutVisible }]">
                            <p class="text-sm font-semibold uppercase tracking-wider text-[#6556ff]">About us</p>
                            <h2 class="pt-3 text-4xl font-semibold leading-tight text-[#222c44] sm:text-5xl">
                                Nje platforme moderne per shkolla, akademi dhe profesore.
                            </h2>
                            <p class="pt-5 text-base leading-relaxed text-slate-600">
                                Courseflow u krijua per ta bere menaxhimin e mesimit online me te thjeshte, me
                                organizim me te mire dhe me rezultate me te matshme per cdo student.
                            </p>

                            <ul class="about-list pt-6 space-y-3 text-sm text-slate-700 sm:text-base">
                                <li class="flex items-start gap-2">
                                    <span class="mt-1.5 h-2 w-2 rounded-full bg-[#43c639]" />
                                    Tenant i dedikuar per cdo shkolle ose akademi
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="mt-1.5 h-2 w-2 rounded-full bg-[#43c639]" />
                                    Krijim i shpejte i kurseve, quiz-eve dhe speaking practice
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="mt-1.5 h-2 w-2 rounded-full bg-[#43c639]" />
                                    Analytics dhe certifikata per nje experience profesionale
                                </li>
                            </ul>

                            <div class="pt-8">
                                <a
                                    href="#contact"
                                    class="inline-flex rounded-full bg-[#6556ff] px-7 py-3 text-sm font-semibold text-white transition hover:bg-[#5849f2]"
                                >
                                    Meso me shume
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="features-section py-20">
                <span class="feature-bg-orb feature-bg-orb-1" />
                <span class="feature-bg-orb feature-bg-orb-2" />
                <span class="feature-bg-orb feature-bg-orb-3" />
                <span class="feature-grid-motion" />

                <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="section-fade max-w-2xl">
                        <p class="text-sm font-semibold uppercase tracking-wider text-[#6556ff]">Features</p>
                        <h2 class="pt-3 text-4xl font-semibold text-[#222c44] sm:text-5xl">Cfare perfshin platforma jone</h2>
                    </div>

                    <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <article
                            v-for="(feature, index) in features"
                            :key="feature.title"
                            class="feature-card-enter feature-card rounded-2xl border border-[#1a21bc]/10 bg-white p-6 shadow-[0_15px_30px_rgba(26,33,188,0.08)]"
                            :style="{ animationDelay: `${0.05 * (index + 1)}s` }"
                        >
                            <span class="feature-card-glow" />
                            <div class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#6556ff]/12 text-sm font-semibold text-[#6556ff]">
                                {{ index + 1 }}
                            </div>
                            <h3 class="pt-4 text-2xl font-semibold text-[#222c44]">{{ feature.title }}</h3>
                            <p class="pt-3 text-base text-slate-600">{{ feature.description }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="how-it-works" class="bg-[#d5effa] py-20">
                <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="section-fade max-w-2xl">
                        <p class="text-sm font-semibold uppercase tracking-wider text-[#1a21bc]">Si funksionon</p>
                        <h2 class="pt-3 text-4xl font-semibold text-[#222c44] sm:text-5xl">Nga regjistrimi deri te mesimi aktiv</h2>
                    </div>

                    <div class="mt-10 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                        <article
                            v-for="(item, index) in howItWorks"
                            :key="item.step"
                            class="section-fade rounded-2xl bg-white p-5 shadow-[0_15px_25px_rgba(34,44,68,0.08)]"
                            :style="{ animationDelay: `${0.1 * (index + 1)}s` }"
                        >
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#1a21bc] text-sm font-semibold text-white">
                                {{ index + 1 }}
                            </span>
                            <h3 class="pt-4 text-xl font-semibold text-[#222c44]">{{ item.step }}</h3>
                            <p class="pt-3 text-sm leading-relaxed text-slate-600">{{ item.description }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="pricing" class="bg-[#f6faff] py-20">
                <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="section-fade max-w-2xl">
                        <p class="text-sm font-semibold uppercase tracking-wider text-[#6556ff]">Pricing</p>
                        <h2 class="pt-3 text-4xl font-semibold text-[#222c44] sm:text-5xl">Basic, Pro dhe School</h2>
                    </div>

                    <div class="mt-10 grid gap-6 xl:grid-cols-3">
                        <article
                            v-for="(plan, index) in plans"
                            :key="plan.name"
                            class="section-fade pricing-card rounded-3xl border p-6"
                            :style="{ animationDelay: `${0.08 * (index + 1)}s` }"
                            :class="plan.featured
                                ? 'border-[#6556ff] bg-white shadow-[0_20px_45px_rgba(101,86,255,0.22)]'
                                : 'border-[#1a21bc]/10 bg-white shadow-[0_15px_30px_rgba(26,33,188,0.08)]'"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-[#6556ff]">{{ plan.name }}</p>
                                    <h3 class="pt-2 text-2xl font-semibold text-[#222c44]">{{ plan.badge }}</h3>
                                </div>
                                <span
                                    v-if="plan.featured"
                                    class="rounded-full bg-[#6556ff] px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white"
                                >
                                    Popular
                                </span>
                            </div>

                            <div class="mt-5">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Per ke eshte</p>
                                <ul class="mt-3 space-y-2 text-sm text-slate-700">
                                    <li v-for="item in plan.audience" :key="`${plan.name}-aud-${item}`" class="flex gap-2">
                                        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-[#1a21bc]" />
                                        <span>{{ item }}</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-6">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cka permban</p>
                                <ul class="mt-3 space-y-2 text-sm text-slate-700">
                                    <li v-for="item in plan.includes" :key="`${plan.name}-inc-${item}`" class="flex gap-2">
                                        <span class="text-[#43c639]">✓</span>
                                        <span>{{ item }}</span>
                                    </li>
                                </ul>
                            </div>

                            <div v-if="plan.excludes.length" class="mt-6">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nuk perfshin</p>
                                <ul class="mt-3 space-y-2 text-sm text-slate-700">
                                    <li v-for="item in plan.excludes" :key="`${plan.name}-exc-${item}`" class="flex gap-2">
                                        <span class="text-rose-500">✕</span>
                                        <span>{{ item }}</span>
                                    </li>
                                </ul>
                            </div>

                            <div v-if="plan.limits.length" class="mt-6">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kufizime</p>
                                <ul class="mt-3 space-y-2 text-sm text-slate-700">
                                    <li v-for="item in plan.limits" :key="`${plan.name}-lim-${item}`" class="flex gap-2">
                                        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-amber-500" />
                                        <span>{{ item }}</span>
                                    </li>
                                </ul>
                            </div>

                            <div v-if="plan.bonus.length" class="mt-6">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Bonus</p>
                                <ul class="mt-3 space-y-2 text-sm text-slate-700">
                                    <li v-for="item in plan.bonus" :key="`${plan.name}-bonus-${item}`" class="flex gap-2">
                                        <span class="text-[#1a21bc]">+</span>
                                        <span>{{ item }}</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-6 rounded-2xl bg-[#f6faff] p-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cmimi</p>
                                <p class="pt-1 text-sm text-slate-600">Range: {{ plan.range }}</p>
                                <p class="pt-1 text-2xl font-semibold text-[#222c44]">{{ plan.suggested }}</p>
                            </div>

                            <button
                                type="button"
                                class="mt-6 w-full rounded-full px-5 py-3 text-sm font-semibold transition"
                                :class="plan.featured
                                    ? 'bg-[#6556ff] text-white hover:bg-[#5849f2]'
                                    : 'border border-[#1a21bc]/20 text-[#1a21bc] hover:border-[#6556ff]/50 hover:text-[#6556ff]'"
                            >
                                {{ plan.cta }}
                            </button>
                        </article>
                    </div>
                </div>
            </section>

            <section id="reviews" class="py-20">
                <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="section-fade max-w-2xl">
                        <p class="text-sm font-semibold uppercase tracking-wider text-[#6556ff]">Testimonial / Reviews</p>
                        <h2 class="pt-3 text-4xl font-semibold text-[#222c44] sm:text-5xl">Mendimet e perdoruesve</h2>
                    </div>

                    <div class="mt-10 grid gap-5 md:grid-cols-3">
                        <article
                            v-for="(review, index) in reviews"
                            :key="review.name"
                            class="section-fade rounded-2xl border border-[#1a21bc]/10 bg-white p-6 shadow-[0_15px_30px_rgba(26,33,188,0.08)]"
                            :style="{ animationDelay: `${0.1 * (index + 1)}s` }"
                        >
                            <p class="text-base leading-relaxed text-slate-600">"{{ review.text }}"</p>
                            <p class="pt-5 text-base font-semibold text-[#222c44]">{{ review.name }}</p>
                            <p class="pt-1 text-sm text-slate-500">{{ review.role }}</p>
                            <p class="pt-3 text-amber-500">★★★★★</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="faq" class="bg-[#d5effa] py-20">
                <div class="mx-auto w-full max-w-4xl px-4 sm:px-6 lg:px-8">
                    <div class="section-fade text-center">
                        <p class="text-sm font-semibold uppercase tracking-wider text-[#1a21bc]">FAQ</p>
                        <h2 class="pt-3 text-4xl font-semibold text-[#222c44] sm:text-5xl">Pyetje te shpeshta</h2>
                    </div>

                    <div class="mt-10 space-y-4">
                        <details
                            v-for="(item, index) in faqs"
                            :key="item.question"
                            class="section-fade faq-item rounded-2xl bg-white p-5 shadow-[0_10px_25px_rgba(34,44,68,0.08)]"
                            :style="{ animationDelay: `${0.08 * (index + 1)}s` }"
                        >
                            <summary class="flex cursor-pointer list-none items-start justify-between gap-4 text-left">
                                <span class="text-lg font-semibold text-[#222c44]">{{ item.question }}</span>
                                <span class="faq-icon text-2xl leading-none text-[#6556ff]">+</span>
                            </summary>
                            <p class="pt-3 text-sm leading-relaxed text-slate-600">{{ item.answer }}</p>
                        </details>
                    </div>
                </div>
            </section>
        </main>

        <footer id="contact" class="bg-[#f6faff] py-14 text-black">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <img :src="'/images/logo/logo.svg'" alt="Courseflow logo" class="h-10 w-auto" />
                        <p class="mt-4 max-w-md text-black/75">
                            Courseflow ndihmon profesore, akademi dhe shkolla te krijojne nje ekosistem modern per mesim online.
                        </p>
                    </div>

                    <div class="lg:col-span-3">
                        <h3 class="mb-4 text-lg font-semibold">Kontakt</h3>
                        <ul class="space-y-2 text-black/80">
                            <li>support@courseflow.app</li>
                            <li>+383 44 123 456</li>
                            <li>Prishtine, Kosove</li>
                        </ul>
                    </div>

                    <div class="lg:col-span-2">
                        <h3 class="mb-4 text-lg font-semibold">Terms</h3>
                        <a href="#" class="text-black/80 transition hover:text-black">Terms &amp; Conditions</a>
                    </div>

                    <div class="lg:col-span-2">
                        <h3 class="mb-4 text-lg font-semibold">Privacy</h3>
                        <a href="#" class="text-black/80 transition hover:text-black">Privacy Policy</a>
                    </div>
                </div>

                <div class="mt-10 border-t border-black/20 pt-5 text-sm text-black/65">
                    @{{ currentYear }} Courseflow. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.elearning-page {
    font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
}

.hero-orb {
    position: absolute;
    border-radius: 9999px;
    filter: blur(55px);
    pointer-events: none;
}

.hero-orb-left {
    left: -2rem;
    top: -3rem;
    height: 13rem;
    width: 13rem;
    background: rgb(101 86 255 / 22%);
}

.hero-orb-right {
    right: -2rem;
    bottom: -3rem;
    height: 12rem;
    width: 12rem;
    background: rgb(26 33 188 / 15%);
}

.section-fade {
    opacity: 0;
    transform: translateY(22px);
    animation: fade-up 0.7s ease forwards;
}

.about-media,
.about-content {
    opacity: 0;
}

.about-media {
    transform: translateX(-42px);
}

.about-content {
    transform: translateX(42px);
}

.about-media.is-visible {
    animation: about-media-in 0.82s cubic-bezier(0.2, 0.65, 0.2, 1) forwards;
}

.about-content.is-visible {
    animation: about-content-in 0.82s cubic-bezier(0.2, 0.65, 0.2, 1) forwards;
    animation-delay: 0.12s;
}

.about-frame {
    position: relative;
    overflow: hidden;
    border-radius: 1.75rem;
    border: 1px solid rgb(26 33 188 / 12%);
    background: linear-gradient(160deg, #f6faff 0%, #edf3ff 55%, #f5f7ff 100%);
    padding: 2.1rem 1.2rem 1.2rem;
    box-shadow: 0 20px 50px rgb(26 33 188 / 14%);
}

.about-frame::after {
    content: '';
    position: absolute;
    top: 0;
    left: -35%;
    width: 30%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgb(255 255 255 / 42%), transparent);
    transform: skewX(-20deg);
    animation: about-frame-sheen 8s ease-in-out infinite;
}

.about-glow {
    position: absolute;
    border-radius: 9999px;
    pointer-events: none;
    filter: blur(35px);
    z-index: 1;
    animation: about-glow-pulse 5.2s ease-in-out infinite;
}

.about-glow-1 {
    width: 7rem;
    height: 7rem;
    top: -1.1rem;
    right: -1.2rem;
    background: rgb(101 86 255 / 24%);
}

.about-glow-2 {
    width: 6rem;
    height: 6rem;
    left: -1.1rem;
    bottom: -1.1rem;
    background: rgb(67 198 57 / 16%);
    animation-delay: 0.9s;
}

.about-image {
    position: relative;
    z-index: 2;
    display: block;
    width: 100%;
    height: clamp(16rem, 38vw, 23rem);
    margin: 0 auto;
    object-fit: cover;
    border-radius: 1rem;
    animation: about-image-pan 9s ease-in-out infinite;
}

.about-quiz-card {
    position: absolute;
    z-index: 4;
    right: 1rem;
    bottom: 1rem;
    width: 11.2rem;
    border: 1px solid rgb(26 33 188 / 14%);
    border-radius: 1rem;
    background: rgb(255 255 255 / 95%);
    padding: 0.75rem;
    box-shadow: 0 14px 30px rgb(34 44 68 / 14%);
    animation: about-card-float 4.8s ease-in-out infinite;
}

.about-quiz-title {
    color: #1a21bc;
    font-size: 0.76rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.about-quiz-row {
    margin-top: 0.42rem;
    display: flex;
    justify-content: space-between;
    gap: 0.4rem;
    color: #334155;
    font-size: 0.69rem;
    font-weight: 600;
}

.about-quiz-track {
    margin-top: 0.3rem;
    height: 0.34rem;
    border-radius: 9999px;
    background: rgb(148 163 184 / 22%);
}

.about-quiz-fill {
    display: block;
    height: 100%;
    border-radius: 9999px;
    background: #6556ff;
    animation: about-quiz-pulse 3.8s ease-in-out infinite;
}

.about-quiz-fill-1 {
    width: 87%;
}

.about-quiz-fill-2 {
    width: 73%;
    animation-delay: 0.5s;
}

.about-chip {
    position: absolute;
    z-index: 5;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 9999px;
    background: rgb(255 255 255 / 95%);
    border: 1px solid rgb(26 33 188 / 12%);
    padding: 0.4rem 0.72rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: #1a21bc;
    box-shadow: 0 10px 22px rgb(34 44 68 / 12%);
    animation: about-chip-float 5.5s ease-in-out infinite;
}

.about-chip-1 {
    left: 1rem;
    top: 1rem;
}

.about-chip-2 {
    right: 1.2rem;
    top: 4rem;
    animation-delay: 0.8s;
}

.about-list li {
    opacity: 0;
    transform: translateX(12px);
}

.about-content.is-visible .about-list li {
    animation: about-list-in 0.58s ease forwards;
}

.about-content.is-visible .about-list li:nth-child(1) {
    animation-delay: 0.22s;
}

.about-content.is-visible .about-list li:nth-child(2) {
    animation-delay: 0.32s;
}

.about-content.is-visible .about-list li:nth-child(3) {
    animation-delay: 0.42s;
}

.hero-scene {
    position: relative;
    min-height: 30rem;
    overflow: hidden;
    border-radius: 2rem;
    border: 1px solid rgb(26 33 188 / 12%);
    background: linear-gradient(150deg, #ffffff 0%, #eef2ff 100%);
    box-shadow: 0 25px 60px rgb(26 33 188 / 15%);
}

.scene-bar {
    position: absolute;
    height: 2rem;
    border-radius: 9999px;
    background: #d9dde6;
    opacity: 0.75;
    animation: scene-bar-pulse 5s ease-in-out infinite;
}

.scene-bar-1 {
    top: 3.2rem;
    right: 3.6rem;
    width: 14rem;
}

.scene-bar-2 {
    top: 6.1rem;
    right: 1.1rem;
    width: 11rem;
    animation-delay: 0.45s;
}

.scene-bar-3 {
    top: 8.9rem;
    left: 3.5rem;
    width: 8rem;
    animation-delay: 0.95s;
}

.scene-bar-4 {
    top: 12.2rem;
    right: 4.8rem;
    width: 12rem;
    animation-delay: 1.3s;
}

.scene-stack {
    position: absolute;
    left: 2.2rem;
    bottom: 4.6rem;
    width: 11.5rem;
    height: 8.5rem;
}

.scene-book {
    position: absolute;
    left: 0;
    right: 0;
    border-radius: 0.95rem;
    box-shadow: 0 8px 20px rgb(52 63 114 / 16%);
}

.scene-book-1 {
    bottom: 0;
    height: 2.35rem;
    background: #6b86ea;
}

.scene-book-2 {
    bottom: 1.9rem;
    height: 2.2rem;
    background: #ffffff;
}

.scene-book-3 {
    bottom: 3.7rem;
    height: 2.1rem;
    background: #f2d45c;
}

.scene-book-4 {
    bottom: 5.35rem;
    height: 2.35rem;
    background: #e96aa0;
}

.scene-laptop {
    position: absolute;
    right: 1.3rem;
    bottom: 1.75rem;
    width: 14.4rem;
    transform: rotate(-23deg);
    transform-origin: center;
    filter: drop-shadow(0 13px 24px rgb(38 50 105 / 25%));
}

.scene-laptop-screen {
    border: 0.55rem solid #304d9d;
    border-radius: 1.15rem;
    background: #9fd0ff;
    padding: 1rem 0.9rem;
}

.scene-line {
    display: block;
    height: 0.38rem;
    border-radius: 9999px;
    background: rgb(255 255 255 / 80%);
}

.scene-line + .scene-line {
    margin-top: 0.48rem;
}

.scene-line-1 {
    width: 85%;
}

.scene-line-2 {
    width: 70%;
}

.scene-line-3 {
    width: 58%;
}

.scene-laptop-keyboard {
    height: 0.65rem;
    margin: 0.28rem auto 0;
    width: 92%;
    border-radius: 0 0 1rem 1rem;
    background: #b8c9e9;
}

.scene-figure {
    position: absolute;
    top: 6rem;
    left: 50%;
    width: min(21rem, 88%);
    transform: translateX(-50%);
    animation: scene-float-main 6s ease-in-out infinite;
    z-index: 3;
}

.scene-badge {
    position: absolute;
    z-index: 5;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border-radius: 9999px;
    border: 1px solid rgb(26 33 188 / 12%);
    background: rgb(255 255 255 / 95%);
    padding: 0.5rem 0.8rem;
    color: #1a21bc;
    font-size: 0.75rem;
    font-weight: 600;
    box-shadow: 0 10px 22px rgb(26 33 188 / 14%);
}

.scene-badge-left {
    top: 2.2rem;
    left: 1.15rem;
}

.scene-badge-right {
    top: 11.8rem;
    right: 0.65rem;
}

.scene-avatar {
    position: absolute;
    z-index: 4;
    width: 3.1rem;
    height: 3.1rem;
    border-radius: 9999px;
    border: 3px solid white;
    object-fit: cover;
    box-shadow: 0 10px 24px rgb(34 44 68 / 18%);
}

.scene-avatar-1 {
    left: 4.9rem;
    bottom: 1rem;
    animation: scene-float-up 4.8s ease-in-out infinite;
}

.scene-avatar-2 {
    right: 5.2rem;
    top: 13.5rem;
    animation: scene-float-down 5.6s ease-in-out infinite;
}

.scene-float-up {
    animation: scene-float-up 4.5s ease-in-out infinite;
}

.scene-float-down {
    animation: scene-float-down 5.2s ease-in-out infinite;
}

.scene-drift-x {
    animation: scene-drift-x 5.4s ease-in-out infinite;
}

.scene-drift-x-reverse {
    animation: scene-drift-x-reverse 6s ease-in-out infinite;
}

@media (max-width: 1023px) {
    .about-content {
        transform: translateY(28px);
    }

    .about-content.is-visible {
        animation: fade-up 0.75s ease forwards;
    }

    .about-quiz-card {
        right: 0.8rem;
        bottom: 0.7rem;
    }

    .hero-scene {
        min-height: 27rem;
    }

    .scene-figure {
        width: min(18rem, 84%);
    }

    .feature-grid-motion {
        opacity: 0.18;
    }
}

@media (max-width: 640px) {
    .about-frame {
        padding: 1.4rem 0.85rem 1rem;
    }

    .about-chip {
        font-size: 0.63rem;
        padding: 0.32rem 0.56rem;
    }

    .about-chip-1 {
        left: 0.55rem;
        top: 0.55rem;
    }

    .about-chip-2 {
        right: 0.55rem;
        top: 3.2rem;
    }

    .about-quiz-card {
        width: 9.4rem;
        right: 0.5rem;
        bottom: 0.5rem;
        padding: 0.62rem;
    }

    .about-quiz-title {
        font-size: 0.68rem;
    }

    .about-quiz-row {
        font-size: 0.61rem;
    }

    .feature-bg-orb-1 {
        width: 9rem;
        height: 9rem;
    }

    .feature-bg-orb-2 {
        width: 10rem;
        height: 10rem;
        top: auto;
        bottom: 4rem;
    }

    .hero-scene {
        min-height: 24rem;
    }

    .scene-stack {
        left: 1rem;
        bottom: 2.4rem;
        width: 8.8rem;
        height: 6.5rem;
    }

    .scene-laptop {
        right: 0.5rem;
        width: 10.8rem;
    }

    .scene-badge {
        font-size: 0.65rem;
        padding: 0.4rem 0.62rem;
    }

    .scene-badge-left {
        top: 1rem;
        left: 0.7rem;
    }

    .scene-badge-right {
        top: 9.3rem;
        right: 0.45rem;
    }
}

.features-section {
    position: relative;
    overflow: hidden;
    background: linear-gradient(160deg, #edf2ff 0%, #e8f0ff 55%, #f3f6ff 100%);
    border-top: 1px solid rgb(26 33 188 / 10%);
    border-bottom: 1px solid rgb(26 33 188 / 10%);
}

.feature-grid-motion {
    position: absolute;
    inset: 0;
    pointer-events: none;
    opacity: 0.26;
    background-image:
        linear-gradient(rgb(101 86 255 / 18%) 1px, transparent 1px),
        linear-gradient(90deg, rgb(101 86 255 / 18%) 1px, transparent 1px);
    background-size: 34px 34px;
    animation: feature-grid-shift 18s linear infinite;
}

.feature-bg-orb {
    position: absolute;
    border-radius: 9999px;
    filter: blur(40px);
    pointer-events: none;
}

.feature-bg-orb-1 {
    width: 12rem;
    height: 12rem;
    top: -2.5rem;
    left: -1.5rem;
    background: rgb(101 86 255 / 22%);
    animation: feature-orb-a 10s ease-in-out infinite;
}

.feature-bg-orb-2 {
    width: 14rem;
    height: 14rem;
    right: 8%;
    top: 4rem;
    background: rgb(26 33 188 / 16%);
    animation: feature-orb-b 11s ease-in-out infinite;
}

.feature-bg-orb-3 {
    width: 10rem;
    height: 10rem;
    right: -2rem;
    bottom: -2rem;
    background: rgb(67 198 57 / 14%);
    animation: feature-orb-a 9s ease-in-out infinite;
    animation-delay: 0.6s;
}

.feature-card-enter {
    opacity: 0;
    transform: translateY(22px) scale(0.97);
    animation: feature-card-enter 0.75s cubic-bezier(0.22, 0.65, 0.22, 1) forwards;
}

.feature-card {
    position: relative;
    overflow: hidden;
    transition: transform 0.28s ease, box-shadow 0.28s ease;
}

.pricing-card {
    transition: transform 0.28s ease, box-shadow 0.28s ease;
}

.feature-card-glow {
    position: absolute;
    top: -1.5rem;
    right: -1.5rem;
    width: 5.5rem;
    height: 5.5rem;
    border-radius: 9999px;
    background: radial-gradient(circle, rgb(101 86 255 / 20%) 0%, rgb(101 86 255 / 0%) 70%);
    pointer-events: none;
}

.feature-card:hover,
.pricing-card:hover {
    transform: translateY(-8px) scale(1.01);
    box-shadow: 0 24px 36px rgb(26 33 188 / 16%);
}

.faq-item summary::-webkit-details-marker {
    display: none;
}

.faq-item[open] .faq-icon {
    transform: rotate(45deg);
}

.faq-icon {
    transition: transform 0.22s ease;
}

@keyframes fade-up {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes feature-card-enter {
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes feature-grid-shift {
    0% {
        background-position: 0 0, 0 0;
    }

    100% {
        background-position: 34px 34px, -34px -34px;
    }
}

@keyframes feature-orb-a {
    0%,
    100% {
        transform: translate(0, 0);
    }

    50% {
        transform: translate(12px, -10px);
    }
}

@keyframes feature-orb-b {
    0%,
    100% {
        transform: translate(0, 0);
    }

    50% {
        transform: translate(-10px, 11px);
    }
}

@keyframes about-media-in {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes about-content-in {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes about-image-pan {
    0%,
    100% {
        transform: scale(1) translateY(0);
    }

    50% {
        transform: scale(1.04) translateY(-6px);
    }
}

@keyframes about-card-float {
    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-6px);
    }
}

@keyframes about-chip-float {
    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-5px);
    }
}

@keyframes about-quiz-pulse {
    0%,
    100% {
        opacity: 0.82;
    }

    50% {
        opacity: 1;
    }
}

@keyframes about-glow-pulse {
    0%,
    100% {
        opacity: 0.5;
    }

    50% {
        opacity: 1;
    }
}

@keyframes about-frame-sheen {
    0% {
        left: -35%;
    }

    60%,
    100% {
        left: 135%;
    }
}

@keyframes about-list-in {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes scene-float-main {
    0%,
    100% {
        transform: translateX(-50%) translateY(0);
    }

    50% {
        transform: translateX(-50%) translateY(-10px);
    }
}

@keyframes scene-float-up {
    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-7px);
    }
}

@keyframes scene-float-down {
    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(8px);
    }
}

@keyframes scene-drift-x {
    0%,
    100% {
        transform: translateX(0);
    }

    50% {
        transform: translateX(-7px);
    }
}

@keyframes scene-drift-x-reverse {
    0%,
    100% {
        transform: translateX(0);
    }

    50% {
        transform: translateX(8px);
    }
}

@keyframes scene-bar-pulse {
    0%,
    100% {
        opacity: 0.45;
    }

    50% {
        opacity: 0.85;
    }
}
</style>
