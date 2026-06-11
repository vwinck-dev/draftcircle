import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useDebounce } from './useDebounce';

export const useSearch = (initial = '') => {
    const search = ref(initial);
    const visit = useDebounce(
        () =>
            router.get(
                route('books.index'),
                { search: search.value },
                { preserveState: true, replace: true },
            ),
        300,
    );
    watch(search, visit);

    return { search };
};
