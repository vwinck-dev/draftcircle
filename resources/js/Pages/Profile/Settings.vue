<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { KeyRound, LifeBuoy, Palette, UserRound } from 'lucide-vue-next';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Card from '@/Components/UI/Card.vue';
import ErasePersonalDataForm from '@/Pages/Profile/Partials/ErasePersonalDataForm.vue';
import { useTheme } from '@/composables/useTheme';
import type { PageProps } from '@/types';
import { trans } from '@/i18n';
const page = usePage<PageProps>();
const user = page.props.auth.user!;
const { theme } = useTheme();
</script>
<template>
    <AppLayout>
        <Head :title="trans('profile.settings')" />
        <h1 class="font-serif text-3xl font-bold text-ink dark:text-white">
            {{ trans('profile.settings') }}
        </h1>
        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <Card>
                <UserRound class="h-6 w-6 text-brand-600 dark:text-blue-300" />
                <h2 class="mt-4 font-serif font-bold text-ink dark:text-white">
                    {{ trans('profile.personal_data') }}
                </h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {{ user.name }} · {{ user.email }}
                </p>
                <Link
                    :href="route('profile.edit')"
                    class="mt-4 inline-block text-sm font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-blue-300"
                    >{{ trans('profile.edit') }}</Link
                >
            </Card>
            <Card>
                <KeyRound class="h-6 w-6 text-brand-600 dark:text-blue-300" />
                <h2 class="mt-4 font-serif font-bold text-ink dark:text-white">
                    {{ trans('profile.security') }}
                </h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {{ trans('profile.security_text') }}
                </p>
                <Link
                    :href="route('profile.security')"
                    class="mt-4 inline-block text-sm font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-blue-300"
                    >{{ trans('profile.manage_security') }}</Link
                >
            </Card>
            <Card>
                <Palette class="h-6 w-6 text-brand-600 dark:text-blue-300" />
                <h2 class="mt-4 font-serif font-bold text-ink dark:text-white">
                    {{ trans('profile.preferences') }}
                </h2>
                <label class="mt-4 block text-sm text-slate-700 dark:text-slate-200"
                    >{{ trans('theme.label')
                    }}<select v-model="theme" class="input mt-2 w-full">
                        <option value="system">{{ trans('theme.system') }}</option>
                        <option value="light">{{ trans('theme.light') }}</option>
                        <option value="dark">{{ trans('theme.dark') }}</option>
                    </select></label
                >
            </Card>
            <Card>
                <LifeBuoy class="h-6 w-6 text-brand-600 dark:text-blue-300" />
                <h2 class="mt-4 font-serif font-bold text-ink dark:text-white">
                    {{ trans('profile.support') }}
                </h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {{ trans('profile.support_text') }}
                </p>
            </Card>
        </div>

        <div class="mt-8">
            <ErasePersonalDataForm />
        </div>
    </AppLayout>
</template>
