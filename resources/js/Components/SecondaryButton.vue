<script setup lang="ts">
withDefaults(
    defineProps<{
        type?: 'button' | 'submit' | 'reset';
    }>(),
    {
        type: 'button',
    },
);
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-xs font-bold uppercase tracking-widest text-slate-700 shadow-sm transition hover:border-brand-500 hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-25 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:text-blue-300"
    >
        <slot />
    </button>
</template>
