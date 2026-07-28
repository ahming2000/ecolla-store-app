<script setup lang="ts">
import EmptyDataPlaceholder from '@/components/placeholder/EmptyDataPlaceholder.vue'
import LoadingPlaceholder from '@/components/placeholder/LoadingPlaceholder.vue'
import type { DeliveryMode } from '@/enums/DeliveryMode'
import { getAllDeliveryModes, getDeliveryModeLabel } from '@/enums/DeliveryMode'
import { getOrderStatusLabel } from '@/enums/OrderStatus'
import Admin from '@/layouts/Admin.vue'
import { getAdminOrders } from '@/libraries/axios/admin/order'
import Notification from '@/libraries/primevue/toast/Notification'
import {
    getQueryParameters,
    replaceQueryParameters,
} from '@/libraries/query-parameters'
import OrderDetailButton from '@/pages/admin/order/OrderDetailButton.vue'
import type { Order, OrderFulfilment } from '@/types'
import { Head, usePage } from '@inertiajs/vue3'
import Column from 'primevue/column'
import type { DataTablePageEvent } from 'primevue/datatable'
import DataTable from 'primevue/datatable'
import DatePicker from 'primevue/datepicker'
import Select from 'primevue/select'
import { useToast } from 'primevue/usetoast'
import { computed, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: Admin })

const { t } = useI18n()
const toast = Notification.init(useToast())
const page = usePage()
const parameters = getQueryParameters(page.url)
const perPageOptions = [50, 100, 150, 200]

const getPositiveInteger = (value: string | null, fallback: number): number => {
    const integer = Number(value)

    return Number.isInteger(integer) && integer > 0 ? integer : fallback
}

const parseOrderDate = (value: string | null): Date | null => {
    const match = value?.match(/^(\d{4})-(\d{2})-(\d{2})$/)

    if (!match) {
        return null
    }

    const year = Number(match[1])
    const month = Number(match[2])
    const day = Number(match[3])
    const date = new Date(year, month - 1, day)

    return date.getFullYear() === year &&
        date.getMonth() === month - 1 &&
        date.getDate() === day
        ? date
        : null
}

const requestedDeliveryMode = parameters.get('delivery_mode')
const requestedPerPage = getPositiveInteger(parameters.get('per_page'), 50)

const selectedDate = ref<Date | null>(
    parseOrderDate(parameters.get('order_date'))
)
const selectedOrderMode = ref<DeliveryMode | null>(
    getAllDeliveryModes().includes(requestedDeliveryMode as DeliveryMode)
        ? (requestedDeliveryMode as DeliveryMode)
        : null
)
const orders = ref<Order[]>([])
const pagination = ref({
    currentPage: getPositiveInteger(parameters.get('page'), 1),
    lastPage: 1,
    perPage: perPageOptions.includes(requestedPerPage) ? requestedPerPage : 50,
    total: 0,
})
const isInitialLoading = ref(true)
const isLoading = ref(false)
let latestRequestId = 0

const deliveryModeOptions = computed(() => {
    return getAllDeliveryModes().map((deliveryMode) => ({
        label: getDeliveryModeLabel(t, deliveryMode),
        value: deliveryMode,
    }))
})

const formatSelectedDate = (date: Date): string => {
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')

    return `${date.getFullYear()}-${month}-${day}`
}

const onOrderUpdated = (fulfilment: OrderFulfilment): void => {
    const order = orders.value.find(
        (currentOrder) => currentOrder.id === fulfilment.id
    )

    if (order) {
        Object.assign(order, fulfilment)
    }
}

const showLoadingError = (error: unknown): void => {
    console.error(error)
    toast.axiosError(
        error,
        t('common.notifications.generic-error'),
        t('common.notifications.error')
    )
}

const syncFiltersToUrl = (
    currentPage = pagination.value.currentPage,
    perPage = pagination.value.perPage
): void => {
    replaceQueryParameters(page.url, {
        order_date: selectedDate.value
            ? formatSelectedDate(selectedDate.value)
            : null,
        delivery_mode: selectedOrderMode.value,
        page: currentPage > 1 ? currentPage : null,
        per_page: perPage === 50 ? null : perPage,
    })
}

