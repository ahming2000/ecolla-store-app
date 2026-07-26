<script setup lang="ts">
import LanguageSwitcher from '@/components/language/LanguageSwitcher.vue'
import Base from '@/layouts/common/Base.vue'
import { ADMIN_LANGUAGE_STORAGE_KEY } from '@/libraries/i18n/language'
import { logout } from '@/routes'
import { page as changingLogPage } from '@/routes/admin/changing-log'
import { page as dashboardPage } from '@/routes/admin/dashboard'
import { page as itemPage } from '@/routes/admin/item'
import { page as orderPage } from '@/routes/admin/order'
import { page as profilePage } from '@/routes/admin/profile'
import { page as settingPage } from '@/routes/admin/setting'
import { page as userPage } from '@/routes/admin/user'
import { page as wikiPage } from '@/routes/admin/wiki'
import { page as loginPage } from '@/routes/login'
import { page as shopLandingPage } from '@/routes/shop/landing'
import type { AppPageProps, MenuItem } from '@/types'
import { router, useForm, usePage } from '@inertiajs/vue3'
import { computed, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const menuItems = ref<MenuItem[]>([])

const form = useForm({})
const { locale: activeLanguage, t } = useI18n()

const user = computed(() => {
    return usePage<AppPageProps>().props.auth.user
})

const isLogin = computed(() => {
    return !!user.value
})

const getLanguageMenuItem = (): MenuItem => {
    return {
        label: t('common.language.label'),
        slot: 'language-switcher',
    }
}

const getGuestMenuItems = (): MenuItem[] => {
    return [
        {
            label: t('admin.layout.navigation.login'),
            icon: 'pi pi-sign-in',
            command: () => router.visit(loginPage()),
        },
        {
            label: t('admin.layout.navigation.change-log'),
            icon: 'pi pi-book',
            command: () => router.visit(changingLogPage()),
        },
        getLanguageMenuItem(),
    ]
}

const getAuthMenuItems = (): MenuItem[] => {
    return [
        {
            label: t('admin.layout.navigation.dashboard'),
            icon: 'pi pi-home',
            command: () => {
                router.visit(dashboardPage())
            },
        },
        {
            label: t('admin.layout.navigation.items'),
            icon: 'pi pi-list',
            command: () => {
                router.visit(itemPage())
            },
        },
        {
            label: t('admin.layout.navigation.orders'),
            icon: 'pi pi-shopping-bag',
            command: () => {
                router.visit(orderPage())
            },
        },
        {
            label: t('admin.layout.navigation.wiki'),
            icon: 'pi pi-question-circle',
            command: () => {
                router.visit(wikiPage())
            },
        },
        {
            label: t('admin.layout.navigation.shop'),
            icon: 'pi pi-shop',
            command: () => {
                router.visit(shopLandingPage())
            },
        },
        getLanguageMenuItem(),
        {
            label: user.value?.username ?? '',
            items: [
                {
                    label: t('admin.layout.navigation.profile'),
                    icon: 'pi pi-user',
                    command: () => {
                        router.visit(profilePage())
                    },
                },
                {
                    label: t('admin.layout.navigation.users'),
                    icon: 'pi pi-users',
                    command: () => {
                        router.visit(userPage())
                    },
                },
                {
                    label: t('admin.layout.navigation.settings'),
                    icon: 'pi pi-cog',
                    command: () => {
                        router.visit(settingPage())
                    },
                },
                {
                    label: t('admin.layout.navigation.change-log'),
                    icon: 'pi pi-book',
                    command: () => {
                        router.visit(changingLogPage())
                    },
                },
                {
                    label: t('admin.layout.navigation.logout'),
                    icon: 'pi pi-sign-out',
                    command: () => {
                        form.submit(logout())
                    },
                },
            ],
        },
    ]
}

watch(
    () => [isLogin.value, activeLanguage.value],
    () => {
        if (isLogin.value) {
            menuItems.value = getAuthMenuItems()
        } else {
            menuItems.value = getGuestMenuItems()
        }
    }
)

onMounted(() => {
    if (isLogin.value) {
        menuItems.value = getAuthMenuItems()
    } else {
        menuItems.value = getGuestMenuItems()
    }
})
</script>

<template>
    <Base
        :brand-name="t('admin.layout.brand')"
        :brand-href="dashboardPage.url()"
        :menu-items="menuItems"
    >
        <template #language-switcher>
            <LanguageSwitcher
                :persistence="isLogin ? 'server' : 'local'"
                :storage-key="ADMIN_LANGUAGE_STORAGE_KEY"
            />
        </template>

        <slot></slot>
    </Base>
</template>
