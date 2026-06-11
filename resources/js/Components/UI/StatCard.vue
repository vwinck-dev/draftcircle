<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
defineProps<{ label: string; value: string | number; hint?: string; href?: string }>();
</script>

<template>
    <component
        :is="href ? Link : 'section'"
        :href="href"
        class="panel group block p-5 transition-all duration-200"
        :class="href ? 'cursor-pointer hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-md dark:hover:border-brand-700' : ''"
    >
        <p class="text-sm font-medium text-slate-500 transition-colors" :class="href ? 'group-hover:text-brand-600 dark:group-hover:text-blue-400' : ''">{{ label }}</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-ink dark:text-white">{{ value }}</p>
        <p v-if="hint" class="mt-2 text-xs text-emerald-600">{{ hint }}</p>
    </component>
</template>
