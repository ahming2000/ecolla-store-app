<script setup lang="ts">
import { getAllDeliveryModes, getDeliveryModeLabel } from '@/enums/DeliveryMode'
import { useCartStore } from '@/stores/cart.store'
import Card from 'primevue/card'
import Select from 'primevue/select'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const cartStore = useCartStore()
const { t } = useI18n()

const deliveryModes = computed(() =>
    getAllDeliveryModes().map((deliveryMode) => ({
        label: getDeliveryModeLabel(t, deliveryMode),
        value: deliveryMode,
    }))
)
</script>

<template>
    <Card>
        <template #title>{{ t('shop.cart.order-mode') }}</template>

        <template #content>
            <Select
                v-model="cartStore.cart.deliveryMode"
                :options="deliveryModes"
                class="w-full"
                id="deliveryMode"
                option-label="label"
                option-value="value"
            />
        </template>
    </Card>
</template>
