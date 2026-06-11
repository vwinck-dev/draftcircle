<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { trans } from '@/i18n';
</script>

<template>
    <Head :title="trans('nav.dashboard')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ trans('nav.dashboard') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        {{ trans('auth.portal_intro') }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
