<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { Book } from '@/types/models';
import StarRating from '@/Components/UI/StarRating.vue';
import BookAvailabilityBadge from './BookAvailabilityBadge.vue';
import { trans } from '@/i18n';
import { trackBookInterest } from '@/composables/useRecommendationCookies';
defineProps<{ book: Book }>();
const imageLoaded = ref(false);

const usePlaceholder = (event: Event) => {
    const image = event.target as HTMLImageElement;

    if (!image.src.endsWith('/images/book-placeholder.svg')) {
        image.src = '/images/book-placeholder.svg';
    }
};
</script>
<template>
    <Link
        :href="route('books.show', book.id)"
        class="panel interactive-lift group block overflow-hidden hover:border-brand-500"
        @click.capture="trackBookInterest(book)"
    >
        <div
            class="relative grid aspect-[4/3] place-items-center overflow-hidden bg-slate-900"
            :aria-busy="!imageLoaded"
        >
            <div v-if="!imageLoaded" class="cover-skeleton absolute inset-0" aria-hidden="true" />
            <img
                :src="book.cover_url"
                :alt="book.title"
                class="h-full w-full object-cover transition duration-500 ease-out group-hover:scale-[1.035]"
                :class="imageLoaded ? 'opacity-100' : 'scale-[1.02] opacity-0'"
                loading="lazy"
                @load="imageLoaded = true"
                @error="usePlaceholder"
            />
        </div>
        <div class="p-4">
            <div class="mb-2 flex items-center justify-between gap-2">
                <BookAvailabilityBadge
                    :available="book.available_copies"
                    :format="book.format"
                /><span class="text-xs uppercase text-slate-400">{{
                    trans(`catalog.format.${book.format}`)
                }}</span>
            </div>
            <h3 class="line-clamp-2 font-bold text-ink group-hover:text-brand-600 dark:text-white dark:group-hover:text-blue-300">
                {{ book.title }}
            </h3>
            <p class="mt-1 text-sm text-slate-500">{{ book.author }}</p>
            <StarRating class="mt-3" :value="book.ratings_avg_rating" />
        </div>
    </Link>
</template>
