<script setup lang="ts">
import { getDeliveryModeLabel } from '@/enums/DeliveryMode'
import {
    CANCELED,
    COMPLETED,
    getOrderStatusLabel,
    PENDING,
    READY,
    REFUNDED,
} from '@/enums/OrderStatus'
import Shop from '@/layouts/Shop.vue'
import {
    lookupOrder,
    type TrackedOrder,
    type TrackedOrderItem,
} from '@/libraries/axios/shop/order-tracking'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Tag from 'primevue/tag'
import { computed, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: Shop })

const props = defineProps<{
    initialReferenceNumber: string
}>()

const { locale, t } = useI18n()
const form = reactive({
    referenceNumber: props.initialReferenceNumber,
    phone: '',
})
const trackedOrder = ref<TrackedOrder | null>(null)
const errorMessage = ref('')
const isLoading = ref(false)

const localizedItems = computed(() => {
    return (
        trackedOrder.value?.items.map((item) => ({
            ...item,
            localizedName:
                locale.value === 'en' && item.name_en
                    ? item.name_en
                    : item.name,
        })) ?? []
    )
})

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-MY', {
        currency: 'MYR',
        style: 'currency',
    }).format(amount)
}

const formatDateTime = (value: string): string => {
    return new Intl.DateTimeFormat(locale.value === 'en' ? 'en-MY' : 'zh-CN', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value))
}

const statusSeverity = (
    status: string
): 'success' | 'info' | 'warn' | 'danger' | 'secondary' => {
    switch (status) {
        case COMPLETED:
            return 'success'
        case READY:
            return 'info'
        case PENDING:
            return 'warn'
        case CANCELED:
            return 'danger'
        case REFUNDED:
        default:
            return 'secondary'
    }
}

const itemLineDescription = (item: TrackedOrderItem): string => {
    return t('shop.tracking.result.item-line', {
        price: formatCurrency(item.unit_price),
        quantity: item.quantity,
    })
}

const submitLookup = async (): Promise<void> => {
    errorMessage.value = ''
    trackedOrder.value = null

    if (!form.referenceNumber.trim() || !/\d/.test(form.phone)) {
        errorMessage.value = t('shop.tracking.errors.required')

        return
    }

    try {
        isLoading.value = true
        trackedOrder.value = await lookupOrder(form.referenceNumber, form.phone)
    } catch (error) {
        errorMessage.value =
            axios.isAxiosError(error) && error.response?.status === 429
                ? t('shop.tracking.errors.rate-limit')
                : t('shop.tracking.errors.not-found')
    } finally {
        isLoading.value = false
    }
}
</script>

