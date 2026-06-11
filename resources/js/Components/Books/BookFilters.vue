<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useDebounce } from '@/composables/useDebounce';
import { trans } from '@/i18n';
const search = ref(new URLSearchParams(location.search).get('search') ?? '');
const format = ref(new URLSearchParams(location.search).get('format') ?? '');
const apply = useDebounce(
    () =>
        router.get(
            route('books.index'),
            { search: search.value, format: format.value },
            { preserveState: true, replace: true },
        ),
    300,
);
watch([search, format], apply);
</script>
<template>
    <div class="panel grid gap-3 p-4 sm:grid-cols-[1fr_180px]">
        <input
            v-model="search"
            class="input w-full"
            :placeholder="trans('catalog.search')"
        /><select v-model="format" class="input w-full">
            <option value="">{{ trans('catalog.all_formats') }}</option>
            <option value="physical">{{ trans('catalog.format.physical') }}</option>
            <option value="digital">{{ trans('catalog.format.digital') }}</option>
            <option value="both">{{ trans('catalog.format.both') }}</option>
        </select>
    </div>
</template>
