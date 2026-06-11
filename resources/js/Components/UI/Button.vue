<script setup lang="ts">
withDefaults(
    defineProps<{
        type?: 'button' | 'submit';
        variant?: 'primary' | 'secondary' | 'danger';
        disabled?: boolean;
    }>(),
    {
        type: 'button',
        variant: 'primary',
        disabled: false,
    },
);
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold shadow-sm transition duration-200 ease-out hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0 disabled:hover:shadow-sm"
        :class="{
            'bg-brand-600 text-white hover:bg-brand-500': variant === 'primary',
            'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200':
                variant === 'secondary',
            'bg-rose-600 text-white hover:bg-rose-500': variant === 'danger',
        }"
    >
        <slot />
    </button>
</template>
