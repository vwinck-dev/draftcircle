<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BookOpen,
    ChevronLeft,
    ChevronRight,
    Columns2,
    List,
    Minus,
    Moon,
    Plus,
} from 'lucide-vue-next';
import Drawer from '@/Components/UI/Drawer.vue';
import { useReadingProgress } from '@/composables/useReadingProgress';
import type { Book } from '@/types/models';
import { trans } from '@/i18n';

const props = defineProps<{ book: Book; currentPage: number }>();
const { currentPage } = useReadingProgress(props.book.id, props.currentPage);
const drawer = ref(false);
const fontSize = ref(18);
const lineHeight = ref(1.9);
const viewMode = ref<'single' | 'spread'>('single');
const isWide = ref(false);
let mediaQuery: ReturnType<typeof globalThis.window.matchMedia> | undefined;

const totalPages = computed(() => props.book.pages?.length ?? 0);
const pageAt = (pageNumber: number) =>
    props.book.pages?.find((page) => page.page_number === pageNumber);
const visiblePages = computed(() => {
    const pages = [pageAt(currentPage.value)];

    if (viewMode.value === 'spread' && isWide.value) {
        pages.push(pageAt(currentPage.value + 1));
    }

    return pages.filter((page) => page !== undefined);
});
const step = computed(() => (viewMode.value === 'spread' && isWide.value ? 2 : 1));

const goTo = (n: number) => {
    currentPage.value = Math.max(1, Math.min(n, totalPages.value));
};

const prevPage = () => goTo(currentPage.value - step.value);
const nextPage = () => goTo(currentPage.value + step.value);

const hasPrev = computed(() => currentPage.value > 1);
const hasNext = computed(() => currentPage.value + step.value <= totalPages.value);

const onKeydown = (e: KeyboardEvent) => {
    const targetName = (e.target as { nodeName?: string } | null)?.nodeName;
    if (targetName === 'INPUT' || targetName === 'TEXTAREA') return;
    if (e.key === 'ArrowRight' || e.key === 'PageDown') {
        e.preventDefault();
        nextPage();
    } else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
        e.preventDefault();
        prevPage();
    }
};

const updateWidth = (event: { matches: boolean }) => {
    isWide.value = event.matches;
};

onMounted(() => {
    globalThis.window.addEventListener('keydown', onKeydown);
    mediaQuery = globalThis.window.matchMedia('(min-width: 1024px)');
    updateWidth(mediaQuery);
    mediaQuery.addEventListener('change', updateWidth);

    const savedMode = localStorage.getItem('reader-view-mode');
    const savedFontSize = Number(localStorage.getItem('reader-font-size'));
    const savedLineHeight = Number(localStorage.getItem('reader-line-height'));

    if (savedMode === 'single' || savedMode === 'spread') viewMode.value = savedMode;
    if (savedFontSize >= 16 && savedFontSize <= 28) fontSize.value = savedFontSize;
    if (savedLineHeight >= 1.6 && savedLineHeight <= 2.2) lineHeight.value = savedLineHeight;
});

onUnmounted(() => {
    globalThis.window.removeEventListener('keydown', onKeydown);
    mediaQuery?.removeEventListener('change', updateWidth);
});

watch(viewMode, (mode) => localStorage.setItem('reader-view-mode', mode));
watch(fontSize, (size) => localStorage.setItem('reader-font-size', String(size)));
watch(lineHeight, (height) => localStorage.setItem('reader-line-height', String(height)));

const toggleDark = () => document.documentElement.classList.toggle('dark');
const decreaseFont = () => (fontSize.value = Math.max(16, fontSize.value - 2));
const increaseFont = () => (fontSize.value = Math.min(28, fontSize.value + 2));
</script>

