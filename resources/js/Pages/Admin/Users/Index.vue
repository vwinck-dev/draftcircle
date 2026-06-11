<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Components/Layout/AdminLayout.vue';
import DetailsModal from '@/Components/Admin/DetailsModal.vue';
import ResourceTable from '@/Components/Admin/ResourceTable.vue';
import Badge from '@/Components/UI/Badge.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import type { Paginated, User } from '@/types/models';
import { trans } from '@/i18n';
defineProps<{ users: Paginated<User> }>();
const selected = ref<User | null>(null);
const detailItems = (user: User) => [
    { label: trans('admin.user_id'), value: user.id },
    { label: trans('admin.columns.name'), value: user.name },
    { label: trans('auth.email'), value: user.email },
    { label: trans('admin.columns.registration'), value: user.registration_number },
    { label: trans('admin.columns.role'), value: trans(`role.${user.role}`) },
    { label: trans('admin.columns.loans'), value: user.loans_count ?? 0 },
];
</script>
<template>
    <AdminLayout>
        <Head :title="trans('admin.users')" />
        <h1 class="mb-6 font-serif text-3xl font-bold text-ink dark:text-white">
            {{ trans('admin.users') }}
        </h1>
        <template v-if="users.data.length">
            <ResourceTable
                :columns="[
                    trans('admin.columns.name'),
                    trans('admin.columns.registration'),
                    trans('admin.columns.role'),
                    trans('admin.columns.loans'),
                    '',
                ]"
            >
                <tr
                    v-for="user in users.data"
                    :key="user.id"
                    class="cursor-pointer"
                    @click="selected = user"
                >
                    <td class="px-5 py-4">
                        <p class="font-semibold">{{ user.name }}</p>
                        <p class="text-xs text-slate-400">{{ user.email }}</p>
                    </td>
                    <td class="px-5 py-4">{{ user.registration_number ?? '—' }}</td>
                    <td class="px-5 py-4">
                        <Badge>{{ trans(`role.${user.role}`) }}</Badge>
                    </td>
                    <td class="px-5 py-4">{{ user.loans_count }}</td>
                    <td class="px-5 py-4">
                        <Link
                            :href="route('admin.users.show', user.id)"
                            class="font-semibold text-brand-600"
                            @click.stop
                            >{{ trans('common.view') }}</Link
                        >
                    </td>
                </tr>
            </ResourceTable>
            <Pagination class="mt-5" :links="users.links" />
        </template>
        <p v-else class="panel p-10 text-center text-sm text-slate-500 dark:text-slate-400">
            {{ trans('admin.no_users') }}
        </p>
        <DetailsModal
            :open="selected !== null"
            :title="trans('admin.user_details')"
            :items="selected ? detailItems(selected) : []"
            @close="selected = null"
        />
    </AdminLayout>
</template>
