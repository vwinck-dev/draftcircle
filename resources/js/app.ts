import '../css/app.css';
import 'sweetalert2/dist/sweetalert2.min.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { createPinia } from 'pinia';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { setTranslations, trans } from './i18n';
import { showToast } from './composables/useSweetAlert';
import { useNotificationStore } from './stores/notifications';
import { useThemeStore } from './stores/theme';
import type { PageProps } from './types';

const appName = import.meta.env.VITE_APP_NAME || '';
const pinia = createPinia();

const showFlash = (pageProps: PageProps) => {
    if (pageProps.flash.error) {
        void showToast(pageProps.flash.error, 'error');
    } else if (pageProps.flash.success) {
        void showToast(pageProps.flash.success);
    }
};

createInertiaApp({
    title: (title) => (appName ? `${title} - ${appName}` : title),
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const pageProps = props.initialPage.props as PageProps;
        setTranslations(pageProps.i18n.translations, pageProps.i18n.locale);
        showFlash(pageProps);

        router.on('success', (event) => {
            const nextProps = event.detail.page.props as PageProps;
            setTranslations(nextProps.i18n.translations, nextProps.i18n.locale);
            showFlash(nextProps);

            // Page props now include the fresh unread count — reset local accumulator
            useNotificationStore().resetExtra();
        });

        useThemeStore(pinia);

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .mount(el);

        // Subscribe to the authenticated user's private notification channel
        if (pageProps.auth.user) {
            const userId = pageProps.auth.user.id;
            const store = useNotificationStore();

            globalThis.window.Echo.private(`App.Models.User.${userId}`).notification(
                (notification: {
                    message_key?: string;
                    message_params?: Record<string, string>;
                }) => {
                    store.increment();

                    if (notification.message_key) {
                        void showToast(
                            trans(notification.message_key, notification.message_params ?? {}),
                        );
                    }
                },
            );
        }
    },
    progress: {
        color: '#2563eb',
    },
});
