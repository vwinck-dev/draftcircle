<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import BottomNav from '@/Components/Layout/BottomNav.vue';
import AcademicFooter from '@/Components/Layout/AcademicFooter.vue';
import CookieNotice from '@/Components/Layout/CookieNotice.vue';
import RecommendationShelf from '@/Components/Layout/RecommendationShelf.vue';
import Topbar from '@/Components/Layout/Topbar.vue';
import { useNotificationStore } from '@/stores/notifications';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
const notifStore = useNotificationStore();
const user = computed(() => page.props.auth.user!);
const isStaff = computed(() => ['admin', 'librarian'].includes(user.value.role));
const showRecommendations = computed(() =>
    ['Home/Index', 'Books/Index', 'Books/Show'].includes(page.component),
);
</script>

<template>
    <div class="flex min-h-screen flex-col overflow-x-clip">
        <Topbar :user="user" :unread="notifStore.unread" :is-staff="isStaff" />

        <div class="flex-1">
            <main class="mx-auto w-full max-w-7xl px-4 py-7 sm:px-6">
                <div>
                    <slot />
                </div>
            </main>

            <RecommendationShelf
                v-if="showRecommendations"
                :recommendations="page.props.recommendations"
            />
        </div>
        <AcademicFooter />
        <BottomNav />
        <CookieNotice />
    </div>
</template>
