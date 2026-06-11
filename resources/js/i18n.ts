import type { TranslationTree } from './types';
import { shallowRef } from 'vue';

type TranslationValue = string | TranslationTree;
type Replacements = Record<string, string | number>;

const messages = shallowRef<TranslationTree>({});
const activeLocale = shallowRef('en');

export const setTranslations = (translations: TranslationTree, locale: string): void => {
    messages.value = translations;
    activeLocale.value = locale;
    document.documentElement.lang = locale.replace('_', '-');
};

export const trans = (key: string, replacements: Replacements = {}): string => {
    const value = key.split('.').reduce<TranslationValue | undefined>((current, part) => {
        return typeof current === 'object' ? current[part] : undefined;
    }, messages.value);

    const translated = typeof value === 'string' ? value : key;

    return Object.entries(replacements).reduce(
        (result, [name, replacement]) => result.replaceAll(`:${name}`, String(replacement)),
        translated,
    );
};

export const locale = (): string => activeLocale.value;

export const formatCurrency = (amount: string | number): string => {
    const value = typeof amount === 'string' ? parseFloat(amount) : amount;
    const loc = activeLocale.value;

    if (loc === 'pt_BR') {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
    }
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};
