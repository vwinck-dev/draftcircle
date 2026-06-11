<script setup lang="ts">
defineProps<{ columns: string[] }>();
</script>
<template>
    <div class="panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead
                    class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800/50"
                >
                    <tr>
                        <th v-for="column in columns" :key="column" class="px-5 py-3">
                            {{ column }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <slot />
                </tbody>
            </table>
        </div>
    </div>
</template>
