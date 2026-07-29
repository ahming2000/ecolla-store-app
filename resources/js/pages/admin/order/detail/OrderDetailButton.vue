<script setup lang="ts">
import { EDITOR, SUPERVISOR } from '@/enums/AccessLevel'
import { DELIVERY } from '@/enums/DeliveryMode'
import { PENDING } from '@/enums/OrderStatus'
import {
    updateOrderStatus,
    updateOrderTrackingNumber,
} from '@/libraries/axios/admin/order'
import { parseFormError } from '@/libraries/axios/common/parser'
import Notification from '@/libraries/primevue/toast/Notification'
import OrderDetailActions from '@/pages/admin/order/detail/OrderDetailActions.vue'
import OrderDetailsTable from '@/pages/admin/order/detail/OrderDetailsTable.vue'
import OrderedItemsTable from '@/pages/admin/order/detail/OrderedItemsTable.vue'
import OrderReceiptDialog from '@/pages/admin/order/detail/OrderReceiptDialog.vue'
import OrderShippingDetails from '@/pages/admin/order/detail/OrderShippingDetails.vue'
import type { AppPageProps, Order, OrderFulfilment } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import { useToast } from 'primevue/usetoast'
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps<{
    order: Order
}>()
const emit = defineEmits<{
    updated: [fulfilment: OrderFulfilment]
}>()

const visible = ref(false)
const receiptVisible = ref(false)
const page = usePage<AppPageProps>()
const toast = Notification.init(useToast())
const { t } = useI18n()

const selectedStatus = ref(props.order.status)
const trackingNo = ref(props.order.tracking_no)
const persistedStatus = ref(props.order.status)
const isUpdatingStatus = ref(false)
const isUpdatingTrackingNumber = ref(false)
const trackingError = ref('')

const receiptSource = computed(() => props.order.receipt_image?.src ?? null)

const canUpdateFulfilment = computed(() => {
    const accessLevel = page.props.auth.user?.access_level ?? -1

    return (
        accessLevel >= SUPERVISOR ||
        (accessLevel === EDITOR && persistedStatus.value === PENDING)
    )
})

const applyFulfilment = (fulfilment: OrderFulfilment): void => {
    persistedStatus.value = fulfilment.status
    selectedStatus.value = fulfilment.status
    trackingNo.value = fulfilment.tracking_no
    emit('updated', fulfilment)
}

const persistTrackingNumber = debounce(
    async (newTrackingNumber: Order['tracking_no']): Promise<void> => {
        if (
            !canUpdateFulfilment.value ||
            newTrackingNumber === props.order.tracking_no
        ) {
            return
        }

        try {
            isUpdatingTrackingNumber.value = true
            trackingError.value = ''

            const fulfilment = await updateOrderTrackingNumber(
                props.order.id,
                newTrackingNumber
            )

            applyFulfilment(fulfilment)
            toast.success(
                t('admin.orders.tracking-updated-success'),
                t('common.notifications.success')
            )
        } catch (error) {
            const errors = parseFormError(error)

            trackingNo.value = props.order.tracking_no
            trackingError.value = errors.tracking_no ?? ''

            if (Object.keys(errors).length === 0) {
                console.error(error)
            }

            toast.axiosError(
                error,
                t('admin.orders.tracking-update-failed'),
                t('common.notifications.error')
            )
        } finally {
            isUpdatingTrackingNumber.value = false
        }
    },
    1000
)

const onStatusChange = async (): Promise<void> => {
    if (
        !canUpdateFulfilment.value ||
        selectedStatus.value === persistedStatus.value
    ) {
        return
    }

    try {
        isUpdatingStatus.value = true
        trackingError.value = ''

        await persistTrackingNumber.flush()

        const fulfilment = await updateOrderStatus(
            props.order.id,
            selectedStatus.value,
            trackingNo.value
        )

        applyFulfilment(fulfilment)
        toast.success(
            t('admin.orders.status-updated-success'),
            t('common.notifications.success')
        )
    } catch (error) {
        const errors = parseFormError(error)

        selectedStatus.value = persistedStatus.value
        trackingError.value = errors.tracking_no ?? ''

        if (Object.keys(errors).length === 0) {
            console.error(error)
        }

        toast.error(
            trackingError.value
                ? t('admin.orders.tracking-required')
                : t('admin.orders.status-update-failed'),
            t('common.notifications.error')
        )
    } finally {
        isUpdatingStatus.value = false
    }
}

const onViewReceipt = (): void => {
    if (receiptSource.value) {
        receiptVisible.value = true
    }
}

watch(trackingNo, (newTrackingNumber) => {
    trackingError.value = ''

    if (newTrackingNumber !== props.order.tracking_no) {
        void persistTrackingNumber(newTrackingNumber)
    }
})

watch(
    () => props.order.status,
    (status) => {
        persistedStatus.value = status
        selectedStatus.value = status
    }
)

watch(
    () => props.order.tracking_no,
    (trackingNumber) => {
        trackingNo.value = trackingNumber
    }
)

onBeforeUnmount(() => {
    persistTrackingNumber.cancel()
})
</script>

<template>
    <div class="inline-block">
        <Button
            :label="t('common.actions.details')"
            icon="pi pi-info-circle"
            size="small"
            @click="visible = true"
        />

        <Dialog
            v-model:visible="visible"
            :draggable="false"
            :header="order.reference_num"
            class="h-full w-full"
            modal
        >
            <div class="grid grid-cols-1 lg:grid-cols-3">
                <div class="mx-3 mb-4 overflow-y-auto lg:mb-0">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="my-auto text-2xl">
                            {{ t('admin.orders.order-details') }}
                        </div>

                        <OrderDetailActions
                            :has-receipt="!!receiptSource"
                            :order="order"
                            @view-receipt="onViewReceipt"
                        />
                    </div>

                    <OrderDetailsTable
                        v-model:status="selectedStatus"
                        :can-update-fulfilment="canUpdateFulfilment"
                        :is-updating-status="isUpdatingStatus"
                        :order="order"
                        @status-change="onStatusChange"
                    />

                    <OrderShippingDetails
                        v-if="order.delivery_mode === DELIVERY"
                        v-model:tracking-number="trackingNo"
                        :can-update-fulfilment="canUpdateFulfilment"
                        :is-updating-tracking-number="isUpdatingTrackingNumber"
                        :order="order"
                        :tracking-error="trackingError"
                    />
                </div>

                <OrderedItemsTable :items="order.items" />
            </div>
        </Dialog>

        <OrderReceiptDialog
            v-model:visible="receiptVisible"
            :order="order"
            :receipt-source="receiptSource"
        />
    </div>
</template>
