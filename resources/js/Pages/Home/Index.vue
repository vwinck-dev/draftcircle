<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowRight } from 'lucide-vue-next';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import BookCard from '@/Components/Books/BookCard.vue';
import type { Book, Category } from '@/types/models';
import type { PageProps } from '@/types';
import { trans } from '@/i18n';
import { trackCategoryInterest } from '@/composables/useRecommendationCookies';
defineProps<{ categories: Category[]; featured: Book[]; newArrivals: Book[] }>();
const page = usePage<PageProps>();
const user = page.props.auth.user!;

const categoryLabel = (cat: Category): string => {
    const key = `categories.${cat.slug}`;
    const translated = trans(key);
    return translated !== key ? translated : cat.name;
};

const greeting = computed(() =>
    new Date().getHours() < 12
        ? trans('home.morning')
        : new Date().getHours() < 18
          ? trans('home.afternoon')
          : trans('home.evening'),
);
</script>
<template>
    <AppLayout>
        <Head :title="trans('home.title')" />
        <section
            class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-900 via-brand-600 to-cyan-500 p-7 text-white shadow-soft sm:p-10"
        >
            <div
                class="hero-orbit absolute -right-16 -top-20 h-72 w-72 rounded-full border-[40px] border-white/10"
            />
            <div class="relative max-w-2xl">
                <p class="text-sm font-semibold text-blue-100">
                    {{ greeting }},
                    {{ user.name }}
                </p>
                <h1
                    class="mt-3 font-serif text-3xl font-bold tracking-tight text-white sm:text-5xl"
                >
                    {{ trans('home.hero_title') }}
                </h1>
                <p class="mt-4 text-blue-100">{{ trans('home.hero_text') }}</p>
                <Link
                    :href="route('books.index')"
                    class="group interactive-lift mt-6 inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-brand-600 shadow-lg shadow-brand-950/15 hover:shadow-xl"
                >
                    {{ trans('home.explore') }}
                    <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                </Link>
            </div>
        </section>

        <section v-if="categories.length" class="mt-9">
            <h2 class="mb-4 font-serif text-xl font-bold text-ink dark:text-white">
                {{ trans('home.subjects') }}
            </h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
                <Link
                    v-for="category in categories"
                    :key="category.id"
                    :href="route('books.index', { category: category.id })"
                    class="panel interactive-lift flex flex-col items-center gap-2 p-4 text-center text-sm font-semibold hover:text-brand-600 dark:hover:text-blue-300"
                    @click.capture="trackCategoryInterest(category.id)"
                >
                    {{ categoryLabel(category) }}
                    <span class="text-xs font-normal text-slate-400">{{
                        trans('home.books_count', { count: category.books_count ?? 0 })
                    }}</span>
                </Link>
            </div>
        </section>

        <section class="mt-9">
            <h2 class="mb-5 font-serif text-xl font-bold text-ink dark:text-white">
                {{ trans('home.featured') }}
            </h2>
            <div v-if="featured.length" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <BookCard v-for="book in featured" :key="book.id" :book="book" />
            </div>
            <p v-else class="panel p-8 text-center text-sm text-slate-400">
                {{ trans('home.no_books') }}
            </p>
        </section>

        <section class="mt-9">
            <h2 class="mb-5 font-serif text-xl font-bold text-ink dark:text-white">
                {{ trans('home.new_arrivals') }}
            </h2>
            <div v-if="newArrivals.length" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <BookCard v-for="book in newArrivals" :key="book.id" :book="book" />
            </div>
            <p v-else class="panel p-8 text-center text-sm text-slate-400">
                {{ trans('home.no_books') }}
            </p>
        </section>
    </AppLayout>
</template>
