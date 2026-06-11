<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Plus, Trash2 } from 'lucide-vue-next';
import type { Book, Category } from '@/types/models';
import Button from '@/Components/UI/Button.vue';
import { trans } from '@/i18n';

const props = defineProps<{ book?: Book; categories: Category[] }>();
const form = useForm({
    title: props.book?.title ?? '',
    author: props.book?.author ?? '',
    publisher: props.book?.publisher ?? '',
    year: props.book?.year ?? new Date().getFullYear(),
    language: props.book?.language ?? 'en',
    isbn: props.book?.isbn ?? '',
    description: props.book?.description ?? '',
    format: props.book?.format ?? 'physical',
    total_copies: props.book?.total_copies ?? 1,
    is_active: props.book?.is_active ?? true,
    category_ids: props.book?.categories.map((category) => category.id) ?? [],
    cover: null as File | null,
    pages: props.book?.pages?.map((page) => ({ content: page.content })) ?? [],
    pages_provided: true,
});
const isDigital = computed(() => form.format === 'digital' || form.format === 'both');
const addPage = () => form.pages.push({ content: '' });
const removePage = (index: number) => form.pages.splice(index, 1);
const submit = () =>
    props.book
        ? form
              .transform((data) => ({ ...data, _method: 'put' }))
              .post(route('admin.books.update', props.book.id), { forceFormData: true })
        : form.post(route('admin.books.store'), { forceFormData: true });
</script>
<template>
    <form class="panel grid gap-5 p-6" @submit.prevent="submit">
        <div class="grid gap-5 md:grid-cols-2">
            <label class="text-sm font-semibold">
                {{ trans('admin.form.title') }}
                <input v-model="form.title" class="input mt-2 w-full" required />
            </label>
            <label class="text-sm font-semibold"
                >{{ trans('admin.form.author') }}
                <input v-model="form.author" class="input mt-2 w-full" required />
            </label>
            <label class="text-sm font-semibold"
                >{{ trans('admin.form.publisher') }}
                <input v-model="form.publisher" class="input mt-2 w-full" />
            </label>
            <label class="text-sm font-semibold"
                >{{ trans('admin.form.isbn') }}
                <input v-model="form.isbn" class="input mt-2 w-full" /> </label
            ><label class="text-sm font-semibold"
                >{{ trans('admin.form.year') }}
                <input v-model="form.year" type="number" class="input mt-2 w-full" />
            </label>
            <label class="text-sm font-semibold"
                >{{ trans('admin.form.language') }}
                <input v-model="form.language" class="input mt-2 w-full" /> </label
            ><label class="text-sm font-semibold"
                >{{ trans('admin.form.format')
                }}<select v-model="form.format" class="input mt-2 w-full">
                    <option value="physical">{{ trans('catalog.format.physical') }}</option>
                    <option value="digital">{{ trans('catalog.format.digital') }}</option>
                    <option value="both">{{ trans('catalog.format.both') }}</option>
                </select>
            </label>
            <label class="text-sm font-semibold"
                >{{ trans('admin.form.copies') }}
                <input
                    v-model="form.total_copies"
                    type="number"
                    min="0"
                    class="input mt-2 w-full"
                />
            </label>
        </div>
        <label class="text-sm font-semibold">
            {{ trans('admin.form.description') }}
            <textarea v-model="form.description" rows="5" class="input mt-2 w-full" />
        </label>
        <fieldset
            v-if="isDigital"
            class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700"
        >
            <div class="sticky top-20 z-10 -mx-4 flex items-center justify-between gap-3 rounded-xl bg-white/90 px-4 py-3 backdrop-blur dark:bg-slate-900/90">
                <div>
                    <legend class="text-sm font-semibold">
                        {{ trans('admin.form.digital_content') }}
                    </legend>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        {{ trans('admin.form.digital_content_hint') }}
                    </p>
                </div>
                <Button type="button" variant="secondary" @click="addPage">
                    <Plus class="mr-2 h-4 w-4" />{{ trans('admin.form.add_page') }}
                </Button>
            </div>
            <div v-if="form.pages.length" class="mt-4 space-y-4">
                <div v-for="(page, index) in form.pages" :key="index">
                    <div class="mb-2 flex items-center justify-between">
                        <label :for="`page-${index}`" class="text-sm font-semibold">
                            {{ trans('reader.page', { page: index + 1 }) }}
                        </label>
                        <button
                            type="button"
                            class="rounded-lg p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30"
                            :aria-label="trans('admin.form.remove_page')"
                            @click="removePage(index)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                    <textarea
                        :id="`page-${index}`"
                        v-model="page.content"
                        rows="10"
                        class="input w-full resize-y font-serif leading-7"
                        required
                    />
                </div>
            </div>
            <button
                v-else
                type="button"
                class="mt-4 w-full rounded-xl border border-dashed border-slate-300 p-6 text-sm font-semibold text-slate-500 hover:border-brand-500 hover:text-brand-600 dark:border-slate-700 dark:hover:text-blue-300"
                @click="addPage"
            >
                {{ trans('admin.form.empty_content') }}
            </button>
        </fieldset>
        <fieldset>
            <legend class="text-sm font-semibold">{{ trans('admin.form.categories') }}</legend>
            <div class="mt-2 flex flex-wrap gap-3">
                <label
                    v-for="category in categories"
                    :key="category.id"
                    class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm transition-colors hover:border-brand-300 hover:bg-brand-50 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:text-brand-700 dark:border-slate-700 dark:hover:border-brand-700 dark:hover:bg-brand-900/20 dark:has-[:checked]:border-brand-600 dark:has-[:checked]:bg-brand-900/30 dark:has-[:checked]:text-brand-400"
                >
                    <input
                        v-model="form.category_ids"
                        type="checkbox"
                        :value="category.id"
                        class="accent-brand-600"
                    />
                    {{ category.name }}
                </label>
            </div>
        </fieldset>
        <label class="text-sm font-semibold"
            >{{ trans('admin.form.cover') }}
            <input
                type="file"
                accept="image/*"
                class="mt-2 block text-sm"
                @change="form.cover = ($event.target as HTMLInputElement).files?.[0] ?? null"
            />
        </label>
        <label
            class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold transition-colors hover:border-brand-300 hover:bg-brand-50 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:text-brand-700 dark:border-slate-700 dark:hover:border-brand-700 dark:hover:bg-brand-900/20 dark:has-[:checked]:border-brand-600 dark:has-[:checked]:bg-brand-900/30 dark:has-[:checked]:text-brand-400"
            ><input v-model="form.is_active" type="checkbox" class="accent-brand-600" />{{
                trans('admin.form.active')
            }}</label
        >
        <div>
            <Button type="submit" :disabled="form.processing">
                {{
                    form.processing ? trans('common.saving') : trans('admin.form.save_book')
                }}</Button
            >
        </div>
    </form>
</template>
