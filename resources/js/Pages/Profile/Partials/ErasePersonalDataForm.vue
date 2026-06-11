<script setup lang="ts">
import { nextTick, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { BookOpen, CheckCheck, Cookie, ShieldAlert, Star } from 'lucide-vue-next';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';
import { trans } from '@/i18n';
import { saveConsents } from '@/composables/useRecommendationCookies';

const open = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({ password: '' });

const items = [
    { icon: BookOpen, key: 'erase_item_progress' },
    { icon: Star,     key: 'erase_item_ratings' },
    { icon: CheckCheck, key: 'erase_item_notifications' },
    { icon: Cookie,   key: 'erase_item_cookies' },
] as const;

const openModal = () => {
    open.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const closeModal = () => {
    open.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    form.delete(route('profile.personal-data.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            saveConsents({ recommendations: false, analytics: false });
            closeModal();
        },
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <section
        class="rounded-2xl border border-rose-200 bg-rose-50/50 p-5 dark:border-rose-900/40 dark:bg-rose-950/10"
    >
        <div class="flex items-start gap-4">
            <span
                class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400"
            >
                <ShieldAlert class="h-5 w-5" />
            </span>
            <div class="flex-1">
                <h3 class="font-serif font-bold text-ink dark:text-white">
                    {{ trans('profile.erase_title') }}
                </h3>
                <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                    {{ trans('profile.erase_text') }}
                </p>
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <Button variant="danger" @click="openModal">
                {{ trans('profile.erase_button') }}
            </Button>
        </div>
    </section>

    <Modal :open="open" :title="trans('profile.erase_confirm_title')" @close="closeModal">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ trans('profile.erase_confirm_text') }}
        </p>

        <div class="mt-4 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">
                {{ trans('profile.erase_items_title') }}
            </p>
            <ul class="mt-3 space-y-2">
                <li
                    v-for="item in items"
                    :key="item.key"
                    class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300"
                >
                    <component
                        :is="item.icon"
                        class="h-4 w-4 shrink-0 text-rose-400"
                    />
                    {{ trans(`profile.${item.key}`) }}
                </li>
            </ul>
        </div>

        <div class="mt-5">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                {{ trans('auth.password') }}
            </label>
            <input
                ref="passwordInput"
                v-model="form.password"
                type="password"
                class="input mt-1.5 w-full"
                :placeholder="trans('auth.password')"
                @keyup.enter="submit"
            />
            <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-600">
                {{ form.errors.password }}
            </p>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <Button variant="secondary" @click="closeModal">
                {{ trans('common.cancel') }}
            </Button>
            <Button
                variant="danger"
                :disabled="!form.password || form.processing"
                @click="submit"
            >
                {{ trans('profile.erase_button') }}
            </Button>
        </div>
    </Modal>
</template>
