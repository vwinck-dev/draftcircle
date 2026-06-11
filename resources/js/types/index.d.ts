import type { Book, User } from './models';

export interface TranslationTree {
    [key: string]: string | TranslationTree;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User | null;
    };
    notifications: {
        unread: number;
    };
    recommendations: {
        personalized: boolean;
        books: Book[];
    };
    flash: {
        success?: string;
        error?: string;
    };
    i18n: {
        locale: string;
        supported: string[];
        translations: TranslationTree;
    };
};
