<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

const isSticky = ref(false);
const sliderModules = [Autoplay, Pagination];
const currentYear = new Date().getFullYear();

const companyLogos = [
    '/images/companies/airbnb.svg',
    '/images/companies/fedex.svg',
    '/images/companies/google.svg',
    '/images/companies/hubspot.svg',
    '/images/companies/microsoft.svg',
    '/images/companies/walmart.svg',
    '/images/companies/airbnb.svg',
    '/images/companies/fedex.svg',
];

const courses = [
    {
        heading: 'Full stack modern javascript',
        teacher: 'Colt Stelle',
        imgSrc: '/images/courses/courseone.png',
        students: 150,
        classes: 12,
        price: 20,
        rating: 4.4,
    },
    {
        heading: 'Design system with React programme',
        teacher: 'Colt Stelle',
        imgSrc: '/images/courses/coursetwo.png',
        students: 130,
        classes: 12,
        price: 20,
        rating: 4.5,
    },
    {
        heading: 'Design banner with Figma',
        teacher: 'Colt Stelle',
        imgSrc: '/images/courses/coursethree.png',
        students: 120,
        classes: 12,
        price: 20,
        rating: 5,
    },
    {
        heading: 'We Launch Delia Webflow this Week!',
        teacher: 'Colt Stelle',
        imgSrc: '/images/courses/courseone.png',
        students: 150,
        classes: 12,
        price: 20,
        rating: 5,
    },
    {
        heading: 'We Launch Delia Webflow this Week!',
        teacher: 'Colt Stelle',
        imgSrc: '/images/courses/coursetwo.png',
        students: 150,
        classes: 12,
        price: 20,
        rating: 5,
    },
    {
        heading: 'We Launch Delia Webflow this Week!',
        teacher: 'Colt Stelle',
        imgSrc: '/images/courses/coursethree.png',
        students: 150,
        classes: 12,
        price: 20,
        rating: 4.2,
    },
];

const mentors = [
    { name: 'Shoo Thar Mien', role: 'Senior UX Designer', imgSrc: '/images/mentor/user3.png' },
    { name: 'Shoo Thar Mien', role: 'Senior UX Designer', imgSrc: '/images/mentor/user2.png' },
    { name: 'Shoo Thar Mien', role: 'Senior UX Designer', imgSrc: '/images/mentor/user1.png' },
    { name: 'Shoo Thar Mien', role: 'Senior UX Designer', imgSrc: '/images/mentor/user3.png' },
    { name: 'Shoo Thar Mien', role: 'Senior UX Designer', imgSrc: '/images/mentor/user2.png' },
    { name: 'Shoo Thar Mien', role: 'Senior UX Designer', imgSrc: '/images/mentor/user1.png' },
];

const testimonials = [
    {
        name: 'Robert Fox',
        role: 'CEO, Parkview Int.Ltd',
        imgSrc: '/images/testimonial/user.svg',
        comment:
            'There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour.',
        rating: 5,
    },
    {
        name: 'Leslie Alexander',
        role: 'CEO, Parkview Int.Ltd',
        imgSrc: '/images/mentor/user2.png',
        comment:
            'There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour.',
        rating: 5,
    },
    {
        name: 'Cody Fisher',
        role: 'CEO, Parkview Int.Ltd',
        imgSrc: '/images/mentor/user3.png',
        comment:
            'There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour.',
        rating: 5,
    },
    {
        name: 'Robert Fox',
        role: 'CEO, Parkview Int.Ltd',
        imgSrc: '/images/mentor/user1.png',
        comment:
            'There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour.',
        rating: 5,
    },
    {
        name: 'Leslie Alexander',
        role: 'CEO, Parkview Int.Ltd',
        imgSrc: '/images/mentor/user2.png',
        comment:
            'There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour.',
        rating: 5,
    },
    {
        name: 'Cody Fisher',
        role: 'CEO, Parkview Int.Ltd',
        imgSrc: '/images/mentor/user3.png',
        comment:
            'There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour.',
        rating: 5,
    },
];

const topLinks = [
    { label: 'Home', href: '#home-section' },
    { label: 'Courses', href: '#courses' },
    { label: 'Mentor', href: '#mentor' },
    { label: 'Group', href: '#group' },
    { label: 'Testimonial', href: '#testimonial' },
];

const footerOther = ['About Us', 'Our Team', 'Career', 'Services', 'Contact'];
const heroPills = ['Flexible', 'Learning path', 'Community'];

const roundedRating = (rating) => Math.round(rating);

const onScroll = () => {
    isSticky.value = window.scrollY >= 80;
};

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
});
</script>

