<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Components/Layout/AdminLayout.vue';
import BookForm from '@/Components/Admin/BookForm.vue';
import type { Category } from '@/types/models';
import { trans } from '@/i18n';
defineProps<{ categories: Category[] }>();
</script>
<template>
    <AdminLayout>
        <Head :title="trans('admin.add_book')" />
        <h1 class="mb-6 font-serif text-3xl font-bold text-ink dark:text-white">
            {{ trans('admin.add_book') }}
        </h1>
        <BookForm :categories="categories" />
    </AdminLayout>
</template>
