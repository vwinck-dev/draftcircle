import type { Book } from '@/types/models';

export type RecommendationConsent = 'accepted' | 'declined' | null;

export interface CookieConsents {
    recommendations: boolean;
    analytics: boolean;
}

const consentCookie = 'library_cookie_consent';
const analyticsCookie = 'library_analytics_consent';
const interestsCookie = 'library_recommendation_interests';
const cookieLifetime = 60 * 60 * 24 * 180;

interface RecommendationInterests {
    categories: Record<string, number>;
    books: number[];
}

const cookieValue = (name: string): string | null => {
    const prefix = `${name}=`;
    const cookie = document.cookie.split('; ').find((item) => item.startsWith(prefix));

    return cookie ? cookie.slice(prefix.length) : null;
};

const writeCookie = (name: string, value: string, maxAge = cookieLifetime): void => {
    const secure = location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = `${name}=${value}; path=/; max-age=${maxAge}; SameSite=Lax${secure}`;
};

const readInterests = (): RecommendationInterests => {
    const value = cookieValue(interestsCookie);

    if (!value) {
        return { categories: {}, books: [] };
    }

    try {
        const parsed = JSON.parse(decodeURIComponent(value)) as Partial<RecommendationInterests>;

        return {
            categories:
                parsed.categories && typeof parsed.categories === 'object' ? parsed.categories : {},
            books: Array.isArray(parsed.books) ? parsed.books : [],
        };
    } catch {
        return { categories: {}, books: [] };
    }
};

const writeInterests = (interests: RecommendationInterests): void => {
    writeCookie(interestsCookie, encodeURIComponent(JSON.stringify(interests)));
};

export const recommendationConsent = (): RecommendationConsent => {
    const consent = cookieValue(consentCookie);

    return consent === 'accepted' || consent === 'declined' ? consent : null;
};

export const analyticsConsent = (): boolean => cookieValue(analyticsCookie) === 'true';

export const hasSetConsent = (): boolean => cookieValue(consentCookie) !== null;

export const allConsents = (): CookieConsents => ({
    recommendations: recommendationConsent() === 'accepted',
    analytics: analyticsConsent(),
});

export const setRecommendationConsent = (consent: Exclude<RecommendationConsent, null>): void => {
    writeCookie(consentCookie, consent);

    if (consent === 'declined') {
        writeCookie(interestsCookie, '', 0);
    }
};

export const saveConsents = (consents: CookieConsents): void => {
    setRecommendationConsent(consents.recommendations ? 'accepted' : 'declined');
    writeCookie(analyticsCookie, String(consents.analytics));

    if (!consents.recommendations) {
        writeCookie(interestsCookie, '', 0);
    }
};

export const trackCategoryInterest = (categoryId: number): void => {
    if (recommendationConsent() !== 'accepted') {
        return;
    }

    const interests = readInterests();
    const categoryKey = String(categoryId);
    interests.categories[categoryKey] = Math.min((interests.categories[categoryKey] ?? 0) + 1, 9);

    const categoryEntries = Object.entries(interests.categories)
        .sort(([, left], [, right]) => right - left)
        .slice(0, 8);
    interests.categories = Object.fromEntries(categoryEntries);

    writeInterests(interests);
};

export const trackBookInterest = (book: Book): void => {
    if (recommendationConsent() !== 'accepted') {
        return;
    }

    const interests = readInterests();
    interests.books = [book.id, ...interests.books.filter((bookId) => bookId !== book.id)].slice(
        0,
        12,
    );
    writeInterests(interests);

    book.categories.forEach((category) => trackCategoryInterest(category.id));
};

export const openCookiePreferences = (): void => {
    globalThis.dispatchEvent(new CustomEvent('library:open-cookie-preferences'));
};
