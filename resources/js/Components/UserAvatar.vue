<script setup lang="ts">
import { computed, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        name: string;
        src?: string | null;
        size?: 'sm' | 'md' | 'lg' | 'xl';
    }>(),
    {
        src: null,
        size: 'md',
    },
);

const imageFailed = ref(false);

watch(
    () => props.src,
    () => {
        imageFailed.value = false;
    },
);

const initials = computed(() => {
    const parts = props.name.trim().split(/\s+/).filter(Boolean);

    if (parts.length === 0) {
        return '?';
    }

    return `${parts[0][0]}${parts.length > 1 ? parts.at(-1)?.[0] : ''}`.toUpperCase();
});

const palette = [
    'bg-blue-600',
    'bg-emerald-600',
    'bg-violet-600',
    'bg-rose-600',
    'bg-amber-600',
    'bg-cyan-600',
    'bg-indigo-600',
    'bg-fuchsia-600',
];

const colorClass = computed(() => {
    const hash = [...props.name].reduce((total, character) => total + character.codePointAt(0)!, 0);

    return palette[hash % palette.length];
});

const sizeClass = computed(
    () =>
        ({
            sm: 'h-7 w-7 text-[10px]',
            md: 'h-9 w-9 text-xs',
            lg: 'h-10 w-10 text-sm',
            xl: 'h-16 w-16 text-lg',
        })[props.size],
);
</script>

<template>
    <span
        class="inline-grid shrink-0 place-items-center overflow-hidden rounded-full font-bold uppercase tracking-wide text-white ring-2 ring-white/70 dark:ring-slate-700"
        :class="[sizeClass, colorClass]"
        :aria-label="name"
        role="img"
    >
        <img
            v-if="src && !imageFailed"
            :src="src"
            :alt="name"
            class="h-full w-full object-cover"
            @error="imageFailed = true"
        />
        <span v-else aria-hidden="true">{{ initials }}</span>
    </span>
</template>
