<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Components/Layout/AdminLayout.vue';
import QuickActions from '@/Components/Admin/QuickActions.vue';
import ReportChart from '@/Components/Admin/ReportChart.vue';
import StatCard from '@/Components/UI/StatCard.vue';
import Badge from '@/Components/UI/Badge.vue';
import { Link } from '@inertiajs/vue3';
import { Activity, BookCheck, ClockAlert, Sparkles, TrendingUp, UserPlus } from 'lucide-vue-next';
import { formatCurrency, locale, trans } from '@/i18n';
import { computed } from 'vue';
const props = defineProps<{
    summary: {
        books: number;
        users: number;
        activeLoans: number;
        overdueLoans: number;
        pendingReservations: number;
        returnedThisMonth: number;
        newUsersThisMonth: number;
        availableCopies: number;
        outstandingFines: number;
        popularBooks: Array<{ id: number; title: string; author: string; loan_count: number }>;
        recentLoans: Array<{
            id: number;
            book: string | null;
            user: string | null;
            status: string;
            reserved_at: string | null;
        }>;
    };
}>();
const chart = computed(() => ({
    [trans('admin.books')]: props.summary.books,
    [trans('admin.users')]: props.summary.users,
    [trans('admin.active_loans')]: props.summary.activeLoans,
    [trans('admin.reservations')]: props.summary.pendingReservations,
    [trans('admin.returned_this_month')]: props.summary.returnedThisMonth,
}));
const date = (value: string | null) =>
    value ? new Date(value).toLocaleDateString(locale().replace('_', '-')) : '—';
