<script setup lang="ts" generic="T extends Record<string, unknown>">
import { computed, ref } from 'vue';
import { trans } from '@/i18n';

export interface DataTableColumn<T> {
    key: keyof T & string;
    label: string;
    sortable?: boolean;
}

const props = withDefaults(
    defineProps<{
        rows: T[];
        columns: DataTableColumn<T>[];
        searchable?: boolean;
        selectable?: boolean;
    }>(),
    {
        searchable: true,
        selectable: true,
    },
);
const emit = defineEmits<{ action: [action: string, row: T]; selection: [rows: T[]] }>();
const search = ref('');
const sortKey = ref<keyof T & string>();
const descending = ref(false);
const selectedIndexes = ref<number[]>([]);

const displayedRows = computed(() => {
    const query = search.value.trim().toLowerCase();
    const rows = query
        ? props.rows.filter((row) =>
              Object.values(row).some((value) =>
                  String(value ?? '')
                      .toLowerCase()
                      .includes(query),
              ),
          )
        : [...props.rows];

    if (!sortKey.value) return rows;

    return rows.sort((left, right) => {
        const result = String(left[sortKey.value!] ?? '').localeCompare(
            String(right[sortKey.value!] ?? ''),
            undefined,
            { numeric: true },
        );
        return descending.value ? -result : result;
    });
});

const sort = (column: DataTableColumn<T>) => {
    if (!column.sortable) return;
    descending.value = sortKey.value === column.key ? !descending.value : false;
    sortKey.value = column.key;
};

const toggle = (row: T) => {
    const index = props.rows.indexOf(row);
    selectedIndexes.value = selectedIndexes.value.includes(index)
        ? selectedIndexes.value.filter((item) => item !== index)
        : [...selectedIndexes.value, index];
    emit(
        'selection',
        props.rows.filter((_, rowIndex) => selectedIndexes.value.includes(rowIndex)),
    );
};
</script>

<template>
    <div class="panel overflow-hidden">
        <div v-if="searchable" class="border-b border-slate-100 p-4 dark:border-slate-800">
            <input
                v-model="search"
                type="search"
                class="input w-full max-w-sm"
                :placeholder="trans('common.search_table')"
                :aria-label="trans('common.search_table')"
            />
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead
                    class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800/50"
                >
                    <tr>
                        <th v-if="selectable" class="w-10 px-4 py-3">
                            <span class="sr-only">{{ trans('common.select') }}</span>
                        </th>
                        <th v-for="column in columns" :key="column.key" class="px-5 py-3">
                            <button
                                type="button"
                                class="font-bold uppercase"
                                :disabled="!column.sortable"
                                @click="sort(column)"
                            >
                                {{ column.label }}
                            </button>
                        </th>
                        <th class="px-5 py-3">
                            <span class="sr-only">{{ trans('common.actions') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="(row, index) in displayedRows" :key="index">
                        <td v-if="selectable" class="px-4 py-4">
                            <input
                                type="checkbox"
                                :checked="selectedIndexes.includes(rows.indexOf(row))"
                                :aria-label="trans('common.select_row')"
                                @change="toggle(row)"
                            />
                        </td>
                        <td v-for="column in columns" :key="column.key" class="px-5 py-4">
                            <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]"
                                >{{ row[column.key] }}
                            </slot>
                        </td>
                        <td class="px-5 py-4">
                            <slot
                                name="actions"
                                :row="row"
                                :action="(name: string) => emit('action', name, row)"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-if="displayedRows.length === 0" class="p-8 text-center text-sm text-slate-500">
            {{ trans('common.no_records') }}
        </p>
        <slot name="pagination" />
    </div>
</template>
