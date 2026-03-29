import './bootstrap';
import { initInertiaVisitLoading } from '@/composables/useInertiaVisitLoading';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';

const vuetify = createVuetify({
    components,
    directives,
    theme: {
        defaultTheme: 'light',
        themes: {
            light: {
                colors: {
                    primary: '#1861ff',
                    secondary: '#6366f1',
                    surface: '#ffffff',
                    background: '#ffffff',
                },
            },
        },
    },
});

initInertiaVisitLoading();

createInertiaApp({
    title: (title) => (title ? `${title} · Courseflow` : 'Courseflow'),
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({
            render: () => h(components.VApp, null, { default: () => h(App, props) }),
        });
        app.use(plugin);
        app.use(createPinia());
        app.use(vuetify);
        app.mount(el);
    },
});
