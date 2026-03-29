import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

/** Shared: true between Inertia `start` and `finish` for any visit. */
export const visitLoading = ref(false);

let listenersAttached = false;

/** Call once from app bootstrap so the first client navigation is tracked. */
export function initInertiaVisitLoading() {
    if (listenersAttached) {
        return;
    }
    listenersAttached = true;
    router.on('start', () => {
        visitLoading.value = true;
    });
    router.on('finish', () => {
        visitLoading.value = false;
    });
}

/**
 * Returns shared `visitLoading` for v-skeleton-loader / overlays.
 */
export function useInertiaVisitLoading() {
    initInertiaVisitLoading();

    return { visitLoading };
}
