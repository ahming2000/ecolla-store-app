<script setup lang="ts">
import type { DashboardDistributions } from '@/pages/admin/dashboard/types'
import Card from 'primevue/card'
import MeterGroup from 'primevue/metergroup'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

type DistributionKey =
    | 'pending'
    | 'ready'
    | 'completed'
    | 'refunded'
    | 'canceled'
    | 'delivery'
    | 'self-pickup'

interface DistributionItem {
    key: DistributionKey
    label: string
    value: number
    color: string
}

const props = defineProps<{
    distributions: DashboardDistributions
}>()

const { locale, t } = useI18n()

const numberFormatter = computed(() => {
    return new Intl.NumberFormat(locale.value)
})

const statusItems = computed<DistributionItem[]>(() => [
    {
        key: 'pending',
        label: t(
            'admin.dashboard.visualizations.distributions.statuses.pending'
        ),
        value: props.distributions.status.pending,
        color: '#f59e0b',
    },
    {
        key: 'ready',
        label: t('admin.dashboard.visualizations.distributions.statuses.ready'),
        value: props.distributions.status.ready,
        color: '#3b82f6',
    },
    {
        key: 'completed',
        label: t(
            'admin.dashboard.visualizations.distributions.statuses.completed'
        ),
        value: props.distributions.status.completed,
        color: '#10b981',
    },
    {
        key: 'refunded',
        label: t(
            'admin.dashboard.visualizations.distributions.statuses.refunded'
        ),
        value: props.distributions.status.refunded,
        color: '#8b5cf6',
    },
    {
        key: 'canceled',
        label: t(
            'admin.dashboard.visualizations.distributions.statuses.canceled'
        ),
        value: props.distributions.status.canceled,
        color: '#f43f5e',
    },
])

const deliveryModeItems = computed<DistributionItem[]>(() => [
    {
        key: 'delivery',
        label: t(
            'admin.dashboard.visualizations.distributions.delivery-modes.delivery'
        ),
        value: props.distributions.delivery_mode.delivery,
        color: '#0ea5e9',
    },
    {
        key: 'self-pickup',
        label: t(
            'admin.dashboard.visualizations.distributions.delivery-modes.self-pickup'
        ),
        value: props.distributions.delivery_mode.self_pickup,
        color: '#ec4899',
    },
])

const total = (items: DistributionItem[]): number => {
    return items.reduce((sum, item) => sum + item.value, 0)
}

const meterItems = (items: DistributionItem[]): DistributionItem[] => {
    const itemTotal = total(items)

    if (itemTotal === 0) {
        return items
    }

    return items.map((item) => ({
        ...item,
        value: (item.value / itemTotal) * 100,
    }))
}

const percentage = (item: DistributionItem, items: DistributionItem[]) => {
    const itemTotal = total(items)

    if (itemTotal === 0) {
        return '0%'
    }

    return new Intl.NumberFormat(locale.value, {
        style: 'percent',
        maximumFractionDigits: 0,
    }).format(item.value / itemTotal)
}
</script>

