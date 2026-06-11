<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { trans } from '@/i18n';

const props = withDefaults(
    defineProps<{ value?: number | string | null; bookId?: number; editable?: boolean }>(),
    { value: 0, editable: false },
);
const rate = (rating: number) =>
    props.editable &&
    props.bookId &&
    router.post(route('books.ratings.store', props.bookId), { rating }, { preserveScroll: true });
</script>

<template>
    <div
        class="flex gap-0.5"
        :aria-label="trans('rating.summary', { rating: Number(value ?? 0).toFixed(1) })"
    >
        <button
            v-for="star in 5"
            :key="star"
            type="button"
            :disabled="!editable"
            :aria-label="trans('rating.rate', { stars: star })"
            class="text-lg disabled:cursor-default"
            :class="
                star <= Math.round(Number(value ?? 0))
                    ? 'text-amber-400'
                    : 'text-slate-300 dark:text-slate-700'
            "
            @click="rate(star)"
        >
            ★
        </button>
    </div>
</template>
