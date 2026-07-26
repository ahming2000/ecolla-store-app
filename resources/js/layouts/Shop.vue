<script setup lang="ts">
import logoImage from '@/assets/images/branding/ecolla.png'
import LanguageSwitcher from '@/components/language/LanguageSwitcher.vue'
import Base from '@/layouts/common/Base.vue'
import { SHOP_LANGUAGE_STORAGE_KEY } from '@/libraries/i18n/language'
import { page as adminDashboardPage } from '@/routes/admin/dashboard'
import { page as cartPage } from '@/routes/shop/cart'
import { page as itemPage } from '@/routes/shop/item'
import { page as landingPage } from '@/routes/shop/landing'
import { page as paymentMethodPage } from '@/routes/shop/payment-method'
import { useCartStore } from '@/stores/cart.store'
import { useItemStore } from '@/stores/item.store'
import type { MenuItem } from '@/types'
import { router } from '@inertiajs/vue3'
import { onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const cartStore = useCartStore()
const itemStore = useItemStore()
const { locale: activeLanguage, t } = useI18n()

const menuItems = ref<MenuItem[]>([])

const buildMenuItems = (): MenuItem[] => {
    return [
        {
            label: t('landing.layout.navigation.home'),
            icon: 'pi pi-home',
            command: () => {
                router.visit(landingPage())
            },
        },
        {
            label: t('landing.layout.navigation.items'),
            icon: 'pi pi-list',
            command: () => {
                router.visit(itemPage())
            },
        },
        {
            label: t('landing.layout.navigation.payment-methods'),
            icon: 'pi pi-wallet',
            command: () => {
                router.visit(paymentMethodPage())
            },
        },
        {
            label: t('landing.layout.navigation.cart'),
            icon: 'pi pi-shopping-cart',
            badge: cartStore.itemCount,
            command: () => {
                router.visit(cartPage())
            },
        },
        {
            label: t('common.language.label'),
            slot: 'language-switcher',
        },
    ]
}

watch(
    () => [cartStore.itemCount, activeLanguage.value],
    () => {
        menuItems.value = buildMenuItems()
    },
    { immediate: true }
)

onMounted(() => {
    cartStore.init()
    itemStore.initShopPage()
})
</script>

<template>
    <Base :menu-items="menuItems" :brand-href="landingPage.url()">
        <template #language-switcher>
            <LanguageSwitcher
                persistence="local"
                :storage-key="SHOP_LANGUAGE_STORAGE_KEY"
            />
        </template>

        <slot></slot>

        <template #footer>
            <footer class="text-white">
                <div class="p-4 bg-[#3c3e44]">
                    <div class="container mx-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d994.610069720882!2d101.14378741460057!3d4.328107998352038!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cae2bc71984c77%3A0x85e97a678280a2b!2s2365%2C%20Jalan%20Hala%20Timah%203%2C%20Taman%20Kolej%20Perdana%2C%2031900%20Kampar%2C%20Negeri%20Perak!5e0!3m2!1sen!2smy!4v1603436959003!5m2!1sen!2smy"
                                width="100%"
                                height="200"
                                style="border: 0"
                                referrerpolicy="no-referrer-when-downgrade"
                            >
                            </iframe>

                            <div class="grid grid-cols-1 md:grid-cols-2">
                                <div class="mb-4 md:mb-0">
                                    <div class="text-2xl font-bold">
                                        {{
                                            t('landing.layout.footer.location')
                                        }}
                                    </div>

                                    <hr
                                        class="border-0 border-t-2 border-[#F02B73] w-[90px] mb-[20px]"
                                    />

                                    <div>
                                        <div>{{ t('shop.address.line1') }}</div>
                                        <div>{{ t('shop.address.line2') }}</div>
                                        <div>{{ t('shop.address.line3') }}</div>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-2xl font-bold">
                                        {{ t('landing.layout.footer.contact') }}
                                    </h4>

                                    <hr
                                        class="border-0 border-t-2 border-[#F02B73] w-[90px] mb-[20px]"
                                    />

                                    <a
                                        class="block"
                                        href="https://www.facebook.com/Ecolla-e%E5%8F%A3%E4%B9%90-2347940035424278"
                                        target="_blank"
                                    >
                                        <i class="pi pi-facebook"></i>
                                        <span class="ml-1">
                                            {{
                                                t(
                                                    'landing.layout.footer.facebook'
                                                )
                                            }}
                                        </span>
                                    </a>

                                    <a
                                        class="block"
                                        href="https://wa.link/fcfum1"
                                        target="_blank"
                                    >
                                        <i class="pi pi-whatsapp"></i>
                                        <span class="ml-1">
                                            {{
                                                t(
                                                    'landing.layout.footer.whatsapp'
                                                )
                                            }}
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="m-0 py-3 bg-[#303136]">
                    <div class="flex justify-center items-center">
                        <img
                            :src="logoImage"
                            :style="{ width: '20px', height: '20px' }"
                            :alt="t('common.alt.logo')"
                            loading="lazy"
                        />

                        <a class="ml-2" :href="adminDashboardPage.url()">
                            {{ t('shop.brand') }}
                        </a>
                    </div>
                </div>
            </footer>

            <a
                href="https://wa.link/fcfum1"
                :title="t('landing.layout.footer.whatsapp-title')"
                target="_blank"
                class="w-15 h-15 bg-[#2fe577] flex items-center justify-center text-white text-3xl rounded-full shadow-md fixed right-5 bottom-5 z-50 transition-colors duration-200 cursor-pointer focus:outline-blue-500 active:bg-gray-500"
            >
                <i class="pi pi-whatsapp"></i>
            </a>
        </template>
    </Base>
</template>
