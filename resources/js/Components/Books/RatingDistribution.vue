<script setup lang="ts">
import { trans } from '@/i18n';
import type { RatingDistribution } from '@/types/models';

defineProps<{
    distribution: RatingDistribution;
}>();
</script>

<template>
    <div class="space-y-2">
        <div class="mb-3 flex items-end gap-3">
            <span class="text-4xl font-bold text-slate-900 dark:text-white">{{
                distribution.average
            }}</span>
            <div class="mb-1">
                <div class="flex text-amber-400">
                    <span v-for="n in 5" :key="n">
                        <svg
                            class="h-4 w-4"
                            :class="
                                n <= Math.round(distribution.average)
                                    ? 'fill-amber-400'
                                    : 'fill-slate-200 dark:fill-slate-700'
                            "
                            viewBox="0 0 20 20"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                        </svg>
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ distribution.total }}
                    {{
                        distribution.total === 1
                            ? trans('comments.ratings_count_one')
                            : trans('comments.ratings_count')
                    }}
                </p>
            </div>
        </div>
        <div v-for="star in [5, 4, 3, 2, 1]" :key="star" class="flex items-center gap-2">
            <span class="w-4 text-right text-xs text-slate-500 dark:text-slate-400">{{
                star
            }}</span>
            <svg class="h-3 w-3 fill-amber-400" viewBox="0 0 20 20">
                <path
                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                />
            </svg>
            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div
                    class="h-full rounded-full bg-amber-400 transition-all duration-500"
                    :style="{
                        width:
                            distribution.total > 0
                                ? ((distribution[star as keyof RatingDistribution] as number) /
                                      distribution.total) *
                                      100 +
                                  '%'
                                : '0%',
                    }"
                ></div>
            </div>
            <span class="w-6 text-right text-xs text-slate-500 dark:text-slate-400">{{
                distribution[star as keyof RatingDistribution]
            }}</span>
        </div>
    </div>
</template>
