<script setup lang="ts">
import AppLayout from '@/Components/Layout/AppLayout.vue';
</script>

<template>
    <AppLayout>
        <header v-if="$slots.header" class="mb-6">
            <slot name="header" />
        </header>
        <slot />
    </AppLayout>
</template>
