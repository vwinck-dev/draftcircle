<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import type { Loan } from '@/types/models';
import { trans, locale, formatCurrency } from '@/i18n';
const props = defineProps<{ loans: Loan[] }>();
type LoanTab = 'reservations' | 'loans' | 'history';

const belongsToTab = (loan: Loan, target: LoanTab) =>
    target === 'reservations'
        ? loan.type === 'reservation' && loan.status === 'pending'
        : target === 'loans'
          ? ['active', 'overdue'].includes(loan.status)
          : ['returned', 'cancelled'].includes(loan.status);

const counts = computed<Record<LoanTab, number>>(() => ({
    reservations: props.loans.filter((loan) => belongsToTab(loan, 'reservations')).length,
    loans: props.loans.filter((loan) => belongsToTab(loan, 'loans')).length,
    history: props.loans.filter((loan) => belongsToTab(loan, 'history')).length,
}));
const firstPopulatedTab = (): LoanTab =>
    (['reservations', 'loans', 'history'] as LoanTab[]).find((item) => counts.value[item] > 0) ??
    'reservations';
const tab = ref<LoanTab>(firstPopulatedTab());
const tabs = computed(() =>
    (['reservations', 'loans', 'history'] as LoanTab[]).map((key) => ({
        key,
        count: counts.value[key],
    })),
);
const filtered = computed(() => props.loans.filter((loan) => belongsToTab(loan, tab.value)));
const tone = (status: Loan['status']) =>
    status === 'pending'
        ? 'yellow'
        : status === 'active'
          ? 'green'
          : status === 'overdue'
            ? 'red'
            : 'gray';
</script>
<template>
    <AppLayout>
        <Head :title="trans('nav.loans')" />
        <h1 class="font-serif text-3xl font-bold text-ink dark:text-white">
            {{ trans('loans.title') }}
        </h1>
        <div class="mt-6 flex gap-2 overflow-x-auto">
            <button
                v-for="item in tabs"
                :key="item.key"
                class="rounded-xl px-4 py-2 text-sm font-semibold transition duration-200 hover:-translate-y-0.5 active:scale-[0.98]"
                :class="
                    tab === item.key
                        ? 'bg-brand-600 text-white'
                        : 'bg-white text-slate-500 dark:bg-slate-900'
                "
                @click="tab = item.key"
            >
                {{ trans(`loans.${item.key}`) }}
                <span
                    class="ml-1.5 rounded-full px-1.5 py-0.5 text-[10px]"
                    :class="
                        tab === item.key
                            ? 'bg-white/20 text-white'
                            : 'bg-slate-100 text-slate-400 dark:bg-slate-800'
                    "
                    >{{ item.count }}</span
                >
            </button>
        </div>
        <div class="mt-5 space-y-3">
            <article
                v-for="loan in filtered"
                :key="loan.id"
                class="panel interactive-lift flex flex-col gap-4 p-5 sm:flex-row sm:items-center"
            >
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="font-serif font-bold text-ink dark:text-white">
                            {{ loan.book.title }}
                        </h2>
                        <Badge :tone="tone(loan.status)">{{
                            trans(`loans.status.${loan.status}`)
                        }}</Badge>
                        <Badge v-if="loan.fine" tone="red">
                            {{ trans('loans.fine', { amount: formatCurrency(loan.fine.amount) }) }}
                        </Badge>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ loan.book.author }} ·
                        {{
                            trans('loans.due', {
                                date: loan.due_at
                                    ? new Date(loan.due_at).toLocaleDateString(
                                          locale().replace('_', '-'),
                                      )
                                    : trans('loans.after_pickup'),
                            })
                        }}
                    </p>
                </div>
                <Button
                    v-if="['active', 'overdue'].includes(loan.status)"
                    variant="secondary"
                    @click="router.put(route('loans.return', loan.id))"
                    >{{ trans('loans.return') }}</Button
                >
            </article>
            <p v-if="!filtered.length" class="panel p-10 text-center text-slate-500">
                {{ trans('loans.empty') }}
            </p>
        </div>
    </AppLayout>
</template>
