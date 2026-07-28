<script setup lang="ts">
import ArrangementDropdown from '@/components/filterer/item/ArrangementDropdown.vue'
import CategorySelect from '@/components/filterer/item/CategorySelect.vue'
import IsNotListedCheckbox from '@/components/filterer/item/IsNotListedCheckbox.vue'
import IsOutOfStockCheckbox from '@/components/filterer/item/IsOutOfStockCheckbox.vue'
import SearchInput from '@/components/filterer/item/SearchInput.vue'
import EmptyDataPlaceholder from '@/components/placeholder/EmptyDataPlaceholder.vue'
import LoadingPlaceholder from '@/components/placeholder/LoadingPlaceholder.vue'
import Admin from '@/layouts/Admin.vue'
import type {
    ItemArrangement,
    ItemArrangementOrder,
} from '@/libraries/axios/admin/item'
import Notification from '@/libraries/primevue/toast/Notification'
import {
    getQueryParameters,
    replaceQueryParameters,
} from '@/libraries/query-parameters'
import AddItemButton from '@/pages/admin/item/detail/AddItemButton.vue'
import ItemCard from '@/pages/admin/item/detail/ItemCard.vue'
import { type AdminItemInitialState, useItemStore } from '@/stores/item.store'
import { Head, usePage } from '@inertiajs/vue3'
import debounce from 'lodash/debounce'
import type { DataViewPageEvent } from 'primevue/dataview'
import DataView from 'primevue/dataview'
import { useToast } from 'primevue/usetoast'
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: Admin })

const toast = Notification.init(useToast())
const itemStore = useItemStore()
const page = usePage()
const { t } = useI18n()

const isInitialLoading = ref(true)
let isInitializing = true
const perPageOptions = [50, 100, 150, 200]
const arrangementTypes: ItemArrangement[] = [
    'created_at',
    'sold_count',
    'view_count',
    'name',
]
const arrangementOrders: ItemArrangementOrder[] = ['asc', 'desc']

const getPositiveInteger = (value: string | null, fallback: number): number => {
    const integer = Number(value)

    return Number.isInteger(integer) && integer > 0 ? integer : fallback
}

const getCategoryIds = (parameters: URLSearchParams): number[] => {
    return [
        ...new Set(
            parameters
                .getAll('category_ids')
                .flatMap((value) => value.split(','))
                .map(Number)
                .filter((value) => Number.isInteger(value) && value > 0)
        ),
    ]
}

const getInitialState = (): AdminItemInitialState => {
    const parameters = getQueryParameters(page.url)
    const sortBy = parameters.get('sort_by')
    const sortDirection = parameters.get('sort_direction')
    const requestedPerPage = getPositiveInteger(parameters.get('per_page'), 50)

    return {
        keyword: parameters.get('keyword') ?? '',
        categoryIds: getCategoryIds(parameters),
        outOfStock: ['1', 'true'].includes(
            parameters.get('out_of_stock') ?? ''
        ),
        notListed: ['1', 'true'].includes(parameters.get('not_listed') ?? ''),
        arrangementType:
            sortBy === 'none'
                ? null
                : arrangementTypes.includes(sortBy as ItemArrangement)
                  ? (sortBy as ItemArrangement)
                  : undefined,
        arrangementOrder: arrangementOrders.includes(
            sortDirection as ItemArrangementOrder
        )
            ? (sortDirection as ItemArrangementOrder)
            : undefined,
        page: getPositiveInteger(parameters.get('page'), 1),
        perPage: perPageOptions.includes(requestedPerPage)
            ? requestedPerPage
            : 50,
    }
}

