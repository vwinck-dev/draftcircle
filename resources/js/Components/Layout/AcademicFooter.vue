<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Bell,
    BookOpen,
    Cookie,
    Github,
    GraduationCap,
    Home,
    Landmark,
    Library,
    LogOut,
    Mail,
    MapPin,
    Shield,
    UserRound,
} from 'lucide-vue-next';
import { trans } from '@/i18n';
import { openCookiePreferences } from '@/composables/useRecommendationCookies';

const year = new Date().getFullYear();

const navLinks = [
    { label: 'nav.home', route: 'home', icon: Home },
    { label: 'nav.catalog', route: 'books.index', icon: Library },
    { label: 'nav.loans', route: 'loans.index', icon: BookOpen },
    { label: 'nav.notifications', route: 'notifications.index', icon: Bell },
];
</script>

<template>
    <footer
        class="mt-16 border-t border-slate-200 bg-ink pb-20 text-slate-300 dark:border-slate-800 md:pb-0"
    >
        <div
            class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-[1.4fr_1fr_1fr_1fr]"
        >
            <section>
                <div class="flex items-center gap-3 text-white">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-500">
                        <GraduationCap class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="font-serif text-xl font-bold">{{ trans('footer.about') }}</p>
                        <p class="text-xs uppercase tracking-[0.2em] text-blue-300">
                            {{ trans('brand.institution') }}
                        </p>
                    </div>
                </div>
                <p class="mt-5 max-w-md text-sm leading-7 text-slate-400">
                    {{ trans('footer.about_text') }}
                </p>
                <p class="mt-5 font-serif text-sm italic text-blue-200">
                    "{{ trans('brand.motto') }}"
                </p>
            </section>

            <section>
                <h2 class="flex items-center gap-2 font-bold text-white">
                    <MapPin class="h-4 w-4 text-blue-300" />{{ trans('footer.navigation') }}
                </h2>
                <nav class="mt-4 grid gap-3 text-sm">
                    <Link
                        v-for="item in navLinks"
                        :key="item.route"
                        :href="route(item.route)"
                        class="flex items-center gap-2 transition-colors hover:text-white"
                    >
                        <component :is="item.icon" class="h-4 w-4 shrink-0" />
                        {{ trans(item.label) }}
                    </Link>
                </nav>
            </section>

            <section>
                <h2 class="flex items-center gap-2 font-bold text-white">
                    <UserRound class="h-4 w-4 text-blue-300" />{{ trans('footer.account') }}
                </h2>
                <nav class="mt-4 grid gap-3 text-sm">
                    <Link
                        :href="route('profile.settings')"
                        class="flex items-center gap-2 transition-colors hover:text-white"
                    >
                        <UserRound class="h-4 w-4 shrink-0" />{{ trans('nav.profile') }}
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="flex items-center gap-2 text-left transition-colors hover:text-rose-400"
                    >
                        <LogOut class="h-4 w-4 shrink-0" />{{ trans('nav.logout') }}
                    </Link>
                </nav>
            </section>

            <section>
                <h2 class="flex items-center gap-2 font-bold text-white">
                    <Landmark class="h-4 w-4 text-blue-300" />{{ trans('footer.institution') }}
                </h2>
                <div class="mt-4 grid gap-3 text-sm">
                    <p class="flex items-center gap-2">
                        <Mail class="h-4 w-4 shrink-0" />{{ trans('footer.support') }}
                    </p>
                    <p class="flex items-center gap-2">
                        <Shield class="h-4 w-4 shrink-0" />{{ trans('footer.ethics') }}
                    </p>
                    <a
                        href="https://github.com/vwinck-dev"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex w-fit items-center gap-2 transition-colors hover:text-white"
                    >
                        <Github class="h-4 w-4 shrink-0" />{{ trans('footer.developer') }}
                    </a>
                    <button
                        type="button"
                        class="flex w-fit items-center gap-2 text-left transition-colors hover:text-white"
                        @click="openCookiePreferences"
                    >
                        <Cookie class="h-4 w-4 shrink-0" />{{ trans('footer.cookie_preferences') }}
                    </button>
                </div>
            </section>
        </div>
        <div class="border-t border-white/10 px-4 py-4 text-center text-xs text-slate-500">
            {{ trans('footer.copyright', { year }) }}
        </div>
    </footer>
</template>