<template>
    <Head title="eLearning" />

    <div class="elearning-page min-h-screen bg-white text-slate-900">
        <header
            class="fixed top-0 z-40 w-full border-b border-slate-100 bg-white transition-all duration-300"
            :class="isSticky ? 'py-4 shadow-lg' : 'py-6 shadow-none'"
        >
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <a href="#home-section" class="shrink-0">
                    <img :src="'/images/logo/logo.svg'" alt="Courseflow logo" class="h-9 w-auto" />
                </a>

                <nav class="hidden items-center gap-8 text-lg font-medium text-[#222c44] lg:flex">
                    <a
                        v-for="item in topLinks"
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
                        class="hidden rounded-full bg-[#6556ff]/15 px-8 py-3 text-base font-medium text-[#6556ff] transition hover:bg-[#6556ff] hover:text-white sm:inline-flex"
                    >
                        Sign In
                    </Link>
                    <Link
                        href="/subscribe"
                        class="rounded-full bg-[#6556ff] px-8 py-3 text-base font-medium text-white transition hover:bg-[#5849f2]"
                    >
                        Sign Up
                    </Link>
                </div>
            </div>
        </header>

        <main class="pt-28">
            <section id="home-section" class="bg-[#f6faff] py-20">
                <div class="mx-auto grid w-full max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-12 lg:items-center lg:px-8">
                    <div class="lg:col-span-6">
                        <div class="inline-flex items-center gap-2 text-sm font-semibold text-[#43c639]">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-[#43c639]" />
                            Get 30% off on first enroll
                        </div>

                        <h1 class="pt-6 text-4xl font-semibold leading-tight text-[#222c44] sm:text-5xl">
                            Advance your engineering skills with us.
                        </h1>

                        <p class="pt-5 text-lg text-slate-600">
                            Build skills with our courses and mentor from world-class companies.
                        </p>

                        <div class="relative pt-6">
                            <input
                                type="text"
                                placeholder="search courses..."
                                class="w-full rounded-full border border-slate-200 bg-white py-6 pl-8 pr-24 text-lg text-slate-700 shadow-[0_40px_60px_rgba(26,33,188,0.1)] outline-none ring-[#6556ff]/30 transition focus:ring"
                            />
                            <button
                                type="button"
                                class="absolute right-2 top-1/2 flex h-14 w-14 -translate-y-1/2 items-center justify-center rounded-full bg-[#1a21bc] transition hover:bg-[#151a96]" style="margin-top: 12px;"
                                aria-label="Search"
                            >
                                <img :src="'/images/banner/search.svg'" alt="Search icon" class="h-6 w-6" />
                            </button>
                        </div>

                        <div class="flex flex-wrap items-center gap-6 pt-10 sm:gap-10">
                            <div v-for="pill in heroPills" :key="pill" class="flex items-center gap-2">
                                <img :src="'/images/banner/check-circle.svg'" alt="Check icon" class="h-7 w-7" />
                                <p class="text-base text-black sm:text-lg">{{ pill }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-6">
                        <img :src="'/images/banner/mahila.png'" alt="Learning banner" class="mx-auto w-full max-w-[680px]" />
                    </div>
                </div>
            </section>

            <section class="py-16 text-center">
                <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <h2 class="text-2xl font-semibold text-[#222c44]">Trusted by companies of all sizes</h2>
                    <div class="mt-10 border-b border-slate-200 pb-12">
                        <Swiper
                            class="company-swiper"
                            :modules="[Autoplay]"
                            :slides-per-view="2"
                            :space-between="30"
                            :loop="true"
                            :allow-touch-move="false"
                            :speed="2200"
                            :autoplay="{ delay: 1200, disableOnInteraction: false }"
                            :breakpoints="{
                                640: { slidesPerView: 3 },
                                900: { slidesPerView: 4 },
                                1200: { slidesPerView: 6 },
                            }"
                        >
                            <SwiperSlide v-for="(logo, index) in companyLogos" :key="logo + index">
                                <img :src="logo" alt="Company logo" class="mx-auto h-8 w-auto" />
                            </SwiperSlide>
                        </Swiper>
                    </div>
                </div>
            </section>

            <section id="courses" class="py-20">
                <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mb-10 flex flex-wrap items-center justify-between gap-4">
                        <h2 class="text-4xl font-semibold text-[#222c44] sm:text-5xl">Popular courses.</h2>
                        <a href="#home-section" class="text-lg font-medium text-[#6556ff] transition hover:tracking-wide">
                            Explore courses >
                        </a>
                    </div>

                    <Swiper
                        class="courses-swiper"
                        :modules="[Autoplay]"
                        :slides-per-view="1"
                        :space-between="22"
                        :loop="true"
                        :autoplay="{ delay: 2600, disableOnInteraction: false }"
                        :breakpoints="{
                            700: { slidesPerView: 2 },
                            1200: { slidesPerView: 3 },
                        }"
                    >
                        <SwiperSlide v-for="course in courses" :key="course.heading + course.imgSrc">
                            <article class="mx-1 mb-10 rounded-2xl bg-white p-3 shadow-[0_40px_20px_rgba(0,0,0,0.15)]">
                                <div class="relative overflow-hidden rounded-3xl">
                                    <img :src="course.imgSrc" :alt="course.heading" class="h-64 w-full object-cover" />
                                    <div class="absolute -bottom-1 right-4 rounded-full bg-[#1a21bc] p-4">
                                        <h3 class="text-center text-xs font-medium uppercase text-white">best<br />seller</h3>
                                    </div>
                                </div>

                                <div class="px-3 pb-6 pt-6">
                                    <h3 class="text-2xl font-bold text-black">{{ course.heading }}</h3>
                                    <p class="pt-4 text-base text-black/70">{{ course.teacher }}</p>

                                    <div class="flex items-center justify-between border-b border-slate-200 py-5">
                                        <div class="flex items-center gap-3">
                                            <p class="text-2xl font-medium text-red-700">{{ course.rating }}</p>
                                            <div class="flex text-lg text-yellow-500">
                                                <span
                                                    v-for="star in 5"
                                                    :key="`course-${course.heading}-${star}`"
                                                    v-html="star <= roundedRating(course.rating) ? '&#9733;' : '&#9734;'"
                                                />
                                            </div>
                                        </div>
                                        <p class="text-3xl font-medium text-black">${{ course.price }}</p>
                                    </div>

                                    <div class="flex items-center justify-between pt-5 text-base text-black/70">
                                        <p>{{ course.classes }} classes</p>
                                        <p>{{ course.students }} students</p>
                                    </div>
                                </div>
                            </article>
                        </SwiperSlide>
                    </Swiper>
                </div>
            </section>

            <section id="mentor" class="bg-[#d5effa] py-20">
                <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <h2 class="text-4xl font-semibold text-[#222c44] sm:text-5xl">
                        Meet with our<br />
                        mentor.
                    </h2>

                    <Swiper
                        class="mentor-swiper mt-8"
                        :modules="[Autoplay]"
                        :slides-per-view="1"
                        :space-between="24"
                        :loop="true"
                        :autoplay="{ delay: 2500, disableOnInteraction: false }"
                        :breakpoints="{
                            700: { slidesPerView: 2 },
                            1200: { slidesPerView: 3 },
                        }"
                    >
                        <SwiperSlide v-for="mentor in mentors" :key="mentor.imgSrc + mentor.name">
                            <article class="mx-1 py-8 text-center">
                                <div class="relative">
                                    <img :src="mentor.imgSrc" :alt="mentor.name" class="mx-auto h-[360px] w-auto" />
                                    <div class="absolute bottom-[102px] right-[85px] rounded-full bg-white p-4">
                                        <img :src="'/images/mentor/linkedin.svg'" alt="LinkedIn icon" class="h-6 w-6" />
                                    </div>
                                </div>
                                <h3 class="-mt-10 text-2xl font-semibold text-[#222c44]">{{ mentor.name }}</h3>
                                <p class="pt-2 text-lg text-[#222c44]/60">{{ mentor.role }}</p>
                            </article>
                        </SwiperSlide>
                    </Swiper>
                </div>
            </section>

            <section id="testimonial" class="py-20">
                <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Swiper
                        class="testimonial-swiper"
                        :modules="sliderModules"
                        :slides-per-view="1"
                        :space-between="24"
                        :loop="true"
                        :pagination="{ clickable: true }"
                        :autoplay="{ delay: 3200, disableOnInteraction: false }"
                        :breakpoints="{
                            800: { slidesPerView: 2 },
                            1200: { slidesPerView: 3 },
                        }"
                    >
                        <SwiperSlide v-for="(testimonial, index) in testimonials" :key="testimonial.name + index">
                            <article
                                class="relative mx-2 my-14 rounded-2xl bg-white p-5 pt-12"
                                :class="index % 2 === 0 ? 'shadow-[0_5.5px_11px_rgba(89,104,118,0.05)]' : 'shadow-[5.5px_38px_110px_rgba(89,104,118,0.15)]'"
                            >
                                <img
                                    :src="testimonial.imgSrc"
                                    :alt="testimonial.name"
                                    class="absolute -top-10 h-[92px] w-[92px] rounded-full border-4 border-white object-cover"
                                />
                                <p class="my-4 text-base text-slate-600">{{ testimonial.comment }}</p>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-[#222c44]">{{ testimonial.name }}</h3>
                                        <p class="text-sm text-slate-500">{{ testimonial.role }}</p>
                                    </div>
                                    <div class="flex text-lg text-yellow-500">
                                        <span v-for="star in testimonial.rating" :key="`testimonial-${index}-${star}`" v-html="'&#9733;'" />
                                    </div>
                                </div>
                            </article>
                        </SwiperSlide>
                    </Swiper>
                </div>
            </section>

            <section id="group" class="pb-20">
                <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div
                        class="overflow-hidden rounded-3xl bg-[#1a21bc] bg-cover bg-no-repeat px-6 py-14 sm:px-12 lg:px-20"
                        style="background-image: url('/images/newsletter/bgFile.png')"
                    >
                        <h2 class="text-center text-4xl font-semibold text-white sm:text-5xl">Newsletter.</h2>
                        <p class="pt-3 text-center text-base text-white/75">
                            Subscrible our newsletter for discounts, promo and many more.
                        </p>
                        <div class="mx-auto mt-8 max-w-3xl">
                            <div class="relative">
                                <input
                                    type="email"
                                    placeholder="Enter your email address"
                                    class="w-full rounded-full bg-white py-6 pl-8 pr-24 text-base text-slate-700 outline-none"
                                />
                                <button
                                    type="button"
                                    class="absolute right-2 top-1/2 flex h-14 w-14 -translate-y-1/2 items-center justify-center rounded-full bg-[#6556ff] transition hover:bg-[#5849f2]"
                                    aria-label="Send newsletter"
                                >
                                    <img :src="'/images/newsletter/send.svg'" alt="Send icon" class="h-6 w-6" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="bg-[#d5effa] py-14">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-12">
                    <div class="lg:col-span-4">
                        <img :src="'/images/logo/logo.svg'" alt="Courseflow logo" class="h-10 w-auto" />
                        <div class="mt-4 flex items-center gap-4 text-black">
                            <a href="#" class="text-3xl transition hover:text-[#6556ff]">f</a>
                            <a href="#" class="text-3xl transition hover:text-[#6556ff]">t</a>
                            <a href="#" class="text-3xl transition hover:text-[#6556ff]">i</a>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <h3 class="mb-4 text-2xl font-medium text-black">Links</h3>
                        <ul class="space-y-2 text-black/60">
                            <li v-for="item in topLinks" :key="`footer-${item.label}`">
                                <a :href="item.href" class="transition hover:text-[#6556ff]">{{ item.label }}</a>
                            </li>
                        </ul>
                    </div>

                    <div class="lg:col-span-2">
                        <h3 class="mb-4 text-2xl font-medium text-black">Other</h3>
                        <ul class="space-y-2 text-black/60">
                            <li v-for="item in footerOther" :key="item">
                                <a href="#" class="transition hover:text-[#6556ff]">{{ item }}</a>
                            </li>
                        </ul>
                    </div>

                    <div class="lg:col-span-4">
                        <p class="text-lg text-black/60">925 Filbert Street Pennsylvania 18072</p>
                        <p class="mt-6 text-lg text-black/60">+45 3411-4411</p>
                        <p class="mt-6 text-lg text-black/60">info@gmail.com</p>
                    </div>
                </div>

                <div class="mt-10 flex flex-wrap items-center justify-between gap-4 text-sm text-black/50">
                    <p>@{{ currentYear }} Agency. All Rights Reserved.</p>
                    <div class="flex items-center gap-5">
                        <a href="#" class="transition hover:text-[#6556ff]">Privacy policy</a>
                        <a href="#" class="transition hover:text-[#6556ff]">Terms &amp; conditions</a>
                    </div>
                    <p>Distributed by ThemeWagon</p>
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

:deep(.company-swiper .swiper-wrapper),
:deep(.courses-swiper .swiper-wrapper),
:deep(.mentor-swiper .swiper-wrapper),
:deep(.testimonial-swiper .swiper-wrapper) {
    align-items: stretch;
}

:deep(.testimonial-swiper .swiper-pagination) {
    position: static;
    margin-top: 0.75rem;
}

:deep(.testimonial-swiper .swiper-pagination-bullet) {
    height: 0.625rem;
    width: 0.625rem;
    background: #1a21bc;
    opacity: 0.25;
}

:deep(.testimonial-swiper .swiper-pagination-bullet-active) {
    opacity: 1;
}
</style>