const loadOrders = async (
    page = pagination.value.currentPage,
    perPage = pagination.value.perPage
): Promise<void> => {
    const requestId = ++latestRequestId

    try {
        isLoading.value = true

        const response = await getAdminOrders({
            order_date: selectedDate.value
                ? formatSelectedDate(selectedDate.value)
                : undefined,
            delivery_mode: selectedOrderMode.value ?? undefined,
            page,
            per_page: perPage,
        })

        if (requestId !== latestRequestId) {
            return
        }

        if (response.current_page > response.last_page) {
            await loadOrders(response.last_page, response.per_page)

            return
        }

        orders.value = response.data
        pagination.value = {
            currentPage: response.current_page,
            lastPage: response.last_page,
            perPage: response.per_page,
            total: response.total,
        }
    } catch (error) {
        if (requestId === latestRequestId) {
            throw error
        }
    } finally {
        if (requestId === latestRequestId) {
            isLoading.value = false
        }
    }
}

const reloadFirstPage = async (): Promise<void> => {
    try {
        syncFiltersToUrl(1)
        await loadOrders(1)
    } catch (error) {
        showLoadingError(error)
    }
}

const onPageChange = async (event: DataTablePageEvent): Promise<void> => {
    try {
        syncFiltersToUrl(event.page + 1, event.rows)
        await loadOrders(event.page + 1, event.rows)
    } catch (error) {
        showLoadingError(error)
    }
}

watch([selectedDate, selectedOrderMode], () => {
    void reloadFirstPage()
})

onMounted(async () => {
    try {
        await loadOrders(pagination.value.currentPage, pagination.value.perPage)
        syncFiltersToUrl()
    } catch (error) {
        showLoadingError(error)
    } finally {
        isInitialLoading.value = false
    }
})
</script>

<template>
    <Head :title="t('admin.orders.title')" />

    <div class="m-5">
        <div class="flex justify-between items-center mb-3">
            <div class="text-3xl">{{ t('admin.orders.title') }}</div>
        </div>

        <LoadingPlaceholder v-if="isInitialLoading" />

        <DataTable
            v-else
            :aria-busy="isLoading"
            :first="(pagination.currentPage - 1) * pagination.perPage"
            :loading="isLoading"
            :rows="pagination.perPage"
            paginator
            :rows-per-page-options="[50, 100, 150, 200]"
            :total-records="pagination.total"
            :value="orders"
            data-key="id"
            lazy
            @page="onPageChange"
        >
            <template #header>
                <div class="container mx-auto pt-5 px-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <DatePicker
                            v-model="selectedDate"
                            :placeholder="t('admin.orders.date-filter')"
                            :date-format="t('admin.orders.date-format')"
                            show-button-bar
                        />

                        <Select
                            v-model="selectedOrderMode"
                            :placeholder="t('admin.orders.order-mode-filter')"
                            :options="deliveryModeOptions"
                            option-label="label"
                            option-value="value"
                            show-clear
                        />
                    </div>
                </div>
            </template>

            <template #empty>
                <EmptyDataPlaceholder
                    :text="t('common.placeholders.no-orders')"
                />
            </template>

            <Column
                :header="t('admin.orders.columns.date')"
                field="created_at_display"
            />
            <Column
                :header="t('admin.orders.columns.reference')"
                field="reference_num"
            />
            <Column :header="t('admin.orders.columns.delivery-mode')">
                <template #body="{ data }">
                    {{ getDeliveryModeLabel(t, data.delivery_mode) }}
                </template>
            </Column>
            <Column
                :header="t('admin.orders.columns.payment-method')"
                field="payment_method.name"
            />
            <Column
                :header="t('admin.orders.columns.item-count')"
                field="items.length"
            />
            <Column
                :header="t('admin.orders.columns.total')"
                field="subtotal"
            />
            <Column :header="t('admin.orders.columns.status')">
                <template #body="{ data }">
                    {{ getOrderStatusLabel(t, data.status) }}
                </template>
            </Column>

            <Column header="">
                <template #body="{ data }">
                    <OrderDetailButton
                        :order="data"
                        @updated="onOrderUpdated"
                    />
                </template>
            </Column>
        </DataTable>
    </div>
</template>

<style scoped lang="postcss"></style>
