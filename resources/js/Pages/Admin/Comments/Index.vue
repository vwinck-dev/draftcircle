<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, MessageSquareReply, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import AdminLayout from '@/Components/Layout/AdminLayout.vue';
import Badge from '@/Components/UI/Badge.vue';
import InputError from '@/Components/InputError.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import type { BookComment, Paginated } from '@/types/models';
import { trans } from '@/i18n';
import { confirmAction } from '@/composables/useSweetAlert';

type ModeratedComment = BookComment & {
    book: { id: number; title: string };
    parent?: { id: number; user: { id: number; name: string } } | null;
};

const props = defineProps<{
    comments: Paginated<ModeratedComment>;
    filters: { status: 'all' | 'visible' | 'hidden' };
}>();

const replyingTo = ref<number | null>(null);
const replyForm = useForm({ body: '' });

const changeStatus = (status: 'all' | 'visible' | 'hidden') => {
    router.get(route('admin.comments.index'), { status }, { preserveState: true });
};

const toggleVisibility = (comment: ModeratedComment) => {
    router.patch(route('admin.comments.update', comment.id), {}, { preserveScroll: true });
};

const deleteComment = async (comment: ModeratedComment) => {
    const confirmed = await confirmAction({
        title: trans('confirmations.move_to_trash_title'),
        text: trans('confirmations.move_comment_to_trash'),
        confirmButtonText: trans('confirmations.move_to_trash'),
        cancelButtonText: trans('common.cancel'),
        destructive: true,
    });

    if (confirmed) {
        router.delete(route('admin.comments.destroy', comment.id), { preserveScroll: true });
    }
};

const toggleReply = (comment: ModeratedComment) => {
    replyingTo.value = replyingTo.value === comment.id ? null : comment.id;
    replyForm.reset();
    replyForm.clearErrors();
};

const submitReply = (comment: ModeratedComment) => {
    replyForm.post(route('comments.reply', comment.id), {
        preserveScroll: true,
        onSuccess: () => {
            replyingTo.value = null;
            replyForm.reset();
        },
    });
};
</script>

<template>
    <AdminLayout>
        <Head :title="trans('admin.comments')" />

        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-serif text-3xl font-bold text-ink dark:text-white">
                    {{ trans('admin.comments') }}
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ trans('admin.moderate_comments_text') }}
                </p>
            </div>

            <div class="flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
                <button
                    v-for="status in ['all', 'visible', 'hidden'] as const"
                    :key="status"
                    type="button"
                    class="rounded-lg px-3 py-2 text-sm font-semibold transition"
                    :class="
                        props.filters.status === status
                            ? 'bg-white text-brand-700 shadow-sm dark:bg-slate-900 dark:text-brand-300'
                            : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white'
                    "
                    @click="changeStatus(status)"
                >
                    {{ trans(`comments.filter_${status}`) }}
                </button>
            </div>
        </div>

        <div v-if="comments.data.length" class="space-y-4">
            <article
                v-for="comment in comments.data"
                :key="comment.id"
                class="panel p-5"
                :class="{ 'opacity-70': comment.is_hidden }"
            >
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex min-w-0 gap-3">
                        <UserAvatar
                            :name="comment.user.name"
                            :src="comment.user.avatar_url"
                            size="lg"
                        />
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-slate-800 dark:text-slate-100">
                                    {{ comment.user.name }}
                                </span>
                                <Badge :tone="comment.is_hidden ? 'yellow' : 'green'">
                                    {{
                                        comment.is_hidden
                                            ? trans('comments.hidden')
                                            : trans('comments.visible')
                                    }}
                                </Badge>
                                <Badge v-if="comment.parent_id" tone="blue">
                                    <MessageSquareReply class="mr-1 h-3 w-3" />
                                    {{ trans('comments.reply') }}
                                </Badge>
                            </div>
                            <p v-if="comment.parent" class="mt-1 text-xs text-slate-400">
                                {{
                                    trans('comments.replying_to', {
                                        name: comment.parent.user.name,
                                    })
                                }}
                            </p>
                            <p
                                class="mt-2 whitespace-pre-wrap text-sm text-slate-700 dark:text-slate-300"
                            >
                                {{ comment.body }}
                            </p>
                            <div
                                class="mt-3 flex flex-wrap items-center gap-3 text-xs text-slate-400"
                            >
                                <Link
                                    :href="route('books.show', comment.book.id)"
                                    class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-blue-300"
                                >
                                    {{ comment.book.title }}
                                </Link>
                                <time :datetime="comment.created_at">
                                    {{ new Date(comment.created_at).toLocaleString() }}
                                </time>
                            </div>
                        </div>
                    </div>

                    <div class="flex shrink-0 gap-2">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-50 px-3 py-2 text-xs font-semibold text-brand-700 hover:bg-brand-100 dark:bg-brand-950 dark:text-brand-300"
                            @click="toggleReply(comment)"
                        >
                            <MessageSquareReply class="h-4 w-4" />
                            {{ trans('comments.reply') }}
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            @click="toggleVisibility(comment)"
                        >
                            <Eye v-if="comment.is_hidden" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                            {{
                                comment.is_hidden
                                    ? trans('comments.restore')
                                    : trans('comments.hide')
                            }}
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100 dark:bg-rose-950 dark:text-rose-300"
                            @click="deleteComment(comment)"
                        >
                            <Trash2 class="h-4 w-4" />
                            {{ trans('comments.delete') }}
                        </button>
                    </div>
                </div>

                <form
                    v-if="replyingTo === comment.id"
                    class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800"
                    @submit.prevent="submitReply(comment)"
                >
                    <div class="flex gap-2">
                        <textarea
                            v-model="replyForm.body"
                            :placeholder="trans('comments.reply_placeholder')"
                            :disabled="replyForm.processing"
                            rows="2"
                            class="input flex-1 resize-none text-sm disabled:opacity-50"
                        ></textarea>
                        <button
                            type="submit"
                            :disabled="replyForm.processing || !replyForm.body.trim()"
                            class="self-end rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
                        >
                            {{ trans('comments.submit') }}
                        </button>
                    </div>
                    <InputError :message="replyForm.errors.body" class="mt-1" />
                </form>
            </article>
        </div>

        <p v-else class="panel p-10 text-center text-sm text-slate-500 dark:text-slate-400">
            {{ trans('comments.empty') }}
        </p>

        <Pagination v-if="comments.links.length > 3" class="mt-5" :links="comments.links" />
    </AdminLayout>
</template>
