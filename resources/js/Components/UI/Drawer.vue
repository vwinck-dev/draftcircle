<script setup lang="ts">
import { Dialog, DialogPanel, TransitionRoot } from '@headlessui/vue';
defineProps<{ open: boolean }>();
defineEmits<{ close: [] }>();
</script>
<template>
    <TransitionRoot :show="open" as="template">
        <Dialog class="relative z-50" @close="$emit('close')">
            <div class="fixed inset-0 bg-slate-950/40" />
            <DialogPanel
                class="fixed inset-y-0 right-0 w-80 bg-white p-6 shadow-2xl dark:bg-slate-900"
            >
                <slot />
            </DialogPanel>
        </Dialog>
    </TransitionRoot>
</template>