<template>
    <div class="min-h-screen bg-[#f4efe4] text-slate-800 dark:bg-slate-950 dark:text-slate-100">
        <Head :title="trans('reader.reading', { title: book.title })" />

        <header
            class="sticky top-0 z-20 border-b border-black/10 bg-[#f4efe4]/90 px-3 py-2 backdrop-blur-xl dark:border-white/10 dark:bg-slate-950/90 sm:px-5"
        >
            <div class="mx-auto flex max-w-7xl items-center gap-2">
                <Link
                    :href="route('books.show', book.id)"
                    :aria-label="trans('reader.back')"
                    class="rounded-xl p-2 transition hover:bg-black/5 dark:hover:bg-white/5"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-serif text-sm font-bold">{{ book.title }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ trans('reader.page_of', { current: currentPage, total: totalPages }) }}
                    </p>
                </div>
                <div class="hidden items-center rounded-xl bg-black/5 p-1 dark:bg-white/5 lg:flex">
                    <button
                        type="button"
                        :aria-label="trans('reader.single_page')"
                        :title="trans('reader.single_page')"
                        class="rounded-lg p-2 transition"
                        :class="
                            viewMode === 'single'
                                ? 'bg-white shadow-sm dark:bg-slate-800'
                                : 'opacity-50 hover:opacity-100'
                        "
                        @click="viewMode = 'single'"
                    >
                        <BookOpen class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        :aria-label="trans('reader.two_pages')"
                        :title="trans('reader.two_pages')"
                        class="rounded-lg p-2 transition"
                        :class="
                            viewMode === 'spread'
                                ? 'bg-white shadow-sm dark:bg-slate-800'
                                : 'opacity-50 hover:opacity-100'
                        "
                        @click="viewMode = 'spread'"
                    >
                        <Columns2 class="h-4 w-4" />
                    </button>
                </div>
                <div class="hidden items-center rounded-xl bg-black/5 p-1 sm:flex dark:bg-white/5">
                    <button
                        type="button"
                        :aria-label="trans('reader.decrease_font')"
                        class="rounded-lg p-2 transition hover:bg-white dark:hover:bg-slate-800"
                        @click="decreaseFont"
                    >
                        <Minus class="h-4 w-4" />
                    </button>
                    <span class="w-9 text-center text-xs font-semibold tabular-nums">{{
                        fontSize
                    }}</span>
                    <button
                        type="button"
                        :aria-label="trans('reader.increase_font')"
                        class="rounded-lg p-2 transition hover:bg-white dark:hover:bg-slate-800"
                        @click="increaseFont"
                    >
                        <Plus class="h-4 w-4" />
                    </button>
                </div>
                <button
                    type="button"
                    :aria-label="trans('reader.contents')"
                    class="rounded-xl p-2 transition hover:bg-black/5 dark:hover:bg-white/5"
                    @click="drawer = true"
                >
                    <List class="h-5 w-5" />
                </button>
                <button
                    type="button"
                    :aria-label="trans('theme.toggle')"
                    class="rounded-xl p-2 transition hover:bg-black/5 dark:hover:bg-white/5"
                    @click="toggleDark"
                >
                    <Moon class="h-5 w-5" />
                </button>
            </div>
        </header>

        <main
            class="mx-auto grid min-h-[calc(100vh-8rem)] max-w-7xl gap-px px-3 pb-36 pt-5 sm:px-6 sm:pt-8"
            :class="viewMode === 'spread' ? 'lg:grid-cols-2' : 'lg:max-w-4xl'"
        >
            <article
                v-for="page in visiblePages"
                :key="page.id"
                class="relative whitespace-pre-line rounded-2xl border border-black/5 bg-[#fffdf8] px-6 py-10 font-serif shadow-[0_20px_60px_-35px_rgba(15,23,42,0.35)] sm:px-12 sm:py-14 dark:border-white/5 dark:bg-slate-900"
                :style="{ fontSize: `${fontSize}px`, lineHeight }"
            >
                {{ page.content }}
                <span class="absolute bottom-4 inset-x-0 text-center font-sans text-xs opacity-35">
                    {{ page.page_number }}
                </span>
            </article>
            <p v-if="!visiblePages.length" class="text-center opacity-40">
                {{ trans('reader.page_not_found') }}
            </p>
        </main>

        <footer
            class="fixed inset-x-0 bottom-0 z-20 border-t border-black/10 bg-[#f4efe4]/95 p-3 backdrop-blur-xl dark:border-white/10 dark:bg-slate-950/95"
        >
            <div class="mx-auto flex max-w-4xl items-center gap-3">
                <button
                    type="button"
                    :disabled="!hasPrev"
                    :aria-label="trans('reader.prev_page')"
                    class="rounded-xl border border-black/10 p-3 transition hover:bg-white disabled:opacity-30 dark:border-white/10 dark:hover:bg-slate-800"
                    @click="prevPage"
                >
                    <ChevronLeft class="h-5 w-5" />
                </button>

                <input
                    :value="currentPage"
                    type="range"
                    min="1"
                    :max="totalPages || 1"
                    class="w-full accent-blue-600"
                    :aria-label="trans('reader.page_navigation')"
                    @input="goTo(Number(($event.target as HTMLInputElement).value))"
                />

                <span class="min-w-[4.5rem] text-center text-xs font-semibold tabular-nums">
                    {{ currentPage }} / {{ totalPages }}
                </span>

                <button
                    type="button"
                    :disabled="!hasNext"
                    :aria-label="trans('reader.next_page')"
                    class="rounded-xl border border-black/10 p-3 transition hover:bg-white disabled:opacity-30 dark:border-white/10 dark:hover:bg-slate-800"
                    @click="nextPage"
                >
                    <ChevronRight class="h-5 w-5" />
                </button>
            </div>
            <p class="mt-1 hidden text-center text-[10px] opacity-35 sm:block">
                {{ trans('reader.keyboard_hint') }}
            </p>
        </footer>

        <Drawer :open="drawer" @close="drawer = false">
            <h2 class="font-serif text-lg font-bold">{{ trans('reader.contents') }}</h2>
            <button
                v-for="item in book.pages"
                :key="item.id"
                type="button"
                class="mt-2 block w-full rounded-lg px-3 py-2 text-left text-sm transition hover:bg-slate-100 dark:hover:bg-slate-800"
                :class="{
                    'bg-blue-50 font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300':
                        item.page_number === currentPage,
                }"
                @click="
                    currentPage = item.page_number;
                    drawer = false;
                "
            >
                {{ trans('reader.page', { page: item.page_number }) }}
            </button>
        </Drawer>
    </div>
</template>
