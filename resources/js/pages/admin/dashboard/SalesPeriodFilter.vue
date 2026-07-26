<script setup lang="ts">
import {
    dashboardPeriods,
    isDashboardPeriod,
    type DashboardFilter,
    type DashboardFilterSelection,
} from '@/pages/admin/dashboard/types'
import Card from 'primevue/card'
import DatePicker from 'primevue/datepicker'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps<{
    filter: DashboardFilter
    loading: boolean
}>()

const emit = defineEmits<{
    change: [selection: DashboardFilterSelection]
}>()

const { t } = useI18n()

const periodOptions = computed(() => {
    return dashboardPeriods.map((period) => ({
        label: t(`admin.dashboard.periods.${period}`),
        value: period,
    }))
})

const selectedDate = computed(() => {
    const [year, month, day] = props.filter.selected_date.split('-').map(Number)

    return new Date(year, month - 1, day)
})

const datePickerView = computed<'date' | 'month' | 'year'>(() => {
    if (props.filter.period === 'monthly') {
        return 'month'
    }

    if (props.filter.period === 'yearly') {
        return 'year'
    }

    return 'date'
})

const dateFormat = computed(() => {
    return t(`admin.dashboard.filters.date-formats.${props.filter.period}`)
})

const dateLabel = computed(() => {
    return t(`admin.dashboard.filters.date-labels.${props.filter.period}`)
})

const formatDate = (date: Date): string => {
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')

    return `${date.getFullYear()}-${month}-${day}`
}

const changePeriod = (value: unknown): void => {
    if (!isDashboardPeriod(value) || value === props.filter.period) {
        return
    }

    emit('change', {
        period: value,
        date: props.filter.selected_date,
    })
}

const changeDate = (value: unknown): void => {
    if (!(value instanceof Date)) {
        return
    }

    emit('change', {
        period: props.filter.period,
        date: formatDate(value),
    })
}
</script>

<template>
    <Card>
        <template #content>
            <section
                class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"
                aria-labelledby="sales-period-heading"
            >
                <div>
                    <h2 id="sales-period-heading" class="text-xl font-semibold">
                        {{ t('admin.dashboard.filters.title') }}
                    </h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ t('admin.dashboard.filters.description') }}
                    </p>
                </div>

                <div
                    class="grid w-full gap-4 sm:grid-cols-2 lg:w-auto lg:grid-cols-[auto_minmax(14rem,18rem)]"
                >
                    <div class="flex min-w-0 flex-col gap-2">
                        <span
                            id="dashboard-period-label"
                            class="text-sm font-medium"
                        >
                            {{ t('admin.dashboard.filters.period') }}
                        </span>
                        <div
                            class="inline-flex max-w-full overflow-x-auto rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800"
                            role="group"
                            aria-labelledby="dashboard-period-label"
                            data-testid="dashboard-period-selector"
                        >
                            <button
                                v-for="option in periodOptions"
                                :key="option.value"
                                type="button"
                                class="whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-500 focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60"
                                :class="
                                    option.value === filter.period
                                        ? 'bg-pink-500 text-white shadow-sm'
                                        : 'text-zinc-600 hover:bg-white hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-700 dark:hover:text-white'
                                "
                                :aria-pressed="option.value === filter.period"
                                :disabled="loading"
                                @click="changePeriod(option.value)"
                            >
                                {{ option.label }}
                            </button>
                        </div>
                    </div>

                    <div class="flex min-w-0 flex-col gap-2">
                        <label
                            for="dashboard-reference-date"
                            class="text-sm font-medium"
                        >
                            {{ dateLabel }}
                        </label>
                        <DatePicker
                            :key="filter.period"
                            input-id="dashboard-reference-date"
                            :model-value="selectedDate"
                            :view="datePickerView"
                            :date-format="dateFormat"
                            :show-week="filter.period === 'weekly'"
                            :manual-input="false"
                            :disabled="loading"
                            show-icon
                            fluid
                            data-testid="dashboard-date-picker"
                            @update:model-value="changeDate"
                        />
                    </div>
                </div>
            </section>
        </template>
    </Card>
</template>
