<script setup lang="ts">
import DeliveryTrackingInput from '@/pages/admin/order/detail/DeliveryTrackingInput.vue'
import type { Order } from '@/types'
import { useI18n } from 'vue-i18n'

withDefaults(
    defineProps<{
        canUpdateFulfilment?: boolean
        isUpdatingTrackingNumber?: boolean
        order: Order
        trackingError?: string
        trackingNumber: Order['tracking_no']
    }>(),
    {
        canUpdateFulfilment: false,
        isUpdatingTrackingNumber: false,
        trackingError: '',
    }
)

const emit = defineEmits<{
    'update:trackingNumber': [value: Order['tracking_no']]
}>()

const { t } = useI18n()
</script>

<template>
    <div>
        <div class="mb-3 mt-4 text-2xl">
            {{ t('admin.orders.shipping-information') }}
        </div>

        <table class="w-full table-auto">
            <tbody>
                <tr>
                    <td class="border border-slate-200 p-3">
                        {{ t('admin.orders.delivery-tracking-id') }}
                    </td>
                    <td class="border border-slate-200 p-3">
                        <DeliveryTrackingInput
                            :disabled="
                                !canUpdateFulfilment || isUpdatingTrackingNumber
                            "
                            :error="trackingError"
                            :model-value="trackingNumber"
                            :order-id="order.id"
                            @update:model-value="
                                (value) => emit('update:trackingNumber', value)
                            "
                        />
                    </td>
                </tr>

                <tr>
                    <td class="border border-slate-200 p-3">
                        {{ t('admin.orders.customer-name') }}
                    </td>
                    <td class="border border-slate-200 p-3">
                        {{ order.cus_name }}
                    </td>
                </tr>

                <tr>
                    <td class="border border-slate-200 p-3">
                        {{ t('admin.orders.customer-phone') }}
                    </td>
                    <td class="border border-slate-200 p-3">
                        {{ order.cus_phone }}
                    </td>
                </tr>

                <tr>
                    <td class="border border-slate-200 p-3">
                        {{ t('admin.orders.customer-address') }}
                    </td>
                    <td class="border border-slate-200 p-3">
                        {{ order.cus_address }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
