<script setup lang="ts">
import EmptyCartPlaceholder from '@/components/placeholder/EmptyCartPlaceholder.vue'
import CartItemRow from '@/pages/shop/cart/CartItemRow.vue'
import { useCartStore } from '@/stores/cart.store'
import Button from 'primevue/button'
import Card from 'primevue/card'
import { useI18n } from 'vue-i18n'

const cartStore = useCartStore()
const { t } = useI18n()
</script>

<template>
    <Card>
        <template #title>
            <div class="flex justify-between items-center">
                <div>
                    {{
                        t('shop.cart.item-count', {
                            count: cartStore.itemCount,
                        })
                    }}
                </div>

                <Button
                    :disabled="cartStore.isEmpty"
                    :label="t('shop.cart.clear')"
                    icon="pi pi-times"
                    outlined
                    severity="danger"
                    size="small"
                    @click="cartStore.reset"
                />
            </div>
        </template>

        <template #content>
            <EmptyCartPlaceholder v-if="cartStore.isEmpty" />

            <div v-else class="space-y-3">
                <CartItemRow
                    v-for="cartItem in cartStore.cart.items"
                    :key="cartItem.variation.id"
                    :cart-item="cartItem"
                />
            </div>
        </template>
    </Card>
</template>
