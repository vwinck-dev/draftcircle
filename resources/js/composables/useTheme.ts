import { storeToRefs } from 'pinia';
import { useThemeStore } from '@/stores/theme';

export const useTheme = () => storeToRefs(useThemeStore());