<template>
    <section aria-labelledby="dashboard-order-distributions-heading">
        <header class="mb-3">
            <h2
                id="dashboard-order-distributions-heading"
                class="text-xl font-semibold"
            >
                {{
                    t(
                        'admin.dashboard.visualizations.distributions.section-title'
                    )
                }}
            </h2>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{
                    t(
                        'admin.dashboard.visualizations.distributions.section-description'
                    )
                }}
            </p>
        </header>

        <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
            <Card data-testid="dashboard-status-distribution">
                <template #content>
                    <article
                        aria-labelledby="dashboard-status-distribution-heading"
                    >
                        <h3
                            id="dashboard-status-distribution-heading"
                            class="text-lg font-semibold"
                        >
                            {{
                                t(
                                    'admin.dashboard.visualizations.distributions.status-title'
                                )
                            }}
                        </h3>
                        <p
                            class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                        >
                            {{
                                t(
                                    'admin.dashboard.visualizations.distributions.status-description'
                                )
                            }}
                        </p>

                        <p
                            v-if="total(statusItems) === 0"
                            class="mt-5 rounded-lg border border-dashed border-zinc-300 px-4 py-8 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400"
                        >
                            {{ t('admin.dashboard.visualizations.empty') }}
                        </p>

                        <MeterGroup
                            v-else
                            :value="meterItems(statusItems)"
                            :aria-labelledby="'dashboard-status-distribution-heading'"
                            class="mt-5"
                            data-testid="dashboard-status-meter"
                        >
                            <template #label>
                                <ul
                                    class="grid list-none grid-cols-1 gap-3 p-0 sm:grid-cols-2"
                                >
                                    <li
                                        v-for="item in statusItems"
                                        :key="item.key"
                                        class="flex items-start gap-2"
                                    >
                                        <span
                                            class="mt-1.5 size-2.5 shrink-0 rounded-full"
                                            :style="{
                                                backgroundColor: item.color,
                                            }"
                                            aria-hidden="true"
                                        ></span>
                                        <span class="min-w-0 text-sm">
                                            <span class="block font-medium">
                                                {{ item.label }}
                                            </span>
                                            <span
                                                class="text-zinc-500 dark:text-zinc-400"
                                            >
                                                {{
                                                    numberFormatter.format(
                                                        item.value
                                                    )
                                                }}
                                                ·
                                                {{
                                                    percentage(
                                                        item,
                                                        statusItems
                                                    )
                                                }}
                                            </span>
                                        </span>
                                    </li>
                                </ul>
                            </template>
                        </MeterGroup>
                    </article>
                </template>
            </Card>

            <Card data-testid="dashboard-delivery-distribution">
                <template #content>
                    <article
                        aria-labelledby="dashboard-delivery-distribution-heading"
                    >
                        <h3
                            id="dashboard-delivery-distribution-heading"
                            class="text-lg font-semibold"
                        >
                            {{
                                t(
                                    'admin.dashboard.visualizations.distributions.delivery-title'
                                )
                            }}
                        </h3>
                        <p
                            class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                        >
                            {{
                                t(
                                    'admin.dashboard.visualizations.distributions.delivery-description'
                                )
                            }}
                        </p>

                        <p
                            v-if="total(deliveryModeItems) === 0"
                            class="mt-5 rounded-lg border border-dashed border-zinc-300 px-4 py-8 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400"
                        >
                            {{ t('admin.dashboard.visualizations.empty') }}
                        </p>

                        <MeterGroup
                            v-else
                            :value="meterItems(deliveryModeItems)"
                            :aria-labelledby="'dashboard-delivery-distribution-heading'"
                            class="mt-5"
                            data-testid="dashboard-delivery-meter"
                        >
                            <template #label>
                                <ul
                                    class="grid list-none grid-cols-1 gap-3 p-0 sm:grid-cols-2"
                                >
                                    <li
                                        v-for="item in deliveryModeItems"
                                        :key="item.key"
                                        class="flex items-start gap-2"
                                    >
                                        <span
                                            class="mt-1.5 size-2.5 shrink-0 rounded-full"
                                            :style="{
                                                backgroundColor: item.color,
                                            }"
                                            aria-hidden="true"
                                        ></span>
                                        <span class="min-w-0 text-sm">
                                            <span class="block font-medium">
                                                {{ item.label }}
                                            </span>
                                            <span
                                                class="text-zinc-500 dark:text-zinc-400"
                                            >
                                                {{
                                                    numberFormatter.format(
                                                        item.value
                                                    )
                                                }}
                                                ·
                                                {{
                                                    percentage(
                                                        item,
                                                        deliveryModeItems
                                                    )
                                                }}
                                            </span>
                                        </span>
                                    </li>
                                </ul>
                            </template>
                        </MeterGroup>
                    </article>
                </template>
            </Card>
        </div>
    </section>
</template>
