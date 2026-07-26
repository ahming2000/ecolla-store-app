<script setup lang="ts">
import { checkoutPage } from '@/routes/shop/cart'
import { useCartStore } from '@/stores/cart.store'
import { router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Card from 'primevue/card'
import { useI18n } from 'vue-i18n'

const cartStore = useCartStore()
const { t } = useI18n()

const onCheckout = (): void => {
    router.visit(checkoutPage())
}
</script>

<template>
    <Card>
        <template #title>{{ t('shop.cart.summary.title') }}</template>

        <template #content>
            <table class="table-auto w-full mb-3">
                <tbody>
                    <tr>
                        <td>{{ t('shop.cart.summary.subtotal') }}</td>
                        <td class="text-end">
                            {{ cartStore.subtotalText }}
                        </td>
                    </tr>

                    <tr>
                        <td>{{ t('shop.cart.summary.shipping') }}</td>
                        <td class="text-end">
                            {{ cartStore.shippingFeeText }}
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <hr class="my-2" />
                        </td>
                    </tr>

                    <tr>
                        <td>{{ t('shop.cart.summary.total') }}</td>
                        <td class="text-end">
                            {{ cartStore.totalText }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <Button
                :disabled="cartStore.isEmpty"
                :label="t('shop.cart.summary.checkout')"
                class="w-full"
                @click="onCheckout"
            />
        </template>
    </Card>
</template>
