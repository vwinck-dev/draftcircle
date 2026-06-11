<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import BookCard from '@/Components/Books/BookCard.vue';
import BookFilters from '@/Components/Books/BookFilters.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import type { Book, Paginated } from '@/types/models';
import { trans } from '@/i18n';
defineProps<{ books: Paginated<Book> }>();
</script>
<template>
    <AppLayout>
        <Head :title="trans('catalog.title')" />
        <div class="mb-6">
            <h1 class="font-serif text-3xl font-bold text-ink dark:text-white">
                {{ trans('catalog.explore') }}
            </h1>
            <p class="mt-2 text-slate-500">{{ trans('catalog.description') }}</p>
        </div>
        <BookFilters />
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <BookCard v-for="book in books.data" :key="book.id" :book="book" />
        </div>
        <p v-if="!books.data.length" class="panel mt-6 p-10 text-center text-slate-500">
            {{ trans('catalog.empty') }}
        </p>
        <Pagination class="mt-7" :links="books.links" />
    </AppLayout>
</template>
