<script setup lang="ts">
import Badge from '@/Components/UI/Badge.vue';
import { trans } from '@/i18n';
defineProps<{ available: number; format: 'physical' | 'digital' | 'both' }>();
</script>
<template>
    <Badge :tone="available > 0 || format === 'digital' ? 'green' : 'red'"
        >{{
            format === 'digital'
                ? trans('catalog.online')
                : available > 0
                  ? trans('catalog.available', { count: available })
                  : trans('catalog.unavailable')
        }}
    </Badge>
</template>
