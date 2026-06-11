<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { RotateCcw, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import AdminLayout from '@/Components/Layout/AdminLayout.vue';
import DetailsModal from '@/Components/Admin/DetailsModal.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import ResourceTable from '@/Components/Admin/ResourceTable.vue';
import type { BookFormat, Paginated } from '@/types/models';
import { trans } from '@/i18n';
import { confirmAction } from '@/composables/useSweetAlert';

type TrashBook = {
    id: number;
    title: string;
    author: string;
    format: BookFormat;
    deleted_at: string | null;
};

type TrashComment = {
    id: number;
    body: string;
    user_name: string;
    book_title: string;
    deleted_at: string | null;
};

defineProps<{
    books: Paginated<TrashBook>;
    comments: Paginated<TrashComment>;
}>();

const restoreBook = (book: TrashBook) => {
    router.patch(route('admin.trash.books.restore', book.id), {}, { preserveScroll: true });
};

const deleteBookPermanently = async (book: TrashBook) => {
    const confirmed = await confirmAction({
        title: trans('confirmations.delete_permanently_title'),
        text: trans('confirmations.delete_book_permanently', { title: book.title }),
        confirmButtonText: trans('confirmations.delete_permanently'),
        cancelButtonText: trans('common.cancel'),
        destructive: true,
    });

    if (confirmed) {
        router.delete(route('admin.trash.books.destroy', book.id), { preserveScroll: true });
    }
};

const restoreComment = (comment: TrashComment) => {
    router.patch(route('admin.trash.comments.restore', comment.id), {}, { preserveScroll: true });
};

const deleteCommentPermanently = async (comment: TrashComment) => {
    const confirmed = await confirmAction({
        title: trans('confirmations.delete_permanently_title'),
        text: trans('confirmations.delete_comment_permanently'),
        confirmButtonText: trans('confirmations.delete_permanently'),
        cancelButtonText: trans('common.cancel'),
        destructive: true,
    });

    if (confirmed) {
        router.delete(route('admin.trash.comments.destroy', comment.id), {
            preserveScroll: true,
        });
    }
};

const formattedDate = (date: string | null) => (date ? new Date(date).toLocaleString() : '—');
const detailsOpen = ref(false);
const detailsTitle = ref('');
const details = ref<Array<{ label: string; value: string | number | null | undefined }>>([]);
const showBook = (book: TrashBook) => {
    detailsTitle.value = trans('admin.book_details');
    details.value = [
        { label: trans('admin.form.title'), value: book.title },
        { label: trans('admin.form.author'), value: book.author },
        { label: trans('admin.form.format'), value: trans(`catalog.format.${book.format}`) },
        { label: trans('admin.columns.deleted_at'), value: formattedDate(book.deleted_at) },
    ];
    detailsOpen.value = true;
};
const showComment = (comment: TrashComment) => {
    detailsTitle.value = trans('admin.comment_details');
    details.value = [
        { label: trans('admin.columns.user'), value: comment.user_name },
        { label: trans('admin.columns.book'), value: comment.book_title },
        { label: trans('comments.title'), value: comment.body },
        { label: trans('admin.columns.deleted_at'), value: formattedDate(comment.deleted_at) },
    ];
    detailsOpen.value = true;
};
</script>

<template>
    <AdminLayout>
        <Head :title="trans('admin.trash')" />

        <div class="mb-6">
            <h1 class="font-serif text-3xl font-bold text-ink dark:text-white">
                {{ trans('admin.trash') }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ trans('admin.trash_text') }}
            </p>
        </div>

        <section>
            <h2 class="mb-3 font-serif text-xl font-bold text-ink dark:text-white">
                {{ trans('admin.trash_books') }}
            </h2>
            <ResourceTable
                v-if="books.data.length"
                :columns="[
                    trans('admin.columns.title'),
                    trans('admin.columns.format'),
                    trans('admin.columns.deleted_at'),
                    trans('common.actions'),
                ]"
            >
                <tr
                    v-for="book in books.data"
                    :key="book.id"
                    class="cursor-pointer"
                    @click="showBook(book)"
                >
                    <td class="px-5 py-4">
                        <p class="font-semibold">{{ book.title }}</p>
                        <p class="text-xs text-slate-400">{{ book.author }}</p>
                    </td>
                    <td class="px-5 py-4">{{ trans(`catalog.format.${book.format}`) }}</td>
                    <td class="px-5 py-4">{{ formattedDate(book.deleted_at) }}</td>
                    <td class="px-5 py-4">
                        <div class="flex flex-wrap gap-3">
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 font-semibold text-brand-600"
                                @click.stop="restoreBook(book)"
                            >
                                <RotateCcw class="h-4 w-4" />{{ trans('common.restore') }}
                            </button>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 font-semibold text-rose-600"
                                @click.stop="deleteBookPermanently(book)"
                            >
                                <Trash2 class="h-4 w-4" />{{ trans('common.delete_permanently') }}
                            </button>
                        </div>
                    </td>
                </tr>
            </ResourceTable>
            <p v-else class="panel p-8 text-center text-sm text-slate-500">
                {{ trans('admin.trash_empty_books') }}
            </p>
            <Pagination v-if="books.links.length > 3" class="mt-4" :links="books.links" />
        </section>

        <section class="mt-10">
            <h2 class="mb-3 font-serif text-xl font-bold text-ink dark:text-white">
                {{ trans('admin.trash_comments') }}
            </h2>
            <ResourceTable
                v-if="comments.data.length"
                :columns="[
                    trans('admin.columns.user'),
                    trans('admin.columns.book'),
                    trans('comments.title'),
                    trans('admin.columns.deleted_at'),
                    trans('common.actions'),
                ]"
            >
                <tr
                    v-for="comment in comments.data"
                    :key="comment.id"
                    class="cursor-pointer"
                    @click="showComment(comment)"
                >
                    <td class="px-5 py-4 font-semibold">{{ comment.user_name }}</td>
                    <td class="px-5 py-4">{{ comment.book_title }}</td>
                    <td class="max-w-sm px-5 py-4">
                        <p class="line-clamp-3">{{ comment.body }}</p>
                    </td>
                    <td class="px-5 py-4">{{ formattedDate(comment.deleted_at) }}</td>
                    <td class="px-5 py-4">
                        <div class="flex flex-wrap gap-3">
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 font-semibold text-brand-600"
                                @click.stop="restoreComment(comment)"
                            >
                                <RotateCcw class="h-4 w-4" />{{ trans('common.restore') }}
                            </button>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 font-semibold text-rose-600"
                                @click.stop="deleteCommentPermanently(comment)"
                            >
                                <Trash2 class="h-4 w-4" />{{ trans('common.delete_permanently') }}
                            </button>
                        </div>
                    </td>
                </tr>
            </ResourceTable>
            <p v-else class="panel p-8 text-center text-sm text-slate-500">
                {{ trans('admin.trash_empty_comments') }}
            </p>
            <Pagination v-if="comments.links.length > 3" class="mt-4" :links="comments.links" />
        </section>
        <DetailsModal
            :open="detailsOpen"
            :title="detailsTitle"
            :items="details"
            @close="detailsOpen = false"
        />
    </AdminLayout>
</template>
