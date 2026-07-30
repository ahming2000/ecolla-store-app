<script setup lang="ts">
import UserDateTime from '@/components/UserDateTime.vue'
import { getDeliveryModeLabel } from '@/enums/DeliveryMode'
import OrderStatusSelect from '@/pages/admin/order/detail/OrderStatusSelect.vue'
import type { Order } from '@/types'
import { useI18n } from 'vue-i18n'

withDefaults(
    defineProps<{
        canUpdateFulfilment?: boolean
        isUpdatingStatus?: boolean
        order: Order
        status: Order['status']
    }>(),
    {
        canUpdateFulfilment: false,
        isUpdatingStatus: false,
    }
)

const emit = defineEmits<{
    statusChange: []
    'update:status': [value: Order['status']]
}>()

const { t } = useI18n()
</script>

<template>
    <table class="w-full table-auto">
        <tbody>
            <tr>
                <td class="border border-slate-200 p-3">
                    {{ t('admin.orders.columns.date') }}
                </td>
                <td class="border border-slate-200 p-3">
                    <UserDateTime :value="order.created_at" />
                </td>
            </tr>

            <tr>
                <td class="border border-slate-200 p-3">
                    {{ t('admin.orders.columns.reference') }}
                </td>
                <td class="border border-slate-200 p-3">
                    {{ order.reference_num }}
                </td>
            </tr>

            <tr>
                <td class="border border-slate-200 p-3">
                    {{ t('admin.orders.columns.delivery-mode') }}
                </td>
                <td class="border border-slate-200 p-3">
                    {{ getDeliveryModeLabel(t, order.delivery_mode) }}
                </td>
            </tr>

            <tr>
                <td class="border border-slate-200 p-3">
                    {{ t('admin.orders.columns.status') }}
                </td>
                <td class="border border-slate-200 p-3">
                    <OrderStatusSelect
                        :disabled="!canUpdateFulfilment || isUpdatingStatus"
                        :loading="isUpdatingStatus"
                        :model-value="status"
                        :order-id="order.id"
                        @change="emit('statusChange')"
                        @update:model-value="
                            (value) => emit('update:status', value)
                        "
                    />
                </td>
            </tr>

            <tr>
                <td class="border border-slate-200 p-3">
                    {{ t('admin.orders.columns.payment-method') }}
                </td>
                <td class="border border-slate-200 p-3">
                    {{ order.payment_method.name }}
                </td>
            </tr>

            <tr>
                <td class="border border-slate-200 p-3">
                    {{ t('admin.orders.shipping-fee') }}
                </td>
                <td class="border border-slate-200 p-3">
                    RM {{ order.shipping_fee }}
                </td>
            </tr>

            <tr>
                <td class="border border-slate-200 p-3">
                    {{ t('admin.orders.note') }}
                </td>
                <td class="border border-slate-200 p-3">
                    {{ order.note }}
                </td>
            </tr>
        </tbody>
    </table>
</template>
