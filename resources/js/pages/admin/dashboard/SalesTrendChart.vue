<script setup lang="ts">
import type {
    DashboardFilter,
    DashboardTrendPoint,
} from '@/pages/admin/dashboard/types'
import Card from 'primevue/card'
import ProgressBar from 'primevue/progressbar'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps<{
    filter: DashboardFilter
    trend: DashboardTrendPoint[]
}>()

const { locale, t } = useI18n()

const moneyFormatter = computed(() => {
    return new Intl.NumberFormat(locale.value, {
        style: 'currency',
        currency: 'MYR',
        minimumFractionDigits: 2,
    })
})

const numberFormatter = computed(() => {
    return new Intl.NumberFormat(locale.value)
})

const dateFormatter = (
    options: Intl.DateTimeFormatOptions
): Intl.DateTimeFormat => {
    return new Intl.DateTimeFormat(locale.value, {
        ...options,
        timeZone: props.filter.timezone,
    })
}

const bucketLabel = (point: DashboardTrendPoint): string => {
    const startsAt = new Date(point.starts_at)
    const endsAt = new Date(point.ends_at)

    if (props.filter.period === 'daily') {
        const formatter = dateFormatter({
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        })

        return `${formatter.format(startsAt)}–${formatter.format(endsAt)}`
    }

    if (props.filter.period === 'weekly') {
        return dateFormatter({
            weekday: 'short',
            day: 'numeric',
            month: 'short',
        }).format(startsAt)
    }

    if (props.filter.period === 'monthly') {
        const formatter = dateFormatter({
            day: 'numeric',
            month: 'short',
        })
        const inclusiveEndsAt = new Date(endsAt.getTime() - 1)

        return `${formatter.format(startsAt)}–${formatter.format(inclusiveEndsAt)}`
    }

    return dateFormatter({ month: 'short' }).format(startsAt)
}

const chartPoints = computed(() => {
    const maximumRevenue = Math.max(
        ...props.trend.map((point) => Number(point.sales_revenue)),
        0
    )

    return props.trend.map((point) => {
        const salesRevenue = Number(point.sales_revenue)
        const formattedRevenue = moneyFormatter.value.format(salesRevenue)
        const formattedOrderCount = numberFormatter.value.format(
            point.completed_order_count
        )
        const label = bucketLabel(point)

        return {
            ...point,
            label,
            formattedRevenue,
            formattedOrderCount,
            percentage:
                maximumRevenue === 0
                    ? 0
                    : Math.round((salesRevenue / maximumRevenue) * 100),
            ariaLabel: t('admin.dashboard.visualizations.trend.bar-label', {
                label,
                revenue: formattedRevenue,
                orders: formattedOrderCount,
            }),
        }
    })
})

const hasSales = computed(() => {
    return props.trend.some((point) => {
        return (
            point.completed_order_count > 0 || Number(point.sales_revenue) > 0
        )
    })
})
</script>

<template>
    <Card data-testid="dashboard-sales-trend">
        <template #content>
            <section aria-labelledby="dashboard-sales-trend-heading">
                <header>
                    <h2
                        id="dashboard-sales-trend-heading"
                        class="text-xl font-semibold"
                    >
                        {{ t('admin.dashboard.visualizations.trend.title') }}
                    </h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{
                            t(
                                'admin.dashboard.visualizations.trend.description'
                            )
                        }}
                    </p>
                </header>

                <p
                    v-if="!hasSales"
                    class="mt-5 rounded-lg border border-dashed border-zinc-300 px-4 py-8 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400"
                    data-testid="dashboard-sales-trend-empty"
                >
                    {{ t('admin.dashboard.visualizations.trend.empty') }}
                </p>

                <div v-else class="mt-5 grid gap-4" role="list">
                    <div
                        v-for="point in chartPoints"
                        :key="point.starts_at"
                        class="grid min-w-0 gap-2 sm:grid-cols-[minmax(7rem,10rem)_minmax(0,1fr)_minmax(10rem,auto)] sm:items-center"
                        role="listitem"
                        data-testid="dashboard-trend-point"
                    >
                        <span class="text-sm font-medium">
                            {{ point.label }}
                        </span>

                        <ProgressBar
                            :value="point.percentage"
                            :show-value="false"
                            :aria-label="point.ariaLabel"
                            class="!h-3"
                        />

                        <span
                            class="text-sm text-zinc-600 sm:text-right dark:text-zinc-300"
                        >
                            <strong
                                class="font-semibold text-zinc-900 dark:text-zinc-100"
                            >
                                {{ point.formattedRevenue }}
                            </strong>
                            ·
                            {{
                                t(
                                    'admin.dashboard.visualizations.trend.completed-orders',
                                    {
                                        count: point.formattedOrderCount,
                                    }
                                )
                            }}
                        </span>
                    </div>
                </div>

                <details
                    class="mt-5 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800/60"
                    data-testid="dashboard-trend-data-table"
                >
                    <summary
                        class="cursor-pointer text-sm font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-500"
                    >
                        {{
                            t('admin.dashboard.visualizations.trend.data-table')
                        }}
                    </summary>

                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full min-w-lg text-left text-sm">
                            <caption class="sr-only">
                                {{
                                    t(
                                        'admin.dashboard.visualizations.trend.table-caption'
                                    )
                                }}
                            </caption>
                            <thead>
                                <tr
                                    class="border-b border-zinc-200 dark:border-zinc-700"
                                >
                                    <th
                                        scope="col"
                                        class="px-2 py-2 font-semibold"
                                    >
                                        {{
                                            t(
                                                'admin.dashboard.visualizations.trend.period'
                                            )
                                        }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-2 py-2 font-semibold"
                                    >
                                        {{
                                            t(
                                                'admin.dashboard.visualizations.trend.orders'
                                            )
                                        }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-2 py-2 font-semibold"
                                    >
                                        {{
                                            t(
                                                'admin.dashboard.visualizations.trend.revenue'
                                            )
                                        }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="point in chartPoints"
                                    :key="`table-${point.starts_at}`"
                                    class="border-b border-zinc-200 last:border-0 dark:border-zinc-700"
                                >
                                    <th
                                        scope="row"
                                        class="whitespace-nowrap px-2 py-2 font-medium"
                                    >
                                        {{ point.label }}
                                    </th>
                                    <td class="px-2 py-2">
                                        {{ point.formattedOrderCount }}
                                    </td>
                                    <td class="whitespace-nowrap px-2 py-2">
                                        {{ point.formattedRevenue }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </details>
            </section>
        </template>
    </Card>
</template>
