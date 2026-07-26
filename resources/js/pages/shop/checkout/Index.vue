<script setup lang="ts">
import Shop from '@/layouts/Shop.vue'
import CartItemListingCard from '@/pages/shop/checkout/CartItemListingCard.vue'
import CheckOutFormCard from '@/pages/shop/checkout/CheckOutFormCard.vue'
import { useCartStore } from '@/stores/cart.store'
import type { ShippingSettings } from '@/types'
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: Shop })

const props = defineProps<{
    shipping: ShippingSettings
}>()

const cartStore = useCartStore()
cartStore.setShippingSettings(props.shipping)

const { t } = useI18n()
</script>

<template>
    <div>
        <Head :title="t('shop.checkout.title')" />

        <div class="container mx-auto w-full flex flex-wrap">
            <div
                class="w-full p-3 md:w-8/12 md:mx-auto lg:w-6/12 lg:mx-auto space-y-3"
            >
                <div class="text-3xl font-bold">
                    {{ t('shop.checkout.title') }}
                </div>
                <CartItemListingCard />
                <CheckOutFormCard />
            </div>
        </div>
    </div>
</template>
