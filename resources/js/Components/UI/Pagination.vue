<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
defineProps<{ links: Array<{ url: string | null; label: string; active: boolean }> }>();
</script>

<template>
    <nav class="flex flex-wrap gap-2">
        <Link
            v-for="link in links"
            :key="link.label"
            :href="link.url ?? '#'"
            class="rounded-lg px-3 py-2 text-sm"
            :class="
                link.active
                    ? 'bg-brand-600 text-white'
                    : 'bg-white text-slate-600 dark:bg-slate-900 dark:text-slate-300'
            "
            v-html="link.label"
        />
    </nav>
</template>
