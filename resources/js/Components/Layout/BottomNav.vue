<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Bell, BookOpen, Home, Search } from 'lucide-vue-next';
import { trans } from '@/i18n';
import { setSlideDirection } from '@/composables/useSlideNavigation';
import { useSoundEffects } from '@/composables/useSoundEffects';

const sounds = useSoundEffects();

const links = [
    { label: 'nav.home', route: 'home', icon: Home, position: 0 },
    { label: 'nav.catalog', route: 'books.index', icon: Search, position: 1 },
    { label: 'nav.loans_short', route: 'loans.index', icon: BookOpen, position: 2 },
    { label: 'nav.notifications_short', route: 'notifications.index', icon: Bell, position: 3 },
];
</script>

<template>
    <nav
        class="fixed inset-x-3 bottom-3 z-40 flex justify-around rounded-2xl border border-slate-200 bg-white/95 p-2 shadow-2xl backdrop-blur md:hidden dark:border-slate-800 dark:bg-slate-900/95"
    >
        <Link
            v-for="item in links"
            :key="item.route"
            :href="route(item.route)"
            view-transition
            class="relative flex flex-1 flex-col items-center gap-1 rounded-xl px-1 py-1.5 text-[10px] font-medium transition duration-200 active:scale-95"
            :class="route().current(item.route) ? 'text-brand-600' : 'text-slate-400'"
            @click="setSlideDirection(item.position); sounds.tick()"
        >
            <component
                :is="item.icon"
                class="h-5 w-5 transition-transform duration-200"
                :class="{ '-translate-y-0.5 scale-110': route().current(item.route) }"
            />
            <span class="text-center leading-tight">{{ trans(item.label) }}</span>
            <span
                v-if="route().current(item.route)"
                class="absolute -bottom-1 h-1 w-6 rounded-full bg-brand-600"
            />
        </Link>
    </nav>
</template>
