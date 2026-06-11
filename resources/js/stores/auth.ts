import { defineStore } from 'pinia';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types';

export const useAuthStore = defineStore('auth', () => {
    const page = usePage<PageProps>();
    const user = computed(() => page.props.auth.user);
    const isStaff = computed(() =>
        user.value ? ['admin', 'librarian'].includes(user.value.role) : false,
    );

    return { user, isStaff };
});
