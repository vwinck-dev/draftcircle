<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Components/Layout/AdminLayout.vue';
import BookForm from '@/Components/Admin/BookForm.vue';
import type { Book, Category } from '@/types/models';
import { trans } from '@/i18n';
defineProps<{ book: Book; categories: Category[] }>();
</script>
<template>
    <AdminLayout>
        <Head :title="`${trans('admin.edit_book')}: ${book.title}`" />
        <h1 class="mb-6 font-serif text-3xl font-bold text-ink dark:text-white">
            {{ trans('admin.edit_book') }}
        </h1>
        <BookForm :book="book" :categories="categories" />
    </AdminLayout>
</template>
