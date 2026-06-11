<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookPlus, UsersRound } from 'lucide-vue-next';
import { trans } from '@/i18n';
</script>

<template>
    <div class="flex gap-2">
        <Link
            :href="route('admin.books.create')"
            class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white"
        >
            <BookPlus class="h-4 w-4" />{{ trans('admin.new_book') }}
        </Link>
        <Link
            :href="route('admin.users.index')"
            class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-200"
        >
            <UsersRound class="h-4 w-4" />{{ trans('admin.users') }}
        </Link>
    </div>
</template>
