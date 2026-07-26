<script setup lang="ts">
import Shop from '@/layouts/Shop.vue'
import CartConfigCard from '@/pages/shop/cart/CartConfigCard.vue'
import CartItemCard from '@/pages/shop/cart/CartItemCard.vue'
import CartSummaryCard from '@/pages/shop/cart/CartSummaryCard.vue'
import ContactInfoCard from '@/pages/shop/cart/ContactInfoCard.vue'
import DeliveryNoteCard from '@/pages/shop/cart/DeliveryNoteCard.vue'
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
        <Head :title="t('shop.cart.title')" />

        <div class="container mx-auto my-3">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                <div class="lg:col-span-2 space-y-3">
                    <CartItemCard />

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                        <DeliveryNoteCard />
                        <ContactInfoCard />
                    </div>
                </div>

                <div class="space-y-3">
                    <CartConfigCard />
                    <CartSummaryCard />
                </div>
            </div>
        </div>
    </div>
</template>
