<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, onUnmounted } from 'vue';
import { trans } from '@/i18n';
import type { PageProps } from '@/types';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const page = usePage<PageProps>();
const user = computed(() => page.props.auth.user!);
const avatarPreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    _method: 'patch',
    name: user.value.name,
    email: user.value.email,
    avatar: null as File | null,
});

const onAvatarChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    if (avatarPreview.value) URL.revokeObjectURL(avatarPreview.value);
    form.avatar = file;
    avatarPreview.value = URL.createObjectURL(file);
};

onUnmounted(() => {
    if (avatarPreview.value) URL.revokeObjectURL(avatarPreview.value);
});

const clearAvatarSelection = () => {
    form.avatar = null;

    if (avatarPreview.value) {
        URL.revokeObjectURL(avatarPreview.value);
        avatarPreview.value = null;
    }

    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    form.post(route('profile.update'), {
        forceFormData: true,
        onSuccess: clearAvatarSelection,
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                {{ trans('profile.information') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-slate-400">
                {{ trans('profile.information_text') }}
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <!-- Avatar upload -->
            <div class="flex items-center gap-4">
                <UserAvatar
                    :name="form.name || user.name"
                    :src="avatarPreview ?? user.avatar_url"
                    size="xl"
                />
                <div>
                    <InputLabel :value="trans('profile.avatar')" />
                    <input
                        id="avatar"
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="sr-only"
                        @change="onAvatarChange"
                    />
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <label
                            for="avatar"
                            class="cursor-pointer rounded-lg bg-brand-50 px-3 py-1.5 text-sm font-semibold text-brand-700 hover:bg-brand-100 hover:text-brand-800 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-blue-300"
                        >
                            {{ trans('profile.choose_avatar') }}
                        </label>
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            {{
                                form.avatar?.name ??
                                trans('profile.no_avatar_selected')
                            }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">{{ trans('profile.avatar_hint') }}</p>
                    <InputError :message="form.errors.avatar" class="mt-1" />
                </div>
            </div>

            <div>
                <InputLabel for="name" :value="trans('auth.name')" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" :value="trans('auth.email')" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800 dark:text-slate-200">
                    {{ trans('profile.email_unverified') }}
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-slate-300 dark:hover:text-blue-300"
                    >
                        {{ trans('profile.resend_email') }}
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    {{ trans('profile.verification_sent') }}
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">{{
                    trans('common.save')
                }}</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600 dark:text-slate-400"
                    >
                        {{ trans('common.saved') }}
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
