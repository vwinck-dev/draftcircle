import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types';

export const useNotificationStore = defineStore('notifications', () => {
    const page = usePage<PageProps>();
    // Extra count accumulated from real-time Echo events between page navigations
    const realtimeExtra = ref(0);

    const unread = computed(() => page.props.notifications.unread + realtimeExtra.value);

    const increment = () => realtimeExtra.value++;

    // Call when Inertia navigates and page props are refreshed so we don't double-count
    const resetExtra = () => (realtimeExtra.value = 0);

    return { unread, increment, resetExtra };
});
