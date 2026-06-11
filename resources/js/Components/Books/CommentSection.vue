<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import { trans } from '@/i18n';
import CommentCard from './CommentCard.vue';
import type { BookComment } from '@/types/models';

const props = defineProps<{
    bookId: number;
    comments: BookComment[];
}>();

const page = usePage();
const authUser = page.props.auth?.user;

const form = useForm({ body: '' });

const submit = () => {
    form.post(route('comments.store', props.bookId), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <div class="space-y-6">
        <!-- New comment form -->
        <form v-if="authUser" class="flex gap-3" @submit.prevent="submit">
            <UserAvatar :name="authUser.name" :src="authUser.avatar_url" class="shrink-0" />
            <div class="min-w-0 flex-1 space-y-2">
                <textarea
                    v-model="form.body"
                    :placeholder="trans('comments.write_placeholder')"
                    :disabled="form.processing"
                    rows="3"
                    class="input w-full resize-none text-sm disabled:opacity-50"
                ></textarea>
                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing || !form.body.trim()"
                        class="rounded-full bg-brand-600 px-4 py-1.5 text-sm font-semibold text-white transition-colors hover:bg-brand-700 disabled:opacity-50"
                    >
                        {{ trans('comments.submit') }}
                    </button>
                </div>
                <InputError :message="form.errors.body" />
            </div>
        </form>
        <p v-else class="text-sm text-slate-500 dark:text-slate-400">
            {{ trans('comments.login_to_comment') }}
        </p>

        <!-- Divider -->
        <div v-if="comments.length" class="border-t border-slate-100 dark:border-slate-800" />

        <!-- Comment list -->
        <div v-if="comments.length" class="space-y-6">
            <CommentCard
                v-for="comment in comments"
                :key="comment.id"
                :comment="comment"
                :book-id="bookId"
            />
        </div>
        <p v-else class="text-sm text-slate-400">
            {{ trans('comments.no_comments') }}
        </p>
    </div>
</template>
