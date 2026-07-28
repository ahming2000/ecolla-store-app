<script setup lang="ts">
import Admin from '@/layouts/Admin.vue'
import SalesPeriodFilter from '@/pages/admin/dashboard/SalesPeriodFilter.vue'
import SalesSummaryGrid from '@/pages/admin/dashboard/SalesSummaryGrid.vue'
import type {
    DashboardFilterSelection,
    DashboardOverview,
} from '@/pages/admin/dashboard/types'
import { page as dashboardPage } from '@/routes/admin/dashboard'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: Admin })

defineProps<{
    dashboard: DashboardOverview
}>()

const { t } = useI18n()
const isLoading = ref(false)

const updateFilter = (selection: DashboardFilterSelection): void => {
    router.visit(
        dashboardPage({
            query: {
                period: selection.period,
                date: selection.date,
            },
        }),
        {
            only: ['dashboard'],
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onStart: () => {
                isLoading.value = true
            },
            onFinish: () => {
                isLoading.value = false
            },
        }
    )
}
</script>

<template>
    <Head :title="t('admin.dashboard.title')" />

    <main class="container mx-auto px-3 py-6">
        <header class="mb-5">
            <h1 class="text-3xl font-semibold tracking-tight">
                {{ t('admin.dashboard.title') }}
            </h1>
            <p class="mt-1 text-zinc-500 dark:text-zinc-400">
                {{ t('admin.dashboard.description') }}
            </p>
        </header>

        <div class="space-y-5" :aria-busy="isLoading">
            <SalesPeriodFilter
                :filter="dashboard.filter"
                :loading="isLoading"
                @change="updateFilter"
            />
            <SalesSummaryGrid :summary="dashboard.summary" />
        </div>
    </main>
</template>
