<script setup lang="ts">
import SalesMetricCard from '@/pages/admin/dashboard/SalesMetricCard.vue'
import type { DashboardSummary } from '@/pages/admin/dashboard/types'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps<{
    summary: DashboardSummary
}>()

const { locale, t } = useI18n()

const numberFormatter = computed(() => {
    return new Intl.NumberFormat(locale.value)
})

const moneyFormatter = computed(() => {
    return new Intl.NumberFormat(locale.value, {
        style: 'currency',
        currency: 'MYR',
        minimumFractionDigits: 2,
    })
})

const metrics = computed(() => {
    return [
        {
            key: 'completed-orders',
            label: t('admin.dashboard.summary.completed-orders.label'),
            value: numberFormatter.value.format(
                props.summary.completed_order_count
            ),
            description: t(
                'admin.dashboard.summary.completed-orders.description'
            ),
            icon: 'pi pi-check-circle',
            tone: 'emerald' as const,
        },
        {
            key: 'items-sold',
            label: t('admin.dashboard.summary.items-sold.label'),
            value: numberFormatter.value.format(props.summary.items_sold),
            description: t('admin.dashboard.summary.items-sold.description'),
            icon: 'pi pi-shopping-bag',
            tone: 'blue' as const,
        },
        {
            key: 'sales-revenue',
            label: t('admin.dashboard.summary.sales-revenue.label'),
            value: moneyFormatter.value.format(
                Number(props.summary.sales_revenue)
            ),
            description: t('admin.dashboard.summary.sales-revenue.description'),
            icon: 'pi pi-chart-line',
            tone: 'amber' as const,
        },
        {
            key: 'canceled-order-value',
            label: t('admin.dashboard.summary.canceled-order-value.label'),
            value: moneyFormatter.value.format(
                Number(props.summary.canceled_order_value)
            ),
            description: t(
                'admin.dashboard.summary.canceled-order-value.description'
            ),
            icon: 'pi pi-times-circle',
            tone: 'rose' as const,
        },
    ]
})
</script>

<template>
    <section
        aria-labelledby="sales-summary-heading"
        data-testid="sales-summary"
    >
        <h2 id="sales-summary-heading" class="mb-3 text-xl font-semibold">
            {{ t('admin.dashboard.summary.title') }}
        </h2>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <SalesMetricCard
                v-for="metric in metrics"
                :key="metric.key"
                :label="metric.label"
                :value="metric.value"
                :description="metric.description"
                :icon="metric.icon"
                :tone="metric.tone"
                :test-id="`dashboard-metric-${metric.key}`"
            />
        </div>
    </section>
</template>
