<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Components/Layout/AdminLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Badge from '@/Components/UI/Badge.vue';
import type { User } from '@/types/models';
import { trans } from '@/i18n';
defineProps<{ user: User }>();
</script>
<template>
    <AdminLayout>
        <Head :title="user.name" />
        <h1 class="mb-6 font-serif text-3xl font-bold text-ink dark:text-white">
            {{ trans('admin.user_details') }}
        </h1>
        <Card>
            <div class="flex items-center gap-4">
                <div
                    class="grid h-16 w-16 place-items-center rounded-2xl bg-brand-50 text-xl font-bold text-brand-600"
                >
                    {{ user.name.slice(0, 2).toUpperCase() }}
                </div>
                <div>
                    <h2 class="font-serif text-xl font-bold">{{ user.name }}</h2>
                    <p class="text-sm text-slate-500">{{ user.email }}</p>
                    <Badge class="mt-2">{{ trans(`role.${user.role}`) }}</Badge>
                </div>
            </div>
            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase text-slate-400">
                        {{ trans('auth.registration_number') }}
                    </dt>
                    <dd class="mt-1 font-semibold">
                        {{ user.registration_number ?? trans('common.not_assigned') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-400">{{ trans('admin.user_id') }}</dt>
                    <dd class="mt-1 font-semibold">{{ user.id }}</dd>
                </div>
            </dl>
        </Card>
    </AdminLayout>
</template>
