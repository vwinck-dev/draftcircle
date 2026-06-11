import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useThemeStore = defineStore('theme', () => {
    const theme = ref(localStorage.getItem('theme') ?? 'dark');

    const apply = () => {
        const dark =
            theme.value === 'dark' ||
            (theme.value === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList.toggle('dark', dark);
    };

    watch(theme, () => {
        localStorage.setItem('theme', theme.value);
        apply();
    });

    apply();

    return { theme };
});
