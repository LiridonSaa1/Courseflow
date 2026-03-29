import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useTenantNavActive() {
    const page = usePage();
    const urlPath = computed(() => String(page.url || '').split('?')[0].split('#')[0] || '');

    function navActive(href) {
        const path = urlPath.value || '';

        if (href === '/dashboard') {
            return path === '/dashboard' || path === '/';
        }

        return path === href || path.startsWith(`${href}/`);
    }

    return { navActive, urlPath };
}
