<script setup lang="ts">
import IftaInputText from '@/components/input/IftaInputText.vue'
import {
    DELIVERY,
    getAllDeliveryModes,
    getDeliveryModeLabel,
    SELF_PICKUP,
    type DeliveryMode,
} from '@/enums/DeliveryMode'
import { CANCELED } from '@/enums/OrderStatus'
import { updateOrder } from '@/libraries/axios/admin/order'
import { parseFormError } from '@/libraries/axios/common/parser'
import { getLocalizedName } from '@/libraries/i18n/language'
import Notification from '@/libraries/primevue/toast/Notification'
import type {
    Order,
    OrderedItem,
    OrderItemUpdateData,
    OrderUpdateData,
} from '@/types'
import { useForm } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Select from 'primevue/select'
import { useToast } from 'primevue/usetoast'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps<{
    order: Order
}>()

const emit = defineEmits<{
    cancel: []
    saved: [order: Order]
}>()

interface OrderItemEditDraft extends Omit<
    OrderItemUpdateData,
    'quantity' | 'effective_price'
> {
    quantity: number | null
    effective_price: number | null
}

interface OrderEditDraft extends Omit<
    OrderUpdateData,
    'shipping_fee' | 'items'
> {
    shipping_fee: number | null
    items: OrderItemEditDraft[]
}

const createDraft = (): OrderEditDraft => ({
    delivery_mode: props.order.delivery_mode,
    shipping_fee: props.order.shipping_fee,
    note: props.order.note,
    cus_name: props.order.cus_name,
    cus_phone: props.order.cus_phone,
    cus_address: props.order.cus_address,
    items: props.order.items.map((item) => ({
        id: item.id,
        quantity: item.quantity,
        effective_price: item.sale_price ?? item.price,
    })),
    cancel_when_empty:
        props.order.items.length === 0 && props.order.status === CANCELED,
})

const form = useForm<OrderEditDraft>(createDraft())
const isSaving = ref(false)
const lastItemConfirmationVisible = ref(false)
const toast = Notification.init(useToast())
const { locale: activeLanguage, t } = useI18n()

const deliveryModeOptions = computed(() =>
    getAllDeliveryModes().map((deliveryMode) => ({
        label: getDeliveryModeLabel(t, deliveryMode),
        value: deliveryMode,
    }))
)

const isDelivery = computed(() => form.delivery_mode === DELIVERY)

const orderedItemById = computed(() => {
    return new Map(props.order.items.map((item) => [item.id, item] as const))
})

const editableItems = computed(() => {
    return form.items
        .map((draft, index) => ({
            draft,
            index,
            orderedItem: orderedItemById.value.get(draft.id),
        }))
        .filter(
            (
                row
            ): row is {
                draft: OrderItemEditDraft
                index: number
                orderedItem: OrderedItem
            } => row.orderedItem !== undefined
        )
})

const itemSubtotal = computed(() => {
    return form.items.reduce((subtotal, item) => {
        return (
            subtotal +
            Number(item.effective_price ?? 0) * Number(item.quantity ?? 0)
        )
    }, 0)
})

const orderTotal = computed(() => {
    return itemSubtotal.value + Number(form.shipping_fee ?? 0)
})

const formErrors = computed(
    () => form.errors as Record<string, string | undefined>
)

const isFormValid = computed(() => {
    const hasValidCustomer = isDelivery.value
        ? Boolean(form.cus_name?.trim()) && Boolean(form.cus_address?.trim())
        : true
    const hasValidItems =
        (form.items.length > 0 &&
            form.items.every(
                (item) =>
                    Number.isInteger(item.quantity) &&
                    Number(item.quantity) >= 1 &&
                    Number.isFinite(item.effective_price) &&
                    Number(item.effective_price) >= 0.01
            )) ||
        (form.items.length === 0 && form.cancel_when_empty)

    return (
        getAllDeliveryModes().includes(form.delivery_mode as DeliveryMode) &&
        Number.isFinite(form.shipping_fee) &&
        Number(form.shipping_fee) >= 0 &&
        Boolean(form.cus_phone?.trim()) &&
        hasValidCustomer &&
        hasValidItems
    )
})

const normalizedNullableText = (value: string | null): string | null => {
    const normalizedValue = value?.trim() ?? ''

    return normalizedValue === '' ? null : normalizedValue
}

const normalizedFormData = (): OrderUpdateData => ({
    delivery_mode: form.delivery_mode,
    shipping_fee:
        form.delivery_mode === SELF_PICKUP ? 0 : Number(form.shipping_fee),
    note: normalizedNullableText(form.note),
    cus_name: normalizedNullableText(form.cus_name),
    cus_phone: form.cus_phone.trim(),
    cus_address: normalizedNullableText(form.cus_address),
    items: form.items.map((item) => ({
        id: item.id,
        quantity: Number(item.quantity),
        effective_price: Number(item.effective_price),
    })),
    cancel_when_empty: form.cancel_when_empty,
})

