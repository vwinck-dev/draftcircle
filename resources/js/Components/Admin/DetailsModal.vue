<script setup lang="ts">
import { watch } from 'vue';
import { X } from 'lucide-vue-next';
import Modal from '@/Components/UI/Modal.vue';
import { trans } from '@/i18n';
import { useSoundEffects } from '@/composables/useSoundEffects';

const props = defineProps<{
    open: boolean;
    title: string;
    items: Array<{ label: string; value: string | number | null | undefined }>;
}>();
defineEmits<{ close: [] }>();

const sounds = useSoundEffects();
watch(() => props.open, (val) => (val ? sounds.open() : sounds.dismiss()));
</script>

<template>
    <Modal :open="open" :title="title" @close="$emit('close')">
        <button
            type="button"
            class="absolute right-4 top-4 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white"
            :aria-label="trans('common.close')"
            @click="$emit('close')"
        >
            <X class="h-4 w-4" />
        </button>
        <dl class="grid gap-2 sm:grid-cols-2">
            <div
                v-for="item in items"
                :key="item.label"
                class="group relative overflow-hidden rounded-xl border border-slate-100 bg-slate-50 p-3 transition-colors hover:border-brand-200 hover:bg-brand-50/40 dark:border-slate-800 dark:bg-slate-800/50 dark:hover:border-brand-800 dark:hover:bg-brand-900/20"
            >
                <span class="absolute inset-y-0 left-0 w-0.5 rounded-full bg-brand-500 opacity-0 transition-opacity group-hover:opacity-100" />
                <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    {{ item.label }}
                </dt>
                <dd class="mt-1.5 break-words text-sm font-semibold text-ink dark:text-white">
                    {{ item.value ?? '—' }}
                </dd>
            </div>
        </dl>
    </Modal>
</template>