<template>
    <div>
        <Head :title="t('shop.tracking.title')" />

        <main class="container mx-auto my-8 max-w-4xl space-y-6 px-3">
            <header class="space-y-2 text-center">
                <h1 class="text-3xl font-bold">
                    {{ t('shop.tracking.heading') }}
                </h1>
                <p class="text-surface-600">
                    {{ t('shop.tracking.description') }}
                </p>
            </header>

            <Card>
                <template #content>
                    <form
                        class="grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end"
                        data-testid="order-tracking-form"
                        @submit.prevent="submitLookup"
                    >
                        <div class="space-y-2">
                            <label
                                class="block font-semibold"
                                for="tracking-reference"
                            >
                                {{ t('shop.tracking.form.reference') }}
                            </label>
                            <InputText
                                id="tracking-reference"
                                v-model="form.referenceNumber"
                                class="w-full"
                                :placeholder="
                                    t(
                                        'shop.tracking.form.reference-placeholder'
                                    )
                                "
                                autocomplete="off"
                            />
                        </div>

                        <div class="space-y-2">
                            <label
                                class="block font-semibold"
                                for="tracking-phone"
                            >
                                {{ t('shop.tracking.form.phone') }}
                            </label>
                            <InputText
                                id="tracking-phone"
                                v-model="form.phone"
                                class="w-full"
                                :placeholder="
                                    t('shop.tracking.form.phone-placeholder')
                                "
                                autocomplete="tel"
                                inputmode="tel"
                            />
                        </div>

                        <Button
                            type="submit"
                            icon="pi pi-search"
                            :label="t('shop.tracking.form.submit')"
                            :loading="isLoading"
                        />
                    </form>

                    <Message
                        v-if="errorMessage"
                        class="mt-4"
                        severity="error"
                        :closable="false"
                        data-testid="order-tracking-error"
                    >
                        {{ errorMessage }}
                    </Message>
                </template>
            </Card>

            <Card v-if="trackedOrder" data-testid="order-tracking-result">
                <template #title>
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <span class="break-all">
                            {{
                                t('shop.tracking.result.reference', {
                                    reference: trackedOrder.reference_num,
                                })
                            }}
                        </span>
                        <Tag
                            :severity="statusSeverity(trackedOrder.status)"
                            :value="getOrderStatusLabel(t, trackedOrder.status)"
                        />
                    </div>
                </template>

                <template #content>
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm text-surface-500">
                                {{ t('shop.tracking.result.placed-at') }}
                            </dt>
                            <dd class="font-medium">
                                {{ formatDateTime(trackedOrder.created_at) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-surface-500">
                                {{ t('shop.tracking.result.delivery-mode') }}
                            </dt>
                            <dd class="font-medium">
                                {{
                                    getDeliveryModeLabel(
                                        t,
                                        trackedOrder.delivery_mode
                                    )
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-surface-500">
                                {{ t('shop.tracking.result.tracking-number') }}
                            </dt>
                            <dd
                                class="break-all font-medium"
                                data-testid="order-tracking-number"
                            >
                                {{
                                    trackedOrder.tracking_no ||
                                    t(
                                        'shop.tracking.result.tracking-unavailable'
                                    )
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-surface-500">
                                {{ t('shop.tracking.result.last-updated') }}
                            </dt>
                            <dd class="font-medium">
                                {{ formatDateTime(trackedOrder.updated_at) }}
                            </dd>
                        </div>
                    </dl>

                    <section class="mt-6 space-y-3">
                        <h2 class="text-xl font-bold">
                            {{ t('shop.tracking.result.items') }}
                        </h2>

                        <ul class="divide-y divide-surface-200">
                            <li
                                v-for="item in localizedItems"
                                :key="item.id"
                                class="flex items-start justify-between gap-4 py-3"
                            >
                                <div class="min-w-0">
                                    <div class="break-words font-semibold">
                                        {{ item.localizedName }}
                                    </div>
                                    <div class="text-sm text-surface-500">
                                        {{ itemLineDescription(item) }}
                                    </div>
                                </div>
                                <div class="shrink-0 font-semibold">
                                    {{ formatCurrency(item.line_total) }}
                                </div>
                            </li>
                        </ul>
                    </section>

                    <dl
                        class="ms-auto mt-4 grid max-w-sm grid-cols-[1fr_auto] gap-x-6 gap-y-2"
                    >
                        <dt>{{ t('shop.tracking.result.subtotal') }}</dt>
                        <dd class="text-end">
                            {{ formatCurrency(trackedOrder.subtotal) }}
                        </dd>
                        <dt>{{ t('shop.tracking.result.shipping') }}</dt>
                        <dd class="text-end">
                            {{ formatCurrency(trackedOrder.shipping_fee) }}
                        </dd>
                        <dt class="font-bold">
                            {{ t('shop.tracking.result.total') }}
                        </dt>
                        <dd class="text-end font-bold">
                            {{ formatCurrency(trackedOrder.total) }}
                        </dd>
                    </dl>

                    <div
                        v-if="trackedOrder.note"
                        class="mt-5 rounded-lg bg-surface-100 p-4"
                    >
                        <div class="text-sm font-semibold">
                            {{ t('shop.tracking.result.note') }}
                        </div>
                        <p class="mt-1 break-words whitespace-pre-wrap">
                            {{ trackedOrder.note }}
                        </p>
                    </div>
                </template>
            </Card>
        </main>
    </div>
</template>
