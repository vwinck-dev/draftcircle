<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp, MessageSquare } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import { confirmAction } from '@/composables/useSweetAlert';
import { trans } from '@/i18n';
import type { BookComment } from '@/types/models';

const COLLAPSE_THRESHOLD = 2;

const props = defineProps<{
    comment: BookComment;
    bookId: number;
}>();

const page = usePage();
const authUser = page.props.auth?.user;
const showReplyForm = ref(false);
const repliesExpanded = ref(props.comment.replies.length <= COLLAPSE_THRESHOLD);
const replyForm = useForm({ body: '' });
const voteForm = useForm({ value: 1 });
const deleteForm = useForm({});

const hasCollapsibleReplies = computed(() => props.comment.replies.length > COLLAPSE_THRESHOLD);

const relativeTime = (dateStr: string): string => {
    const diff = Date.now() - new Date(dateStr).getTime();
    const minutes = Math.floor(diff / 60000);
    if (minutes < 1) return 'agora';
    if (minutes < 60) return `${minutes}min`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h`;
    const days = Math.floor(hours / 24);
    if (days < 30) return `${days}d`;
    const months = Math.floor(days / 30);
    if (months < 12) return `${months}m`;
    return `${Math.floor(months / 12)}a`;
};

const submitReply = () => {
    replyForm.post(route('comments.reply', props.comment.id), {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
            showReplyForm.value = false;
        },
    });
};

const vote = (value: -1 | 1) => {
    voteForm.value = value;
    voteForm.put(route('comments.vote', props.comment.id), {
        preserveScroll: true,
        only: ['comments'],
    });
};

const deleteComment = async (id: number) => {
    const confirmed = await confirmAction({
        title: trans('confirmations.move_to_trash_title'),
        text: trans('confirmations.move_comment_to_trash'),
        confirmButtonText: trans('confirmations.move_to_trash'),
        cancelButtonText: trans('common.cancel'),
        destructive: true,
    });
    if (confirmed) {
        deleteForm.delete(route('comments.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <article class="flex min-w-0 gap-2">
        <!-- Vote column -->
        <div class="flex shrink-0 flex-col items-center pt-0.5">
            <button
                type="button"
                :aria-label="trans('comments.upvote')"
                :disabled="voteForm.processing"
                class="rounded p-0.5 transition-colors disabled:opacity-50"
                :class="
                    comment.user_vote === 1
                        ? 'text-orange-500 dark:text-orange-400'
                        : 'text-slate-400 hover:text-orange-500 dark:hover:text-orange-400'
                "
                @click="vote(1)"
            >
                <ChevronUp class="h-5 w-5" />
            </button>
            <span
                class="text-xs font-bold tabular-nums"
                :class="{
                    'text-orange-500 dark:text-orange-400': comment.user_vote === 1,
                    'text-blue-500 dark:text-blue-400': comment.user_vote === -1,
                    'text-slate-600 dark:text-slate-300': comment.user_vote === null,
                }"
            >
                {{ comment.score }}
            </span>
            <button
                type="button"
                :aria-label="trans('comments.downvote')"
                :disabled="voteForm.processing"
                class="rounded p-0.5 transition-colors disabled:opacity-50"
                :class="
                    comment.user_vote === -1
                        ? 'text-blue-500 dark:text-blue-400'
                        : 'text-slate-400 hover:text-blue-500 dark:hover:text-blue-400'
                "
                @click="vote(-1)"
            >
                <ChevronDown class="h-5 w-5" />
            </button>
        </div>

        <!-- Content -->
        <div class="min-w-0 flex-1">
            <!-- Header: avatar + name + timestamp -->
            <div class="flex min-w-0 items-center gap-1.5">
                <UserAvatar :name="comment.user.name" :src="comment.user.avatar_url" size="sm" />
                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                    {{ comment.user.name }}
                </span>
                <span class="text-xs text-slate-400">·</span>
                <span
                    class="text-xs text-slate-400"
                    :title="new Date(comment.created_at).toLocaleString()"
                >
                    {{ relativeTime(comment.created_at) }}
                </span>
            </div>

            <!-- Body -->
            <p
                class="mt-1.5 whitespace-pre-wrap break-words text-sm leading-relaxed text-slate-700 dark:text-slate-300"
            >
                {{ comment.body }}
            </p>

            <!-- Action bar -->
            <div class="mt-2 flex items-center gap-4">
                <button
                    v-if="authUser"
                    type="button"
                    class="flex items-center gap-1 text-xs font-semibold text-slate-500 transition-colors hover:text-brand-600 dark:text-slate-400 dark:hover:text-brand-400"
                    @click="showReplyForm = !showReplyForm"
                >
                    <MessageSquare class="h-3.5 w-3.5" />
                    {{ trans('comments.reply') }}
                </button>
                <button
                    v-if="authUser && authUser.id === comment.user_id"
                    type="button"
                    :disabled="deleteForm.processing"
                    class="text-xs font-semibold text-slate-400 transition-colors hover:text-red-500 disabled:opacity-50 dark:hover:text-red-400"
                    @click="deleteComment(comment.id)"
                >
                    {{ trans('comments.delete') }}
                </button>
            </div>

            <!-- Reply form -->
            <form v-if="showReplyForm" class="mt-3" @submit.prevent="submitReply">
                <div class="flex gap-2">
                    <textarea
                        v-model="replyForm.body"
                        :placeholder="trans('comments.reply_placeholder')"
                        :disabled="replyForm.processing"
                        rows="2"
                        class="input min-w-0 flex-1 resize-none text-sm disabled:opacity-50"
                    ></textarea>
                    <button
                        type="submit"
                        :disabled="replyForm.processing || !replyForm.body.trim()"
                        class="self-end rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
                    >
                        {{ trans('comments.submit') }}
                    </button>
                </div>
                <InputError :message="replyForm.errors.body" class="mt-1" />
            </form>

            <!-- Replies section -->
            <div v-if="comment.replies.length" class="mt-3">
                <!-- Expand button (YouTube-style, only when > threshold replies and collapsed) -->
                <button
                    v-if="hasCollapsibleReplies && !repliesExpanded"
                    type="button"
                    class="flex items-center gap-1.5 text-sm font-semibold text-brand-600 transition-colors hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300"
                    @click="repliesExpanded = true"
                >
                    <ChevronDown class="h-4 w-4" />
                    Ver {{ comment.replies.length }} respostas
                </button>

                <!-- Replies list (always visible if ≤ threshold; toggled if > threshold) -->
                <div
                    v-if="repliesExpanded"
                    class="space-y-4 border-l-2 border-slate-200 pl-3 dark:border-slate-700"
                >
                    <CommentCard
                        v-for="reply in comment.replies"
                        :key="reply.id"
                        :comment="reply"
                        :book-id="bookId"
                    />

                    <!-- Collapse button at bottom of replies (YouTube-style) -->
                    <button
                        v-if="hasCollapsibleReplies"
                        type="button"
                        class="flex items-center gap-1.5 text-sm font-semibold text-slate-400 transition-colors hover:text-slate-600 dark:hover:text-slate-300"
                        @click="repliesExpanded = false"
                    >
                        <ChevronUp class="h-4 w-4" />
                        Ocultar respostas
                    </button>
                </div>
            </div>
        </div>
    </article>
</template>