</script>
<template>
    <AdminLayout>
        <Head :title="trans('admin.dashboard')" />
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-widest text-brand-600">
                    {{ trans('admin.overview') }}
                </p>
                <h1 class="mt-2 font-serif text-3xl font-bold text-ink dark:text-white">
                    {{ trans('admin.dashboard') }}
                </h1>
            </div>
            <QuickActions />
        </div>
        <section
            class="mt-6 overflow-hidden rounded-3xl bg-gradient-to-br from-brand-900 via-brand-700 to-cyan-600 p-6 text-white shadow-soft"
        >
            <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
                <div>
                    <div class="flex items-center gap-2 text-sm font-semibold text-blue-100">
                        <Sparkles class="h-4 w-4" />{{ trans('admin.library_pulse') }}
                    </div>
                    <p class="mt-3 max-w-2xl font-serif text-2xl font-bold text-white">
                        {{ trans('admin.library_pulse_text', { count: summary.activeLoans }) }}
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="rounded-2xl bg-white/10 px-5 py-3 backdrop-blur">
                        <p class="text-2xl font-bold">{{ summary.returnedThisMonth }}</p>
                        <p class="text-xs text-blue-100">
                            {{ trans('admin.returned_this_month') }}
                        </p>
                    </div>
                    <div class="rounded-2xl bg-white/10 px-5 py-3 backdrop-blur">
                        <p class="text-2xl font-bold">{{ summary.newUsersThisMonth }}</p>
                        <p class="text-xs text-blue-100">
                            {{ trans('admin.new_users_this_month') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <StatCard
                :href="route('admin.books.index')"
                :label="trans('admin.books')"
                :value="summary.books"
            />
            <StatCard
                :href="route('admin.users.index')"
                :label="trans('admin.users')"
                :value="summary.users"
            />
            <StatCard
                :href="route('admin.loans.index')"
                :label="trans('admin.active_loans')"
                :value="summary.activeLoans"
            />
            <StatCard
                :href="route('admin.loans.index')"
                :label="trans('admin.reservations')"
                :value="summary.pendingReservations"
            />
            <StatCard
                :label="trans('admin.outstanding_fines')"
                :value="formatCurrency(summary.outstandingFines)"
            />
        </div>
        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            <Link
                :href="route('admin.loans.index')"
                class="panel group flex cursor-pointer items-center gap-4 p-5 transition-all duration-200 hover:-translate-y-0.5 hover:border-rose-300 hover:shadow-md dark:hover:border-rose-800"
            >
                <ClockAlert class="h-7 w-7 text-rose-500" />
                <div>
                    <p class="text-2xl font-bold text-ink dark:text-white">
                        {{ summary.overdueLoans }}
                    </p>
                    <p class="text-sm text-slate-500 transition-colors group-hover:text-rose-600">
                        {{ trans('admin.overdue_loans') }}
                    </p>
                </div>
            </Link>
            <Link
                :href="route('admin.books.index')"
                class="panel group flex cursor-pointer items-center gap-4 p-5 transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md dark:hover:border-emerald-800"
            >
                <BookCheck class="h-7 w-7 text-emerald-500" />
                <div>
                    <p class="text-2xl font-bold text-ink dark:text-white">
                        {{ summary.availableCopies }}
                    </p>
                    <p
                        class="text-sm text-slate-500 transition-colors group-hover:text-emerald-600"
                    >
                        {{ trans('admin.available_copies') }}
                    </p>
                </div>
            </Link>
            <Link
                :href="route('admin.users.index')"
                class="panel group flex cursor-pointer items-center gap-4 p-5 transition-all duration-200 hover:-translate-y-0.5 hover:border-cyan-300 hover:shadow-md dark:hover:border-cyan-800"
            >
                <UserPlus class="h-7 w-7 text-cyan-500" />
                <div>
                    <p class="text-2xl font-bold text-ink dark:text-white">
                        {{ summary.newUsersThisMonth }}
                    </p>
                    <p class="text-sm text-slate-500 transition-colors group-hover:text-cyan-600">
                        {{ trans('admin.new_users_this_month') }}
                    </p>
                </div>
            </Link>
        </div>
        <div class="mt-6 grid gap-6 xl:grid-cols-[1.3fr_1fr]">
            <div>
                <div class="mb-3 flex items-center gap-2">
                    <TrendingUp class="h-5 w-5 text-brand-600" />
                    <h2 class="font-serif text-xl font-bold text-ink dark:text-white">
                        {{ trans('admin.metrics') }}
                    </h2>
                </div>
                <ReportChart :values="chart" />
            </div>
            <section>
                <div class="mb-3 flex items-center gap-2">
                    <Sparkles class="h-5 w-5 text-amber-500" />
                    <h2 class="font-serif text-xl font-bold text-ink dark:text-white">
                        {{ trans('admin.popular_books') }}
                    </h2>
                </div>
                <div class="panel divide-y divide-slate-100 overflow-hidden dark:divide-slate-800">
                    <Link
                        v-for="(book, index) in summary.popularBooks"
                        :key="book.id"
                        :href="route('admin.books.edit', book.id)"
                        class="group flex items-center gap-3 p-4 transition-colors hover:bg-brand-50/60 dark:hover:bg-brand-900/10"
                    >
                        <span
                            class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand-50 text-xs font-bold text-brand-600 dark:bg-brand-900/30 dark:text-blue-300"
                            >{{ index + 1 }}</span
                        >
                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate text-sm font-semibold transition-colors group-hover:text-brand-600 dark:group-hover:text-blue-300"
                            >
                                {{ book.title }}
                            </p>
                            <p class="truncate text-xs text-slate-400">{{ book.author }}</p>
                        </div>
                        <span class="text-xs font-semibold text-slate-500">{{
                            trans('admin.loan_count', { count: book.loan_count })
                        }}</span>
                    </Link>
                </div>
            </section>
        </div>
        <section class="mt-6">
            <div class="mb-3 flex items-center gap-2">
                <Activity class="h-5 w-5 text-brand-600" />
                <h2 class="font-serif text-xl font-bold text-ink dark:text-white">
                    {{ trans('admin.recent_activity') }}
                </h2>
            </div>
            <div
                v-if="summary.recentLoans.length"
                class="panel grid divide-y divide-slate-100 overflow-hidden dark:divide-slate-800 md:grid-cols-2 md:divide-x md:divide-y-0"
            >
                <Link
                    v-for="loan in summary.recentLoans"
                    :key="loan.id"
                    :href="route('admin.loans.index')"
                    class="group flex items-center gap-3 p-4 transition-colors hover:bg-brand-50/60 dark:hover:bg-brand-900/10"
                >
                    <div class="min-w-0 flex-1">
                        <p
                            class="truncate text-sm font-semibold transition-colors group-hover:text-brand-600 dark:group-hover:text-blue-300"
                        >
                            {{ loan.book ?? '—' }}
                        </p>
                        <p class="truncate text-xs text-slate-400">
                            {{ loan.user ?? '—' }} · {{ date(loan.reserved_at) }}
                        </p>
                    </div>
                    <Badge>{{ trans(`loans.status.${loan.status}`) }}</Badge>
                </Link>
            </div>
            <p v-else class="panel p-6 text-sm text-slate-400">
                {{ trans('admin.no_recent_activity') }}
            </p>
        </section>
    </AdminLayout>
</template>
