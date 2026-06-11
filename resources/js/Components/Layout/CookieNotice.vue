<script setup lang="ts">
import { onMounted, onUnmounted, reactive, ref } from 'vue';
import { BarChart3, Cookie, Lock, Sparkles, X } from 'lucide-vue-next';
import Button from '@/Components/UI/Button.vue';
import { trans } from '@/i18n';
import {
    allConsents,
    hasSetConsent,
    saveConsents,
    type CookieConsents,
} from '@/composables/useRecommendationCookies';

const visible = ref(false);
const local = reactive<CookieConsents>({ recommendations: false, analytics: false });

const open = () => {
    const current = allConsents();
    local.recommendations = current.recommendations;
    local.analytics = current.analytics;
    visible.value = true;
};

const save = () => {
    saveConsents({ ...local });
    visible.value = false;
};

const acceptAll = () => {
    local.recommendations = true;
    local.analytics = true;
    save();
};

onMounted(() => {
    if (!hasSetConsent()) open();
    globalThis.addEventListener('library:open-cookie-preferences', open);
});

onUnmounted(() => globalThis.removeEventListener('library:open-cookie-preferences', open));



const categories = [
    {
        key: 'essential',
        icon: Lock,
        always: true,
        name: () => trans('cookies.essential_name'),
        desc: () => trans('cookies.essential_desc'),
    },
    {
        key: 'recommendations',
        icon: Sparkles,
        always: false,
        name: () => trans('cookies.recommendations_name'),
        desc: () => trans('cookies.recommendations_desc'),
        model: () => local.recommendations,
        toggle: () => { local.recommendations = !local.recommendations; },
    },
    {
        key: 'analytics',
        icon: BarChart3,
        always: false,
        name: () => trans('cookies.analytics_name'),
        desc: () => trans('cookies.analytics_desc'),
        model: () => local.analytics,
        toggle: () => { local.analytics = !local.analytics; },
    },
];
</script>

<template>
    <Transition name="slide-fade">
        <div
            v-if="visible"
            class="fixed inset-0 z-50 flex items-end justify-center p-3 sm:items-center sm:p-6"
        >
            <div
                class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                @click="hasSetConsent() && (visible = false)"
            />

            <dialog
                open
                class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl dark:border-slate-700 dark:bg-slate-900"
                :aria-label="trans('cookies.title')"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800"
                >
                    <div class="flex items-center gap-2.5">
                        <Cookie class="h-5 w-5 text-brand-600 dark:text-blue-400" />
                        <h2 class="font-serif text-lg font-bold text-ink dark:text-white">
                            {{ trans('cookies.title') }}
                        </h2>
                    </div>
                    <button
                        v-if="hasSetConsent()"
                        type="button"
                        class="rounded-xl p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                        @click="visible = false"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <p class="px-6 py-4 text-sm leading-6 text-slate-500 dark:text-slate-400">
                    {{ trans('cookies.description') }}
                </p>

                <div class="divide-y divide-slate-100 px-6 dark:divide-slate-800">
                    <div
                        v-for="cat in categories"
                        :key="cat.key"
                        class="flex items-start gap-4 py-4"
                    >
                        <span
                            class="mt-0.5 shrink-0 rounded-lg bg-brand-50 p-1.5 text-brand-600 dark:bg-brand-900/30 dark:text-blue-400"
                        >
                            <component :is="cat.icon" class="h-4 w-4" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-ink dark:text-white">
                                {{ cat.name() }}
                            </p>
                            <p class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                {{ cat.desc() }}
                            </p>
                        </div>
                        <span
                            v-if="cat.always"
                            class="shrink-0 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400"
                        >
                            {{ trans('cookies.always_active') }}
                        </span>
                        <button
                            v-else
                            type="button"
                            role="switch"
                            :aria-checked="cat.model!()"
                            class="relative mt-0.5 inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors duration-200"
                            :class="
                                cat.model!()
                                    ? 'bg-brand-600'
                                    : 'bg-slate-200 dark:bg-slate-700'
                            "
                            @click="cat.toggle!()"
                        >
                            <span
                                class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200"
                                :class="cat.model!() ? 'translate-x-6' : 'translate-x-1'"
                            />
                        </button>
                    </div>
                </div>

                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-6 py-4 dark:border-slate-800"
                >
                    <button
                        type="button"
                        class="text-sm text-slate-500 transition hover:text-slate-700 dark:hover:text-slate-200"
                        @click="() => { local.recommendations = false; local.analytics = false; save(); }"
                    >
                        {{ trans('cookies.decline') }}
                    </button>
                    <div class="flex gap-2">
                        <Button variant="secondary" @click="save">
                            {{ trans('cookies.save') }}
                        </Button>
                        <Button @click="acceptAll">
                            {{ trans('cookies.accept_all') }}
                        </Button>
                    </div>
                </div>
            </dialog>
        </div>
    </Transition>
</template>
