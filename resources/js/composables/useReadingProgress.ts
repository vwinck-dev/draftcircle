import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useDebounce } from './useDebounce';

export const useReadingProgress = (bookId: number, initialPage: number) => {
    const currentPage = ref(initialPage);
    const save = useDebounce(
        () =>
            router.put(
                route('books.progress', bookId),
                { page: currentPage.value },
                { preserveScroll: true },
            ),
        2000,
    );
    watch(currentPage, save);

    return { currentPage };
};
