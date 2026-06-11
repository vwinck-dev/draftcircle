<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Components/Layout/AdminLayout.vue';
import DetailsModal from '@/Components/Admin/DetailsModal.vue';
import ResourceTable from '@/Components/Admin/ResourceTable.vue';
import Badge from '@/Components/UI/Badge.vue';
import type { Loan } from '@/types/models';
import { trans, locale, formatCurrency } from '@/i18n';

type OverdueLoan = {
    id: number;
    user: { id: number; name: string; email: string } | null;
    book_title: string;
    due_at: string | null;
    days_overdue: number;
    fine_amount: string | null;
};

defineProps<{
    loans: Loan[];
    overdueLoans: OverdueLoan[];
}>();

type Tab = 'all' | 'overdue';
const tab = ref<Tab>('all');
const detailsOpen = ref(false);
const details = ref<Array<{ label: string; value: string | number | null | undefined }>>([]);
const showLoan = (loan: Loan) => {
    details.value = [
        { label: trans('admin.loan_id'), value: loan.id },
        { label: trans('admin.columns.book'), value: loan.book.title },
        { label: trans('admin.columns.user'), value: loan.user?.name },
        { label: trans('admin.columns.type'), value: trans(`loans.type.${loan.type}`) },
        { label: trans('admin.columns.status'), value: trans(`loans.status.${loan.status}`) },
        {
            label: trans('admin.columns.due'),
            value: loan.due_at
                ? new Date(loan.due_at).toLocaleDateString(locale().replace('_', '-'))
                : null,
        },
        {
            label: trans('admin.columns.fine'),
            value: loan.fine ? formatCurrency(loan.fine.amount) : null,
        },
    ];
    detailsOpen.value = true;
};
const showOverdue = (item: OverdueLoan) => {
    details.value = [
        { label: trans('admin.loan_id'), value: item.id },
        { label: trans('admin.columns.user'), value: item.user?.name },
        { label: trans('auth.email'), value: item.user?.email },
        { label: trans('admin.columns.book'), value: item.book_title },
        { label: trans('admin.columns.days_overdue'), value: item.days_overdue },
        {
            label: trans('admin.columns.fine'),
            value: item.fine_amount ? formatCurrency(item.fine_amount) : null,
        },
    ];
    detailsOpen.value = true;
};
</script>
<template>
    <AdminLayout>
        <Head :title="trans('admin.all_loans')" />
        <h1 class="mb-4 font-serif text-3xl font-bold text-ink dark:text-white">
            {{ trans('admin.all_loans') }}
        </h1>

        <!-- Tabs -->
        <div class="mb-6 flex gap-2">
            <button
                class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                :class="
                    tab === 'all'
                        ? 'bg-brand-600 text-white'
                        : 'bg-white text-slate-500 dark:bg-slate-900'
                "
                @click="tab = 'all'"
            >
                {{ trans('admin.all_loans') }}
                <span
                    class="ml-1.5 rounded-full px-1.5 py-0.5 text-[10px]"
                    :class="
                        tab === 'all'
                            ? 'bg-white/20 text-white'
                            : 'bg-slate-100 text-slate-400 dark:bg-slate-800'
                    "
                    >{{ loans.length }}</span
                >
            </button>
            <button
                class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                :class="
                    tab === 'overdue'
                        ? 'bg-red-600 text-white'
                        : 'bg-white text-slate-500 dark:bg-slate-900'
                "
                @click="tab = 'overdue'"
            >
                {{ trans('admin.overdue_users') }}
                <span
                    class="ml-1.5 rounded-full px-1.5 py-0.5 text-[10px]"
                    :class="
                        tab === 'overdue'
                            ? 'bg-white/20 text-white'
                            : 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400'
                    "
                    >{{ overdueLoans.length }}</span
                >
            </button>
        </div>

        <!-- All loans -->
        <template v-if="tab === 'all'">
            <p
                v-if="!loans.length"
                class="panel p-10 text-center text-sm text-slate-500 dark:text-slate-400"
            >
                {{ trans('admin.no_loans') }}
            </p>
            <ResourceTable
                v-else
                :columns="[
                    trans('admin.columns.book'),
                    trans('admin.columns.user'),
                    trans('admin.columns.type'),
                    trans('admin.columns.status'),
                    trans('admin.columns.due'),
                    trans('admin.columns.fine'),
                ]"
            >
                <tr
                    v-for="loan in loans"
                    :key="loan.id"
                    class="cursor-pointer"
                    @click="showLoan(loan)"
                >
                    <td class="px-5 py-4 font-semibold">{{ loan.book.title }}</td>
                    <td class="px-5 py-4">{{ loan.user?.name }}</td>
                    <td class="px-5 py-4">{{ trans(`loans.type.${loan.type}`) }}</td>
                    <td class="px-5 py-4">
                        <Badge
                            :tone="
                                loan.status === 'overdue'
                                    ? 'red'
                                    : loan.status === 'active'
                                      ? 'green'
                                      : 'gray'
                            "
                            >{{ trans(`loans.status.${loan.status}`) }}</Badge
                        >
                    </td>
                    <td class="px-5 py-4">
                        {{
                            loan.due_at
                                ? new Date(loan.due_at).toLocaleDateString(
                                      locale().replace('_', '-'),
                                  )
                                : '—'
                        }}
                    </td>
                    <td class="px-5 py-4">
                        <span v-if="loan.fine" class="font-semibold text-red-600 dark:text-red-400">
                            {{ formatCurrency(loan.fine.amount) }}
                        </span>
                        <span v-else class="text-slate-400">—</span>
                    </td>
                </tr>
            </ResourceTable>
        </template>

        <!-- Overdue users -->
        <template v-else>
            <p v-if="!overdueLoans.length" class="panel p-10 text-center text-slate-500">
                {{ trans('admin.no_overdue') }}
            </p>
            <ResourceTable
                v-else
                :columns="[
                    trans('admin.columns.user'),
                    trans('admin.columns.book'),
                    trans('admin.columns.days_overdue'),
                    trans('admin.columns.fine'),
                ]"
            >
                <tr
                    v-for="item in overdueLoans"
                    :key="item.id"
                    class="cursor-pointer"
                    @click="showOverdue(item)"
                >
                    <td class="px-5 py-4">
                        <p class="font-semibold">{{ item.user?.name ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ item.user?.email }}</p>
                    </td>
                    <td class="px-5 py-4">{{ item.book_title }}</td>
                    <td class="px-5 py-4">
                        <Badge tone="red"
                            >{{ item.days_overdue }}
                            {{ trans('admin.columns.days_overdue').toLowerCase() }}</Badge
                        >
                    </td>
                    <td class="px-5 py-4">
                        <span
                            v-if="item.fine_amount"
                            class="font-semibold text-red-600 dark:text-red-400"
                        >
                            {{ formatCurrency(item.fine_amount) }}
                        </span>
                        <span v-else class="text-slate-400">—</span>
                    </td>
                </tr>
            </ResourceTable>
        </template>
        <DetailsModal
            :open="detailsOpen"
            :title="trans('admin.loan_details')"
            :items="details"
            @close="detailsOpen = false"
        />
    </AdminLayout>
</template>