const syncFiltersToUrl = (
    currentPage = itemStore.adminPagination.currentPage,
    perPage = itemStore.adminPagination.perPage
): void => {
    const isDefaultSort =
        itemStore.filters.arrangementType === 'created_at' &&
        itemStore.filters.arrangementOrder === 'desc'

    replaceQueryParameters(page.url, {
        keyword: itemStore.filters.keyword.trim() || null,
        category_ids: itemStore.filters.categories.map(
            (category) => category.id
        ),
        out_of_stock: itemStore.filters.outOfStock ? 1 : null,
        not_listed: itemStore.filters.notListed ? 1 : null,
        sort_by: isDefaultSort
            ? null
            : (itemStore.filters.arrangementType ?? 'none'),
        sort_direction:
            isDefaultSort || itemStore.filters.arrangementType === null
                ? null
                : itemStore.filters.arrangementOrder,
        page: currentPage > 1 ? currentPage : null,
        per_page: perPage === 50 ? null : perPage,
    })
}

const showLoadingError = (error: unknown): void => {
    console.error(error)
    toast.axiosError(
        error,
        t('common.notifications.generic-error'),
        t('common.notifications.error')
    )
}

const reloadFirstPage = async (): Promise<void> => {
    try {
        syncFiltersToUrl(1)
        await itemStore.loadAdminItems(1)
    } catch (error) {
        showLoadingError(error)
    }
}

const debouncedKeywordReload = debounce(() => {
    void reloadFirstPage()
}, 300)

watch(
    () => itemStore.filters.keyword,
    () => {
        if (!isInitializing) {
            debouncedKeywordReload()
        }
    }
)

watch(
    () => [
        itemStore.filters.categories.map((category) => category.id).join(','),
        itemStore.filters.outOfStock,
        itemStore.filters.notListed,
        itemStore.filters.arrangementType,
        itemStore.filters.arrangementOrder,
    ],
    () => {
        if (!isInitializing) {
            debouncedKeywordReload.cancel()
            void reloadFirstPage()
        }
    }
)

const onPageChange = async (event: DataViewPageEvent): Promise<void> => {
    try {
        syncFiltersToUrl(event.page + 1, event.rows)
        await itemStore.onAdminPageChange(event.page + 1, event.rows)
    } catch (error) {
        showLoadingError(error)
    }
}

onMounted(async () => {
    try {
        await itemStore.initAdminPage(getInitialState())
        syncFiltersToUrl()
    } catch (error) {
        showLoadingError(error)
    } finally {
        isInitializing = false
        isInitialLoading.value = false
    }
})

onBeforeUnmount(() => {
    debouncedKeywordReload.cancel()
})
</script>

<template>
    <Head :title="t('admin.items.title')" />

    <div class="m-5">
        <div class="flex justify-between items-center mb-3">
            <div class="text-3xl">{{ t('admin.items.title') }}</div>

            <AddItemButton />
        </div>

        <LoadingPlaceholder v-if="isInitialLoading" />

        <DataView
            v-else
            :first="
                (itemStore.adminPagination.currentPage - 1) *
                itemStore.adminPagination.perPage
            "
            :rows="itemStore.adminPagination.perPage"
            :rows-per-page-options="[50, 100, 150, 200]"
            :total-records="itemStore.adminPagination.total"
            :value="itemStore.items"
            :aria-busy="itemStore.isLoading"
            data-key="id"
            layout="grid"
            lazy
            paginator
            @page="onPageChange"
        >
            <template #header>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <SearchInput v-model="itemStore.filters.keyword" />

                    <CategorySelect
                        v-model="itemStore.filters.categories"
                        :options="itemStore.categories"
                    />

                    <ArrangementDropdown
                        v-model="itemStore.filters.arrangementType"
                        :on-order-change="itemStore.onArrangementOrderChange"
                        :order="itemStore.filters.arrangementOrder"
                    />

                    <div class="flex">
                        <IsOutOfStockCheckbox
                            v-model="itemStore.filters.outOfStock"
                        />

                        <IsNotListedCheckbox
                            v-model="itemStore.filters.notListed"
                        />
                    </div>
                </div>
            </template>

            <template #empty>
                <EmptyDataPlaceholder
                    :text="t('common.placeholders.no-items')"
                />
            </template>

            <template #grid="{ items }">
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-3"
                >
                    <ItemCard
                        v-for="item in items"
                        :key="item.id"
                        :item="item"
                    />
                </div>
            </template>
        </DataView>
    </div>
</template>
