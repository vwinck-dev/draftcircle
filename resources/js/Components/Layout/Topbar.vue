<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Bell, ChevronDown, LogOut, Moon, Settings } from 'lucide-vue-next';
import { useThemeStore } from '@/stores/theme';
import type { User } from '@/types/models';
import Dropdown from '@/Components/Dropdown.vue';
import LocaleSwitcher from './LocaleSwitcher.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import { trans } from '@/i18n';
import { setSlideDirection } from '@/composables/useSlideNavigation';
import { useSoundEffects } from '@/composables/useSoundEffects';

const sounds = useSoundEffects();

defineProps<{ user: User; unread: number; isStaff: boolean }>();
const theme = useThemeStore();
const links = [
    { label: 'nav.home', route: 'home', position: 0 },
    { label: 'nav.catalog', route: 'books.index', position: 1 },
    { label: 'nav.loans', route: 'loans.index', position: 2 },
    { label: 'nav.notifications', route: 'notifications.index', position: 3 },
];
</script>

<template>
    <header
        class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/90"
    >
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-5 px-4 sm:px-6">
            <Link
                :href="route('home')"
                view-transition
                class="flex shrink-0 items-center gap-2 font-serif font-bold text-ink dark:text-white"
                @click="setSlideDirection(0)"
            >
                <ApplicationLogo class="h-10 w-10" /><span
                    class="hidden whitespace-nowrap sm:block"
                    >{{ trans('brand.name') }}</span
                >
            </Link>
            <nav class="hidden flex-1 items-center justify-center gap-1 md:flex">
                <Link
                    v-for="item in links"
                    :key="item.route"
                    :href="route(item.route)"
                    view-transition
                    class="whitespace-nowrap rounded-xl px-3 py-2 text-sm font-medium transition duration-200 hover:-translate-y-0.5"
                    :class="
                        route().current(item.route)
                            ? 'bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-blue-300'
                            : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-900'
                    "
                    @click="
                        setSlideDirection(item.position);
                        sounds.tick();
                    "
                >
                    {{ trans(item.label) }}</Link
                >
                <Link
                    v-if="isStaff"
                    :href="route('admin.dashboard')"
                    view-transition
                    class="rounded-xl px-3 py-2 text-sm font-medium text-slate-500 transition duration-200 hover:-translate-y-0.5 hover:bg-slate-100 dark:hover:bg-slate-900"
                    @click="setSlideDirection(4)"
                >
                    {{ trans('nav.admin') }}</Link
                >
            </nav>
            <div class="ml-auto flex items-center gap-2">
                <LocaleSwitcher class="hidden xl:inline-flex" />
                <button
                    type="button"
                    :aria-label="trans('theme.toggle')"
                    class="rounded-xl p-2 text-slate-500 transition duration-200 hover:-translate-y-0.5 hover:bg-slate-100 active:scale-95 dark:text-white dark:hover:bg-slate-900"
                    @click="theme.theme = theme.theme === 'dark' ? 'light' : 'dark'"
                >
                    <Moon
                        class="h-5 w-5 transition-all"
                        :class="theme.theme === 'dark' ? 'fill-white' : 'fill-transparent'"
                    />
                </button>
                <Link
                    :href="route('notifications.index')"
                    view-transition
                    :aria-label="trans('nav.notifications')"
                    class="relative rounded-xl p-2 text-slate-500 transition duration-200 hover:-translate-y-0.5 hover:bg-slate-100 active:scale-95 dark:hover:bg-slate-900"
                    @click="setSlideDirection(3)"
                >
                    <Bell class="h-5 w-5" /><span
                        v-if="unread"
                        class="absolute right-0 top-0 grid h-5 min-w-5 place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white"
                        >{{ unread }}</span
                    >
                </Link>
                <Dropdown
                    width="trigger"
                    content-classes="overflow-hidden rounded-xl border border-slate-200 bg-white py-1 dark:border-slate-700 dark:bg-slate-900"
                >
                    <template #trigger>
                        <button
                            type="button"
                            class="flex max-w-60 items-center gap-2 rounded-xl bg-slate-100 px-3 py-2 text-sm font-semibold transition duration-200 hover:-translate-y-0.5 hover:bg-slate-200 active:scale-[0.98] dark:bg-slate-900 dark:hover:bg-slate-800"
                            :aria-label="user.name"
                        >
                            <UserAvatar :name="user.name" :src="user.avatar_url" size="sm" />
                            <span class="hidden truncate sm:block">{{ user.name }}</span>
                            <ChevronDown class="hidden h-4 w-4 shrink-0 text-slate-400 sm:block" />
                        </button>
                    </template>
                    <template #content>
                        <Link
                            :href="route('profile.settings')"
                            class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-brand-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-blue-300"
                        >
                            <Settings class="h-4 w-4" />{{ trans('nav.profile') }}
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm font-medium text-rose-600 transition hover:bg-rose-50 dark:hover:bg-rose-950/30"
                        >
                            <LogOut class="h-4 w-4" />{{ trans('nav.logout') }}
                        </Link>
                    </template>
                </Dropdown>
            </div>
        </div>
    </header>
</template>
