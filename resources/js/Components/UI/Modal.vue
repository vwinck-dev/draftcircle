<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
defineProps<{ open: boolean; title: string }>();
defineEmits<{ close: [] }>();
</script>

<template>
    <TransitionRoot :show="open" as="template">
        <Dialog class="relative z-50" @close="$emit('close')">
            <TransitionChild
                as="template"
                enter="duration-200 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-150 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" />
            </TransitionChild>
            <div class="fixed inset-0 grid place-items-center p-4">
                <TransitionChild
                    as="template"
                    enter="duration-200 ease-out"
                    enter-from="opacity-0 scale-95 translate-y-2"
                    enter-to="opacity-100 scale-100 translate-y-0"
                    leave="duration-150 ease-in"
                    leave-from="opacity-100 scale-100 translate-y-0"
                    leave-to="opacity-0 scale-95 translate-y-2"
                >
                    <DialogPanel class="panel relative w-full max-w-lg overflow-hidden">
                        <div class="border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                            <DialogTitle class="text-lg font-bold text-ink dark:text-white">{{ title }}</DialogTitle>
                        </div>
                        <div class="p-6">
                            <slot />
                        </div>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
