<script setup lang="ts">
import { trans } from '@/i18n';
</script>
<template>
    <span
        class="inline-block h-5 w-5 animate-spin rounded-full border-2 border-current border-r-transparent"
        :aria-label="trans('common.loading')"
    />
</template>