const removeItem = (index: number): void => {
    if (form.items.length === 1) {
        lastItemConfirmationVisible.value = true

        return
    }

    form.items.splice(index, 1)
    form.clearErrors()
}

const removeLastItemAndCancel = (): void => {
    form.items.splice(0, 1)
    form.cancel_when_empty = true
    form.clearErrors()
    lastItemConfirmationVisible.value = false
}

const onSubmit = async (): Promise<void> => {
    if (!isFormValid.value || isSaving.value) {
        return
    }

    try {
        isSaving.value = true
        form.clearErrors()

        const updatedOrder = await updateOrder(
            props.order.id,
            normalizedFormData()
        )

        toast.success(
            t('admin.orders.edit.updated-success'),
            t('common.notifications.success')
        )
        emit('saved', updatedOrder)
    } catch (error) {
        form.errors = parseFormError(error)
        toast.axiosError(
            error,
            t('admin.orders.edit.update-failed'),
            t('common.notifications.error')
        )
    } finally {
        isSaving.value = false
    }
}

watch(
    () => form.delivery_mode,
    (deliveryMode) => {
        if (deliveryMode === SELF_PICKUP) {
            form.shipping_fee = 0
        }
    }
)
</script>

<template>
    <form
        class="flex min-w-0 flex-col gap-5"
        data-testid="order-edit-form"
        @submit.prevent="onSubmit"
    >
        <section class="rounded-lg border border-surface-200 p-4">
            <h3 class="mb-4 text-xl font-bold">
                {{ t('admin.orders.edit.customer-and-order') }}
            </h3>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div class="flex flex-col gap-1">
                    <label
                        :for="`order-${order.id}-delivery-mode`"
                        class="text-sm font-medium"
                    >
                        {{ t('admin.orders.columns.delivery-mode') }}
                    </label>
                    <Select
                        v-model="form.delivery_mode"
                        :input-id="`order-${order.id}-delivery-mode`"
                        :options="deliveryModeOptions"
                        fluid
                        option-label="label"
                        option-value="value"
                    />
                    <small v-if="formErrors.delivery_mode" class="text-red-500">
                        {{ formErrors.delivery_mode }}
                    </small>
                </div>

                <IftaInputText
                    v-model.number="form.shipping_fee"
                    :disabled="form.delivery_mode === SELF_PICKUP"
                    :error="formErrors.shipping_fee"
                    :input-id="`order-${order.id}-shipping-fee`"
                    :label="t('admin.orders.shipping-fee')"
                    :min="0"
                    :step="0.01"
                    input-class="w-full"
                    type="number"
                />

                <IftaInputText
                    v-model="form.cus_name"
                    :error="formErrors.cus_name"
                    :input-id="`order-${order.id}-customer-name`"
                    :label="t('admin.orders.customer-name')"
                    :required="isDelivery"
                    input-class="w-full"
                />

                <IftaInputText
                    v-model="form.cus_phone"
                    :error="formErrors.cus_phone"
                    :input-id="`order-${order.id}-customer-phone`"
                    :label="t('admin.orders.customer-phone')"
                    input-class="w-full"
                    required
                />

                <IftaInputText
                    v-model="form.cus_address"
                    :error="formErrors.cus_address"
                    :input-id="`order-${order.id}-customer-address`"
                    :label="t('admin.orders.customer-address')"
                    :required="isDelivery"
                    class="md:col-span-2"
                    input-class="w-full"
                />

                <IftaInputText
                    v-model="form.note"
                    :error="formErrors.note"
                    :input-id="`order-${order.id}-note`"
                    :label="t('admin.orders.note')"
                    class="md:col-span-2"
                    input-class="w-full"
                />
            </div>
        </section>

        <section class="min-w-0 rounded-lg border border-surface-200 p-4">
            <div
                class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"
            >
                <h3 class="text-xl font-bold">
                    {{
                        t('admin.orders.item-details', {
                            count: form.items.length,
                        })
                    }}
                </h3>
                <p class="text-sm text-surface-500">
                    {{ t('admin.orders.edit.price-help') }}
                </p>
            </div>

            <div v-if="editableItems.length > 0" class="overflow-x-auto">
                <table class="w-full min-w-[42rem] table-auto">
                    <thead>
                        <tr class="text-left">
                            <th class="border-b border-surface-200 p-2">
                                {{ t('admin.orders.columns.barcode') }}
                            </th>
                            <th class="border-b border-surface-200 p-2">
                                {{ t('admin.orders.columns.name') }}
                            </th>
                            <th class="border-b border-surface-200 p-2">
                                {{ t('admin.orders.columns.quantity') }}
                            </th>
                            <th class="border-b border-surface-200 p-2">
                                {{ t('admin.orders.columns.price') }}
                            </th>
                            <th
                                class="border-b border-surface-200 p-2"
                                :aria-label="t('common.actions.delete')"
                            />
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in editableItems" :key="row.draft.id">
                            <td class="border-b border-surface-100 p-2">
                                {{ row.orderedItem.barcode }}
                            </td>
                            <td class="border-b border-surface-100 p-2">
                                {{
                                    getLocalizedName(
                                        row.orderedItem,
                                        activeLanguage
                                    )
                                }}
                            </td>
                            <td class="border-b border-surface-100 p-2">
                                <IftaInputText
                                    v-model.number="row.draft.quantity"
                                    :error="
                                        formErrors[
                                            `items.${row.index}.quantity`
                                        ]
                                    "
                                    :input-id="`order-${order.id}-item-${row.draft.id}-quantity`"
                                    :label="t('admin.orders.columns.quantity')"
                                    :min="1"
                                    :step="1"
                                    input-class="w-28"
                                    required
                                    type="number"
                                />
                            </td>
                            <td class="border-b border-surface-100 p-2">
                                <IftaInputText
                                    v-model.number="row.draft.effective_price"
                                    :error="
                                        formErrors[
                                            `items.${row.index}.effective_price`
                                        ]
                                    "
                                    :input-id="`order-${order.id}-item-${row.draft.id}-price`"
                                    :label="t('admin.orders.columns.price')"
                                    :min="0.01"
                                    :step="0.01"
                                    input-class="w-32"
                                    required
                                    type="number"
                                />
                            </td>
                            <td class="border-b border-surface-100 p-2">
                                <Button
                                    :aria-label="
                                        t('admin.orders.edit.remove-item', {
                                            name: getLocalizedName(
                                                row.orderedItem,
                                                activeLanguage
                                            ),
                                        })
                                    "
                                    :data-testid="`remove-order-item-${row.draft.id}`"
                                    :disabled="isSaving"
                                    icon="pi pi-trash"
                                    rounded
                                    severity="danger"
                                    text
                                    type="button"
                                    @click="removeItem(row.index)"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-else
                class="rounded-md bg-orange-50 p-4 text-orange-800"
                data-testid="empty-order-cancellation-notice"
            >
                {{ t('admin.orders.edit.empty-order-will-cancel') }}
            </div>

            <small v-if="formErrors.items" class="mt-2 block text-red-500">
                {{ formErrors.items }}
            </small>

            <dl
                class="ml-auto mt-4 grid max-w-sm grid-cols-2 gap-x-4 gap-y-2 text-right"
            >
                <dt>{{ t('admin.orders.edit.items-subtotal') }}</dt>
                <dd>RM {{ itemSubtotal.toFixed(2) }}</dd>
                <dt>{{ t('admin.orders.shipping-fee') }}</dt>
                <dd>RM {{ Number(form.shipping_fee ?? 0).toFixed(2) }}</dd>
                <dt class="font-bold">
                    {{ t('admin.orders.edit.order-total') }}
                </dt>
                <dd class="font-bold">RM {{ orderTotal.toFixed(2) }}</dd>
            </dl>
        </section>

        <div class="flex justify-end gap-2">
            <Button
                :disabled="isSaving"
                :label="t('common.actions.cancel')"
                severity="secondary"
                type="button"
                @click="emit('cancel')"
            />
            <Button
                :data-testid="`save-order-${order.id}`"
                :disabled="!isFormValid"
                :label="t('common.actions.save')"
                :loading="isSaving"
                icon="pi pi-save"
                type="submit"
            />
        </div>
    </form>

    <Dialog
        v-model:visible="lastItemConfirmationVisible"
        :draggable="false"
        :header="t('admin.orders.edit.remove-last-title')"
        :style="{ width: 'min(30rem, calc(100vw - 2rem))' }"
        modal
    >
        <p>{{ t('admin.orders.edit.remove-last-confirmation') }}</p>

        <div class="mt-5 flex justify-end gap-2">
            <Button
                :label="t('common.actions.cancel')"
                severity="secondary"
                type="button"
                @click="lastItemConfirmationVisible = false"
            />
            <Button
                :label="t('admin.orders.edit.remove-and-cancel')"
                severity="danger"
                type="button"
                @click="removeLastItemAndCancel"
            />
        </div>
    </Dialog>
</template>
