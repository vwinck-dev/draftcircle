<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { BookOpenCheck, GraduationCap, Quote } from 'lucide-vue-next';
import LocaleSwitcher from '@/Components/Layout/LocaleSwitcher.vue';
import { trans } from '@/i18n';
</script>

<template>
    <div class="relative min-h-screen overflow-hidden bg-slate-50 dark:bg-slate-950">
        <div
            class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(37,99,235,.14),transparent_38%),radial-gradient(circle_at_bottom_right,rgba(14,116,144,.14),transparent_38%)]"
        />
        <div class="relative grid min-h-screen lg:grid-cols-[1.05fr_.95fr]">
            <aside class="hidden overflow-hidden bg-ink px-12 py-10 text-white lg:flex lg:flex-col">
                <Link href="/" class="flex items-center gap-3">
                    <ApplicationLogo class="h-12 w-12" />
                    <div>
                        <p class="font-serif text-xl font-bold">{{ trans('brand.name') }}</p>
                        <p class="text-xs uppercase tracking-[0.2em] text-blue-300">
                            {{ trans('brand.institution') }}
                        </p>
                    </div>
                </Link>
                <div class="my-auto max-w-xl">
                    <span
                        class="grid h-14 w-14 place-items-center rounded-2xl bg-white/10 text-blue-200"
                    >
                        <GraduationCap class="h-8 w-8" />
                    </span>
                    <p class="mt-8 text-xs font-bold uppercase tracking-[0.3em] text-blue-300">
                        {{ trans('auth.scholarly_access') }}
                    </p>
                    <h1 class="mt-4 font-serif text-5xl font-bold leading-tight">
                        {{ trans('auth.scholarly_description') }}
                    </h1>
                    <div
                        class="mt-10 flex items-start gap-3 border-l-2 border-blue-400 pl-5 text-blue-100"
                    >
                        <Quote class="mt-1 h-5 w-5 shrink-0" />
                        <p class="font-serif text-lg italic">{{ trans('brand.motto') }}</p>
                    </div>
                </div>
                <p class="flex items-center gap-2 text-xs text-slate-400">
                    <BookOpenCheck class="h-4 w-4" />{{
                        trans('footer.copyright', { year: new Date().getFullYear() })
                    }}
                </p>
            </aside>
            <main class="flex min-h-screen flex-col px-5 py-5 sm:px-8">
                <div class="flex items-center justify-between lg:justify-end">
                    <Link
                        href="/"
                        class="flex items-center gap-2 font-serif font-bold text-ink lg:hidden dark:text-white"
                    >
                        <ApplicationLogo class="h-10 w-10" />{{ trans('brand.name') }}
                    </Link>
                    <LocaleSwitcher />
                </div>
                <div class="mx-auto my-auto w-full max-w-md py-10">
                    <div class="mb-7 text-center">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-brand-600">
                            {{ trans('brand.institution') }}
                        </p>
                        <h2 class="mt-3 font-serif text-3xl font-bold text-ink dark:text-white">
                            {{ trans('auth.welcome_back') }}
                        </h2>
                        <p class="mt-3 text-sm leading-6 text-slate-500">
                            {{ trans('auth.portal_intro') }}
                        </p>
                    </div>
                    <div
                        class="rounded-3xl border border-white/80 bg-white/90 p-7 shadow-[0_30px_80px_-35px_rgba(15,35,64,.45)] backdrop-blur dark:border-slate-800 dark:bg-slate-900/90 sm:p-9"
                    >
                        <slot />
                    </div>
                </div>
                <p class="text-center text-xs text-slate-400 lg:hidden">
                    {{
                        trans('footer.copyright', {
                            year: new Date().getFullYear(),
                        })
                    }}
                </p>
            </main>
        </div>
    </div>
</template>
