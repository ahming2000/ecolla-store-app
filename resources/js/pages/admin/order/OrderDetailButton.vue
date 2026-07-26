<script setup lang="ts">
import { EDITOR, SUPERVISOR } from '@/enums/AccessLevel'
import { DELIVERY, getDeliveryModeLabel } from '@/enums/DeliveryMode'
import {
    getOrderStatuses,
    getOrderStatusLabel,
    PENDING,
} from '@/enums/OrderStatus'
import {
    updateOrderStatus,
    updateOrderTrackingNumber,
} from '@/libraries/axios/admin/order'
import { parseFormError } from '@/libraries/axios/common/parser'
import { getLocalizedName } from '@/libraries/i18n/language'
import Notification from '@/libraries/primevue/toast/Notification'
import { download as downloadOrder } from '@/routes/admin/order'
import type { AppPageProps, Order, OrderFulfilment } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import Button from 'primevue/button'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
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
const { locale: activeLanguage, t } = useI18n()

const selectedStatus = ref(props.order.status)
const trackingNo = ref(props.order.tracking_no)
const persistedStatus = ref(props.order.status)
const isUpdatingStatus = ref(false)
const isUpdatingTrackingNumber = ref(false)
const trackingError = ref('')

const statusOptions = computed(() => {
    return getOrderStatuses().map((status) => ({
        label: getOrderStatusLabel(t, status),
        value: status,
    }))
})

const receiptSource = computed(() => props.order.receipt_image.src)

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
    if (!receiptSource.value) {
        return
    }

    receiptVisible.value = true
}

const onDownloadOrder = (): void => {
    const link = document.createElement('a')

    link.href = downloadOrder.url(props.order.id)
    link.download = `${props.order.reference_num}.pdf`
    link.hidden = true
    document.body.append(link)
    link.click()
    link.remove()
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
            :header="order.reference_num"
            class="w-full h-full"
            modal
            :draggable="false"
        >
            <div class="grid grid-cols-1 lg:grid-cols-3">
                <div class="mb-4 lg:mb-0 overflow-y-auto mx-3">
                    <div class="flex justify-between items-center mb-3">
                        <div class="text-2xl my-auto">
                            {{ t('admin.orders.order-details') }}
                        </div>

                        <div class="flex gap-1">
                            <Button
                                :aria-label="t('admin.orders.view-receipt')"
                                :data-testid="`view-order-receipt-${order.id}`"
                                :disabled="!receiptSource"
                                :label="t('admin.orders.view-receipt')"
                                icon="pi pi-image"
                                size="small"
                                @click="onViewReceipt"
                            />
                            <Button
                                :aria-label="t('admin.orders.download-order')"
                                :data-testid="`download-order-${order.id}`"
                                :label="t('admin.orders.download-order')"
                                icon="pi pi-download"
                                size="small"
                                @click="onDownloadOrder"
                            />
                        </div>
                    </div>

                    <table class="table-auto w-full">
                        <tbody>
                            <tr>
                                <td class="border border-slate-200 p-3">
                                    {{ t('admin.orders.columns.date') }}
                                </td>
                                <td class="border border-slate-200 p-3">
                                    {{ order.created_at_display }}
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
                                    {{
                                        t('admin.orders.columns.delivery-mode')
                                    }}
                                </td>
                                <td class="border border-slate-200 p-3">
                                    {{
                                        getDeliveryModeLabel(
                                            t,
                                            order.delivery_mode
                                        )
                                    }}
                                </td>
                            </tr>

                            <tr>
                                <td class="border border-slate-200 p-3">
                                    {{ t('admin.orders.columns.status') }}
                                </td>
                                <td class="border border-slate-200 p-3">
                                    <Select
                                        v-model="selectedStatus"
                                        :aria-label="
                                            t('admin.orders.columns.status')
                                        "
                                        :data-testid="`order-status-${order.id}`"
                                        :disabled="
                                            !canUpdateFulfilment ||
                                            isUpdatingStatus
                                        "
                                        :loading="isUpdatingStatus"
                                        :options="statusOptions"
                                        option-label="label"
                                        option-value="value"
                                        @change="onStatusChange"
                                    />
                                </td>
                            </tr>

                            <tr>
                                <td class="border border-slate-200 p-3">
                                    {{
                                        t('admin.orders.columns.payment-method')
                                    }}
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

                    <template v-if="order.delivery_mode === DELIVERY">
                        <div class="text-2xl mt-4 mb-3">
                            {{ t('admin.orders.shipping-information') }}
                        </div>

                        <table class="table-auto w-full">
                            <tbody>
                                <tr>
                                    <td class="border border-slate-200 p-3">
                                        {{
                                            t(
                                                'admin.orders.delivery-tracking-id'
                                            )
                                        }}
                                    </td>
                                    <td class="border border-slate-200 p-3">
                                        <InputText
                                            v-model="trackingNo"
                                            :aria-label="
                                                t(
                                                    'admin.orders.delivery-tracking-id'
                                                )
                                            "
                                            :data-testid="`order-tracking-number-${order.id}`"
                                            :disabled="
                                                !canUpdateFulfilment ||
                                                isUpdatingTrackingNumber
                                            "
                                            :invalid="!!trackingError"
                                            class="w-full"
                                            size="small"
                                        />
                                        <small
                                            v-if="trackingError"
                                            class="text-red-600"
                                        >
                                            {{ trackingError }}
                                        </small>
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
                    </template>
                </div>

                <div class="col-span-2">
                    <div class="text-2xl">
                        {{
                            t('admin.orders.item-details', {
                                count: order.items.length,
                            })
                        }}
                    </div>

                    <DataTable :value="order.items">
                        <Column
                            :header="t('admin.orders.columns.barcode')"
                            field="barcode"
                        />
                        <Column :header="t('admin.orders.columns.name')">
                            <template #body="{ data }">
                                {{ getLocalizedName(data, activeLanguage) }}
                            </template>
                        </Column>
                        <Column
                            :header="t('admin.orders.columns.quantity')"
                            field="quantity"
                        />

                        <Column :header="t('admin.orders.columns.price')">
                            <template #body="{ data }">
                                <div
                                    :class="{
                                        'line-through': data.sale_price,
                                        'text-gray-200': data.sale_price,
                                    }"
                                >
                                    {{ data.price }}
                                </div>

                                <div v-if="data.sale_price">
                                    {{ data.sale_price }}
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </Dialog>

        <Dialog
            v-model:visible="receiptVisible"
            :draggable="false"
            :header="
                t('admin.orders.receipt-dialog-title', {
                    reference: order.reference_num,
                })
            "
            :style="{ width: 'min(96vw, 64rem)' }"
            modal
        >
            <img
                v-if="receiptSource"
                :alt="
                    t('admin.orders.receipt-image-alt', {
                        reference: order.reference_num,
                    })
                "
                class="max-h-[75vh] w-full object-contain"
                :src="receiptSource"
            />
        </Dialog>
    </div>
</template>

<style scoped lang="postcss"></style>
