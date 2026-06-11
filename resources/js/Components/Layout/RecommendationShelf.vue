<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Compass, Sparkles } from 'lucide-vue-next';
import BookCard from '@/Components/Books/BookCard.vue';
import type { Book } from '@/types/models';
import { trans } from '@/i18n';

defineProps<{ recommendations: { personalized: boolean; books: Book[] } }>();
</script>

<template>
    <section
        v-if="recommendations.books.length"
        class="mt-16 border-y border-blue-100 bg-gradient-to-b from-blue-50/80 to-white py-14 dark:border-slate-800 dark:from-brand-900/20 dark:to-slate-950"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p
                        class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-brand-600 dark:text-blue-300"
                    >
                        <Sparkles v-if="recommendations.personalized" class="h-4 w-4" />
                        <Compass v-else class="h-4 w-4" />
                        {{
                            trans(
                                recommendations.personalized
                                    ? 'recommendations.personalized_eyebrow'
                                    : 'recommendations.popular_eyebrow',
                            )
                        }}
                    </p>
                    <h2 class="mt-2 font-serif text-2xl font-bold text-ink dark:text-white">
                        {{
                            trans(
                                recommendations.personalized
                                    ? 'recommendations.personalized_title'
                                    : 'recommendations.popular_title',
                            )
                        }}
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        {{
                            trans(
                                recommendations.personalized
                                    ? 'recommendations.personalized_description'
                                    : 'recommendations.popular_description',
                            )
                        }}
                    </p>
                </div>
                <Link
                    :href="route('books.index')"
                    class="group inline-flex items-center gap-2 text-sm font-bold text-brand-600"
                >
                    {{ trans('recommendations.explore') }}
                    <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                </Link>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <BookCard v-for="book in recommendations.books" :key="book.id" :book="book" />
            </div>
        </div>
    </section>
</template>
