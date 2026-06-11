<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BarChart3,
    Bookmark,
    BookOpen,
    LayoutDashboard,
    MessageSquareText,
    Settings,
    Trash2,
    UsersRound,
} from 'lucide-vue-next';
import { trans } from '@/i18n';

const links = [
    { label: 'nav.dashboard', route: 'admin.dashboard', icon: LayoutDashboard },
    { label: 'nav.books', route: 'admin.books.index', icon: BookOpen },
    { label: 'nav.categories', route: 'admin.categories.index', icon: Bookmark },
    { label: 'nav.users', route: 'admin.users.index', icon: UsersRound },
    { label: 'admin.all_loans', route: 'admin.loans.index', icon: BarChart3 },
    { label: 'admin.comments', route: 'admin.comments.index', icon: MessageSquareText },
    { label: 'admin.trash', route: 'admin.trash.index', icon: Trash2 },
    { label: 'nav.settings', route: 'admin.settings.index', icon: Settings },
];
</script>

<template>
    <aside class="panel hidden h-fit p-3 lg:block">
        <p class="px-3 py-2 text-xs font-bold uppercase tracking-widest text-slate-400">
            {{ trans('nav.admin') }}
        </p>
        <Link
            v-for="link in links"
            :key="link.route"
            :href="route(link.route)"
            class="mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition duration-200 hover:translate-x-1"
            :class="
                route().current(link.route)
                    ? 'bg-brand-600 text-white'
                    : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800'
            "
        >
            <component :is="link.icon" class="h-4 w-4" />
            {{ trans(link.label) }}
        </Link>
    </aside>
</template>
