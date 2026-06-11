<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Check, ChevronDown, Languages } from 'lucide-vue-next';
import Dropdown from '@/Components/Dropdown.vue';
import { trans } from '@/i18n';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
const changeLocale = (value: string) => {
    router.put(route('locale.update'), { locale: value }, { preserveScroll: true });
};
</script>

<template>
    <Dropdown
        width="trigger"
        content-classes="overflow-hidden rounded-xl border border-slate-200 bg-white py-1 dark:border-slate-700 dark:bg-slate-900"
    >
        <template #trigger>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white/80 px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-brand-300 hover:text-brand-600 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-200 dark:hover:border-brand-700 dark:hover:text-blue-300"
                :aria-label="trans('locale.label')"
            >
                <Languages class="h-4 w-4" />
                <span>{{ trans(`locale.${page.props.i18n.locale}`) }}</span>
                <ChevronDown class="h-3.5 w-3.5 text-slate-400" />
            </button>
        </template>
        <template #content>
            <button
                v-for="item in page.props.i18n.supported"
                :key="item"
                type="button"
                class="flex w-full items-center justify-between gap-4 px-4 py-2.5 text-left text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-brand-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-blue-300"
                @click="changeLocale(item)"
            >
                {{ trans(`locale.${item}`) }}
                <Check
                    v-if="item === page.props.i18n.locale"
                    class="h-4 w-4 text-brand-600 dark:text-blue-300"
                />
            </button>
        </template>
    </Dropdown>
</template>
