<script setup lang="ts">
import fallbackImage from '@/assets/images/branding/ecolla.png'
import { useCartStore } from '@/stores/cart.store'
import type { CartItemData } from '@/types'
import Card from 'primevue/card'
import { useI18n } from 'vue-i18n'

const cartStore = useCartStore()
const { locale, t } = useI18n()

const getItemName = (cartItem: CartItemData): string => {
    return locale.value === 'en' && cartItem.item.name_en
        ? cartItem.item.name_en
        : cartItem.item.name
}

const getVariationName = (cartItem: CartItemData): string => {
    return locale.value === 'en' && cartItem.variation.name_en
        ? cartItem.variation.name_en
        : cartItem.variation.name
}
</script>

<template>
    <Card>
        <template #title>{{ t('shop.checkout.items-title') }}</template>

        <template #content>
            <div class="flex flex-col gap-1">
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-2">
                    <div
                        v-for="cartItem in cartStore.cart.items"
                        :key="cartItem.variation.id"
                        class="grid grid-cols-3 gap-2"
                    >
                        <div>
                            <img
                                class="rounded-lg"
                                :src="
                                    cartItem.variation.image?.src ??
                                    cartItem.item.cover_image ??
                                    fallbackImage
                                "
                                :alt="
                                    t('shop.cart.item.image-alt', {
                                        name: getVariationName(cartItem),
                                    })
                                "
                            />
                        </div>

                        <div class="col-span-2">
                            <span class="font-bold truncate">
                                {{ getItemName(cartItem) }}
                            </span>

                            <div class="flex justify-between gap-1">
                                <span class="text-gray-500 truncate">
                                    {{ getVariationName(cartItem) }}
                                </span>

                                <span>x{{ cartItem.quantity }}</span>
                            </div>

                            <div class="flex justify-end">
                                <span class="text-gray-500">
                                    {{
                                        cartItem.variation.sale_price ??
                                        cartItem.variation.price
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template #footer>
            <div
                v-if="cartStore.shippingFee !== 0"
                class="flex justify-between"
            >
                <span class="text-gray-500">
                    {{ t('shop.checkout.shipping') }}
                </span>

                <span class="text-gray-500">
                    {{ cartStore.shippingFeeText }}
                </span>
            </div>

            <div class="flex justify-between">
                <span class="font-bold">{{ t('shop.checkout.total') }}</span>

                <span class="font-bold">
                    {{ cartStore.totalText }}
                </span>
            </div>
        </template>
    </Card>
</template>
