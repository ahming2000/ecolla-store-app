import '../css/app.css'

import en from '@/langs/en'
import zh from '@/langs/zh'
import {
    ADMIN_LANGUAGE_STORAGE_KEY,
    DEFAULT_LANGUAGE,
    getStoredLanguage,
    SHOP_LANGUAGE_STORAGE_KEY,
} from '@/libraries/i18n/language'
import type { AppPageProps } from '@/types'
import { createInertiaApp, router } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createPinia } from 'pinia'
import { StyleClass } from 'primevue'
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import { createApp, h, type DefineComponent } from 'vue'
import { createI18n } from 'vue-i18n'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'
const primeUiLicenseKey = import.meta.env.VITE_PRIMEUI_LICENSE_KEY

const i18n = createI18n({
    legacy: false,
    locale: DEFAULT_LANGUAGE,
    fallbackLocale: 'en',
    messages: { zh, en },
})

interface InertiaPage {
    component: string
    props: unknown
}

const syncLanguage = (page: InertiaPage): void => {
    const pageProps = page.props as Partial<AppPageProps>
    const isShopPage =
        page.component.startsWith('shop/') || page.component === 'error/Shop'
    const isGuestAdminPage =
        !pageProps.auth?.user &&
        (page.component.startsWith('admin/') ||
            page.component === 'error/Admin')
    const storedLanguage = isShopPage
        ? getStoredLanguage(SHOP_LANGUAGE_STORAGE_KEY)
        : isGuestAdminPage
          ? getStoredLanguage(ADMIN_LANGUAGE_STORAGE_KEY)
          : null
    const language = isShopPage
        ? (storedLanguage ?? DEFAULT_LANGUAGE)
        : (storedLanguage ?? pageProps.auth?.user?.lang ?? DEFAULT_LANGUAGE)

    i18n.global.locale.value = language
    document.documentElement.lang = language
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        syncLanguage(props.initialPage)

        router.on('navigate', (event) => {
            syncLanguage(event.detail.page)
        })

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(createPinia())
            .use(PrimeVue, {
                license: primeUiLicenseKey,
                theme: 'none',
            })
            .use(ToastService)
            .directive('styleclass', StyleClass)
            .use(i18n)
            .mount(el)
    },
    progress: {
        color: '#4B5563',
    },
})
