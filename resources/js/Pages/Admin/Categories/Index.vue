<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Pencil, Trash2 } from 'lucide-vue-next';
import AdminLayout from '@/Components/Layout/AdminLayout.vue';
import Modal from '@/Components/UI/Modal.vue';
import type { Category } from '@/types/models';
import { trans } from '@/i18n';

defineProps<{ categories: (Category & { books_count: number })[] }>();

const modalOpen = ref(false);
const editTarget = ref<Category | null>(null);

const form = useForm({ name: '', slug: '' });

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    modalOpen.value = true;
};

const openEdit = (category: Category) => {
    editTarget.value = category;
    form.name = category.name;
    form.slug = category.slug;
    modalOpen.value = true;
};

const close = () => {
    modalOpen.value = false;
    form.clearErrors();
};

watch(
    () => form.name,
    (name) => {
        if (!editTarget.value) {
            form.slug = name
                .toLowerCase()
                .normalize('NFD')
                .replace(/[̀-ͯ]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '');
        }
    },
);

const submit = () => {
    if (editTarget.value) {
        form.put(route('admin.categories.update', editTarget.value.id), {
            onSuccess: close,
        });
    } else {
        form.post(route('admin.categories.store'), {
            onSuccess: close,
        });
    }
};

const deleteCategory = (category: Category & { books_count: number }) => {
    if (category.books_count > 0) return;
    form.delete(route('admin.categories.destroy', category.id));
};
</script>

<template>
    <AdminLayout>
        <Head :title="trans('admin.manage_categories')" />
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="font-serif text-3xl font-bold text-ink dark:text-white">
                    {{ trans('admin.manage_categories') }}
                </h1>
                <p class="mt-1 text-sm text-slate-400">
                    {{ trans('admin.manage_categories_text') }}
                </p>
            </div>
            <button
                type="button"
                class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white"
                @click="openCreate"
            >
                {{ trans('admin.add_category') }}
            </button>
        </div>

        <div v-if="categories.length" class="panel overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead
                    class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800/50"
                >
                    <tr>
                        <th class="px-5 py-3">{{ trans('admin.category_name') }}</th>
                        <th class="px-5 py-3">{{ trans('admin.category_slug') }}</th>
                        <th class="px-5 py-3">{{ trans('admin.columns.copies') }}</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="category in categories" :key="category.id">
                        <td class="px-5 py-4 font-semibold">{{ category.name }}</td>
                        <td class="px-5 py-4 font-mono text-slate-400">{{ category.slug }}</td>
                        <td class="px-5 py-4 text-slate-500">
                            {{ trans('admin.category_books', { count: category.books_count }) }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    class="text-brand-600 hover:text-brand-700 dark:text-blue-400"
                                    :aria-label="trans('common.edit')"
                                    @click="openEdit(category)"
                                >
                                    <Pencil class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="text-rose-500 hover:text-rose-600 disabled:cursor-not-allowed disabled:opacity-30"
                                    :disabled="category.books_count > 0"
                                    :title="
                                        category.books_count > 0
                                            ? trans('flash.category_has_books')
                                            : trans('common.delete')
                                    "
                                    :aria-label="trans('common.delete')"
                                    @click="deleteCategory(category)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-else class="panel p-10 text-center text-sm text-slate-500 dark:text-slate-400">
            {{ trans('admin.no_categories') }}
        </p>

        <Modal
            :open="modalOpen"
            :title="editTarget ? trans('admin.edit_category') : trans('admin.new_category')"
            @close="close"
        >
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label
                        for="cat-name"
                        class="mb-1 block text-sm font-semibold text-ink dark:text-white"
                    >
                        {{ trans('admin.category_name') }}
                    </label>
                    <input
                        id="cat-name"
                        v-model="form.name"
                        type="text"
                        class="w-full"
                        :placeholder="trans('admin.category_name')"
                        required
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-rose-500">
                        {{ form.errors.name }}
                    </p>
                </div>
                <div>
                    <label
                        for="cat-slug"
                        class="mb-1 block text-sm font-semibold text-ink dark:text-white"
                    >
                        {{ trans('admin.category_slug') }}
                    </label>
                    <input
                        id="cat-slug"
                        v-model="form.slug"
                        type="text"
                        class="w-full font-mono"
                        :placeholder="trans('admin.category_slug')"
                        required
                    />
                    <p class="mt-1 text-xs text-slate-400">
                        {{ trans('admin.category_slug_hint') }}
                    </p>
                    <p v-if="form.errors.slug" class="mt-1 text-xs text-rose-500">
                        {{ form.errors.slug }}
                    </p>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
                        @click="close"
                    >
                        {{ trans('common.cancel') }}
                    </button>
                    <button
                        type="submit"
                        class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-bold text-white disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ editTarget ? trans('common.save') : trans('admin.add_category') }}
                    </button>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
