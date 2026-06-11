<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import AdminLayout from '@/Components/Layout/AdminLayout.vue';
import DetailsModal from '@/Components/Admin/DetailsModal.vue';
import ResourceTable from '@/Components/Admin/ResourceTable.vue';
import Badge from '@/Components/UI/Badge.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import type { Book, Paginated } from '@/types/models';
import { trans } from '@/i18n';
import { confirmAction } from '@/composables/useSweetAlert';
defineProps<{ books: Paginated<Book> }>();
const selected = ref<Book | null>(null);
const detailItems = (book: Book) => [
    { label: trans('admin.form.title'), value: book.title },
    { label: trans('admin.form.author'), value: book.author },
    { label: trans('admin.form.isbn'), value: book.isbn },
    { label: trans('admin.form.publisher'), value: book.publisher },
    { label: trans('admin.form.format'), value: trans(`catalog.format.${book.format}`) },
    { label: trans('admin.form.copies'), value: `${book.available_copies} / ${book.total_copies}` },
    { label: trans('admin.form.language'), value: book.language },
    {
        label: trans('admin.columns.status'),
        value: book.is_active ? trans('common.active') : trans('common.inactive'),
    },
];

const deleteBook = async (book: Book) => {
    const confirmed = await confirmAction({
        title: trans('confirmations.move_to_trash_title'),
        text: trans('confirmations.move_book_to_trash', { title: book.title }),
        confirmButtonText: trans('confirmations.move_to_trash'),
        cancelButtonText: trans('common.cancel'),
        destructive: true,
    });

    if (confirmed) {
        router.delete(route('admin.books.destroy', book.id), { preserveScroll: true });
    }
};
</script>
<template>
    <AdminLayout>
        <Head :title="trans('admin.manage_books')" />
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="font-serif text-3xl font-bold text-ink dark:text-white">
                    {{ trans('admin.books') }}
                </h1>
                <p class="mt-1 text-sm text-slate-500">{{ trans('admin.manage_books_text') }}</p>
            </div>
            <div class="flex gap-2">
                <Link
                    :href="route('admin.trash.index')"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-200"
                >
                    <Trash2 class="h-4 w-4" />{{ trans('admin.trash') }}
                </Link>
                <Link
                    :href="route('admin.books.create')"
                    class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white"
                    >{{ trans('admin.add_book') }}
                </Link>
            </div>
        </div>
        <template v-if="books.data.length">
            <ResourceTable
                :columns="[
                    trans('admin.columns.title'),
                    trans('admin.columns.format'),
                    trans('admin.columns.copies'),
                    trans('admin.columns.status'),
                    trans('common.actions'),
                ]"
            >
                <tr
                    v-for="book in books.data"
                    :key="book.id"
                    class="cursor-pointer"
                    @click="selected = book"
                >
                    <td class="px-5 py-4">
                        <p class="font-semibold">{{ book.title }}</p>
                        <p class="text-xs text-slate-400">{{ book.author }}</p>
                    </td>
                    <td class="px-5 py-4">{{ trans(`catalog.format.${book.format}`) }}</td>
                    <td class="px-5 py-4">{{ book.available_copies }} / {{ book.total_copies }}</td>
                    <td class="px-5 py-4">
                        <Badge :tone="book.is_active ? 'green' : 'gray'">{{
                            book.is_active ? trans('common.active') : trans('common.inactive')
                        }}</Badge>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex gap-3">
                            <Link
                                :href="route('admin.books.edit', book.id)"
                                class="font-semibold text-brand-600"
                                @click.stop
                                >{{ trans('common.edit') }}</Link
                            ><button
                                type="button"
                                class="font-semibold text-rose-600"
                                @click.stop="deleteBook(book)"
                            >
                                {{ trans('common.delete') }}
                            </button>
                        </div>
                    </td>
                </tr>
            </ResourceTable>
            <Pagination class="mt-5" :links="books.links" />
        </template>
        <p v-else class="panel p-10 text-center text-sm text-slate-500 dark:text-slate-400">
            {{ trans('admin.no_books_admin') }}
        </p>
        <DetailsModal
            :open="selected !== null"
            :title="trans('admin.book_details')"
            :items="selected ? detailItems(selected) : []"
            @close="selected = null"
        />
    </AdminLayout>
</template>
