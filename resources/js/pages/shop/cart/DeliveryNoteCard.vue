<script setup lang="ts">
import { useCartStore } from '@/stores/cart.store'
import Card from 'primevue/card'
import { useI18n } from 'vue-i18n'

const cartStore = useCartStore()
const { t } = useI18n()
</script>

<template>
    <Card>
        <template #content>
            <div class="mb-3 space-y-1">
                <div class="text-2xl font-bold">
                    {{ t('shop.cart.pickup.title') }}
                </div>
                <p>{{ t('shop.cart.pickup.description') }}</p>
            </div>

            <div class="space-y-1">
                <div class="text-2xl font-bold flex items-center space-x-2">
                    <span>{{ t('shop.cart.delivery.title') }}</span>

                    <span class="text-sm text-red-700">
                        ({{ t('shop.cart.delivery.cash-on-delivery') }})
                    </span>
                </div>

                <div>{{ t('shop.cart.delivery.within-distance') }}</div>
                <div>{{ t('shop.cart.delivery.outside-distance') }}</div>
                <div data-testid="configured-shipping-fee">
                    {{
                        t('shop.cart.delivery.fee', {
                            amount: cartStore.configuredShippingFeeText,
                        })
                    }}
                </div>
                <div
                    v-if="
                        cartStore.shippingSettings.freeShipping.isActivated &&
                        cartStore.shippingSettings.freeShipping.description
                    "
                    class="text-green-700"
                    data-testid="free-shipping-description"
                >
                    {{ cartStore.shippingSettings.freeShipping.description }}
                </div>

                <div class="font-bold">
                    {{ t('shop.cart.delivery.schedule') }}
                </div>
                <div>{{ t('shop.cart.delivery.before-three') }}</div>
                <div>{{ t('shop.cart.delivery.after-three') }}</div>
            </div>
        </template>
    </Card>
</template>
