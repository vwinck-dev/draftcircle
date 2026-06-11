<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { trans } from '@/i18n';

defineProps<{ canLogin?: boolean; canRegister?: boolean }>();
</script>

<template>
    <GuestLayout>
        <Head :title="trans('brand.name')" />
        <div class="text-center">
            <p class="font-serif text-xl font-bold text-ink dark:text-white">
                {{ trans('brand.motto') }}
            </p>
            <div class="mt-6 flex justify-center gap-3">
                <Link
                    v-if="canLogin"
                    :href="route('login')"
                    class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white"
                    >{{ trans('auth.login') }}
                </Link>
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-bold text-slate-600 dark:border-slate-700 dark:text-slate-200"
                >
                    {{ trans('auth.register') }}</Link
                >
            </div>
        </div>
    </GuestLayout>
</template>
