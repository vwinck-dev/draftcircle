<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { BadgeCheck, KeyRound, ShieldCheck } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import type { PageProps } from '@/types';
import { trans } from '@/i18n';

const user = usePage<PageProps>().props.auth.user!;
</script>

<template>
    <Head :title="trans('profile.security')" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <span
                    class="grid h-11 w-11 place-items-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-blue-300"
                >
                    <ShieldCheck class="h-6 w-6" />
                </span>
                <div>
                    <h1 class="font-serif text-2xl font-bold text-ink dark:text-white">
                        {{ trans('profile.security') }}
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ trans('profile.security_overview_text') }}
                    </p>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2">
                <section class="panel flex items-center gap-4 p-5">
                    <BadgeCheck class="h-6 w-6 text-emerald-500" />
                    <div>
                        <p class="text-sm font-semibold text-ink dark:text-white">
                            {{ trans('profile.email_status') }}
                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{
                                user.email_verified_at
                                    ? trans('profile.email_verified')
                                    : trans('profile.email_unverified')
                            }}
                        </p>
                    </div>
                </section>
                <section class="panel flex items-center gap-4 p-5">
                    <KeyRound class="h-6 w-6 text-brand-600 dark:text-blue-300" />
                    <div>
                        <p class="text-sm font-semibold text-ink dark:text-white">
                            {{ trans('profile.account_password') }}
                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ trans('profile.password_protected') }}
                        </p>
                    </div>
                </section>
            </div>

            <div class="panel p-4 sm:p-8">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="panel border-rose-200 p-4 sm:p-8 dark:border-rose-900/50">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
