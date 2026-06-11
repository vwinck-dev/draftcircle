<script setup lang="ts">
import AppLayout from './AppLayout.vue';
import Sidebar from './Sidebar.vue';
</script>

<template>
    <AppLayout>
        <div class="grid gap-6 lg:grid-cols-[220px_1fr]">
            <Sidebar />
            <div>
                <slot />
            </div>
        </div>
    </AppLayout>
</template>
