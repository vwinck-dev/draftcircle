<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Calendar, Globe2 } from 'lucide-vue-next';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import BookAvailabilityBadge from '@/Components/Books/BookAvailabilityBadge.vue';
import RatingDistribution from '@/Components/Books/RatingDistribution.vue';
import CommentSection from '@/Components/Books/CommentSection.vue';
import Button from '@/Components/UI/Button.vue';
import StarRating from '@/Components/UI/StarRating.vue';
import type {
    Book,
    BookComment,
    RatingDistribution as RatingDistributionType,
} from '@/types/models';
import { trans } from '@/i18n';

defineProps<{
    book: Book;
    comments: BookComment[];
    ratingDistribution: RatingDistributionType;
    userRating: number | null;
}>();

const imageLoaded = ref(false);
const activeTab = ref<'details' | 'comments'>('details');

const usePlaceholder = (event: Event) => {
    const image = event.target as HTMLImageElement;

    if (!image.src.endsWith('/images/book-placeholder.svg')) {
        image.src = '/images/book-placeholder.svg';
    }
};
</script>
<template>
    <AppLayout>
        <Head :title="book.title" />
        <div class="grid gap-8 lg:grid-cols-[320px_1fr]">
            <div
                class="panel relative aspect-[3/4] overflow-hidden bg-slate-900"
                :aria-busy="!imageLoaded"
            >
                <div
                    v-if="!imageLoaded"
                    class="cover-skeleton absolute inset-0"
                    aria-hidden="true"
                />
                <img
                    :src="book.cover_url"
                    :alt="book.title"
                    class="h-full w-full object-cover transition duration-500 ease-out"
                    :class="imageLoaded ? 'opacity-100' : 'scale-[1.02] opacity-0'"
                    @load="imageLoaded = true"
                    @error="usePlaceholder"
                />
            </div>
            <div>
                <div class="flex flex-wrap gap-2">
                    <BookAvailabilityBadge
                        :available="book.available_copies"
                        :format="book.format"
                    /><span
                        v-for="category in book.categories"
                        :key="category.id"
                        class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800"
                        >{{ category.name }}</span
                    >
                </div>
                <h1
                    class="mt-5 font-serif text-4xl font-bold tracking-tight text-ink dark:text-white"
                >
                    {{ book.title }}
                </h1>
                <p class="mt-2 text-lg text-slate-500">
                    {{ trans('catalog.by', { author: book.author }) }}
                </p>
                <div class="mt-4 flex items-center gap-3">
                    <StarRating :value="book.ratings_avg_rating" :book-id="book.id" editable /><span
                        class="text-sm text-slate-400"
                        >{{ trans('catalog.ratings', { count: book.ratings_count ?? 0 }) }}</span
                    >
                </div>
                <div class="mt-6 flex flex-wrap gap-5 text-sm text-slate-500">
                    <span class="flex items-center gap-2">
                        <Calendar class="h-4 w-4" />{{
                            book.year ?? trans('common.unknown_year')
                        }} </span
                    ><span class="flex items-center gap-2">
                        <Globe2 class="h-4 w-4" />{{ book.language }}
                    </span>
                </div>
                <div class="mt-8 flex flex-wrap gap-3">
                    <Button
                        v-if="book.format !== 'digital'"
                        :disabled="book.available_copies < 1"
                        @click="router.post(route('loans.store', book.id))"
                        >{{ trans('catalog.reserve') }}</Button
                    >
                    <Link v-if="book.format !== 'physical'" :href="route('books.reader', book.id)"
                        ><Button variant="secondary">{{
                            trans('catalog.read_online')
                        }}</Button></Link
                    >
                </div>

                <!-- Tab switcher -->
                <div class="mt-10 flex gap-1 border-b border-slate-200 dark:border-slate-800 mb-6">
                    <button
                        v-for="tab in ['details', 'comments'] as const"
                        :key="tab"
                        @click="activeTab = tab"
                        class="px-4 py-2 text-sm font-medium capitalize transition-colors border-b-2 -mb-px"
                        :class="
                            activeTab === tab
                                ? 'border-brand-500 text-brand-600 dark:text-brand-400'
                                : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400'
                        "
                    >
                        {{ tab === 'details' ? trans('catalog.details') : trans('comments.title') }}
                    </button>
                </div>

                <!-- Details tab -->
                <div v-if="activeTab === 'details'" class="space-y-6">
                    <p class="max-w-3xl leading-8 text-slate-600 dark:text-slate-300">
                        {{ book.description }}
                    </p>
                    <RatingDistribution :distribution="ratingDistribution" />
                </div>

                <!-- Comments tab -->
                <div v-else-if="activeTab === 'comments'">
                    <CommentSection :book-id="book.id" :comments="comments" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
