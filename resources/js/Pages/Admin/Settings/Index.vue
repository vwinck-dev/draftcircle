<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AdminLayout from '@/Components/Layout/AdminLayout.vue';
import Button from '@/Components/UI/Button.vue';
import { trans } from '@/i18n';
const props = defineProps<{ settings: Record<string, Record<string, string | null>> }>();
const group = ref(Object.keys(props.settings)[0] ?? 'general');
const form = useForm({ group: group.value, settings: { ...(props.settings[group.value] ?? {}) } });
const groups = computed(() => Object.keys(props.settings));
const select = (value: string) => {
    group.value = value;
    form.group = value;
    form.settings = { ...(props.settings[value] ?? {}) };
};
const save = () => form.put(route('admin.settings.update'));
</script>
<template>
    <AdminLayout>
        <Head :title="trans('admin.system_settings')" />
        <h1 class="font-serif text-3xl font-bold text-ink dark:text-white">
            {{ trans('admin.system_settings') }}
        </h1>
        <div class="mt-6 grid gap-5 lg:grid-cols-[190px_1fr]">
            <nav class="panel h-fit p-2">
                <button
                    v-for="item in groups"
                    :key="item"
                    type="button"
                    class="block w-full rounded-xl px-3 py-2.5 text-left text-sm font-semibold"
                    :class="
                        group === item
                            ? 'bg-brand-600 text-white'
                            : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800'
                    "
                    @click="select(item)"
                >
                    {{ trans(`admin.group.${item}`) }}
                </button>
            </nav>
            <form class="panel space-y-5 p-6" @submit.prevent="save">
                <label
                    v-for="(_, key) in form.settings"
                    :key="key"
                    class="block text-sm font-semibold text-slate-700 dark:text-slate-200"
                >
                    {{ trans(`admin.setting.${String(key)}`) }}
                    <textarea
                        v-if="key === 'blocked_comment_words'"
                        v-model="form.settings[key]"
                        rows="8"
                        class="input mt-2 w-full resize-y"
                        :placeholder="trans('admin.blocked_words_placeholder')"
                    ></textarea>
                    <input v-else v-model="form.settings[key]" class="input mt-2 w-full" />
                    <span
                        v-if="key === 'blocked_comment_words'"
                        class="mt-2 block text-xs font-normal leading-5 text-slate-500 dark:text-slate-400"
                    >
                        {{ trans('admin.blocked_words_hint') }}
                    </span>
                </label>
                <Button type="submit" :disabled="form.processing">{{
                    trans('admin.save_settings')
                }}</Button>
            </form>
        </div>
    </AdminLayout>
</template>
