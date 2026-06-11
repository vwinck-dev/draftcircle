<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Bell, CheckCheck } from 'lucide-vue-next';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Button from '@/Components/UI/Button.vue';
import type { DatabaseNotification } from '@/types/models';
import { trans, locale } from '@/i18n';
import { useSoundEffects } from '@/composables/useSoundEffects';

const sounds = useSoundEffects();

const props = defineProps<{ notifications: DatabaseNotification[] }>();
const processing = ref(false);
const cleared = ref(false);
const local = ref<DatabaseNotification[]>([...props.notifications]);

watch(
    () => props.notifications,
    (next) => {
        if (!cleared.value) local.value = [...next];
    },
);

const hasUnread = computed(() => local.value.some((n) => !n.read_at));
const notificationMessage = (notification: DatabaseNotification) =>
    notification.data.message_key
        ? trans(notification.data.message_key, notification.data.message_params ?? {})
        : (notification.data.message ?? '');

const markAllRead = () => {
    if (!hasUnread.value || processing.value) return;
    processing.value = true;
    cleared.value = true;
    local.value = [];
    sounds.pop();

    router.put(
        route('notifications.read-all'),
        {},
        {
            preserveScroll: true,
            onFinish: () => (processing.value = false),
        },
    );
};
</script>
<template>
    <AppLayout>
        <Head :title="trans('notifications.title')" />
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="font-serif text-3xl font-bold text-ink dark:text-white">
                    {{ trans('notifications.title') }}
                </h1>
                <p class="mt-2 text-slate-500">{{ trans('notifications.description') }}</p>
            </div>
            <Button variant="secondary" :disabled="!hasUnread || processing" @click="markAllRead">
                <CheckCheck class="mr-2 h-4 w-4" />{{
                    processing
                        ? trans('notifications.marking_all')
                        : hasUnread
                          ? trans('notifications.mark_all')
                          : trans('notifications.all_read')
                }}
            </Button>
        </div>
        <TransitionGroup tag="div" class="relative mt-6 space-y-3" name="notif">
            <article
                v-for="notification in local"
                :key="notification.id"
                class="panel notification-card flex gap-4 p-5"
                :class="{ 'border-brand-500': !notification.read_at }"
            >
                <span
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600 dark:bg-slate-800"
                >
                    <Bell class="h-5 w-5" />
                </span>
                <div>
                    <p class="font-semibold">{{ notificationMessage(notification) }}</p>
                    <p class="mt-1 text-xs text-slate-400">
                        {{
                            new Date(notification.created_at).toLocaleString(
                                locale().replace('_', '-'),
                            )
                        }}
                    </p>
                </div>
            </article>
            <p v-if="!local.length" key="empty" class="panel p-10 text-center text-slate-500">
                {{ trans('notifications.empty') }}
            </p>
        </TransitionGroup>
    </AppLayout>
</template>
