<script setup lang="ts">
withDefaults(defineProps<{ tone?: 'blue' | 'green' | 'yellow' | 'red' | 'gray' }>(), {
    tone: 'blue',
});
</script>

<template>
    <span
        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
        :class="{
            'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300': tone === 'blue',
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300':
                tone === 'green',
            'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300': tone === 'yellow',
            'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300': tone === 'red',
            'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300': tone === 'gray',
        }"
    >
        <slot />
    </span>
</template>
