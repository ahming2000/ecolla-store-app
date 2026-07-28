<script setup lang="ts">
import ArrangementDropdown from '@/components/filterer/item/ArrangementDropdown.vue'
import CategorySelect from '@/components/filterer/item/CategorySelect.vue'
import OriginSelect from '@/components/filterer/item/OriginSelect.vue'
import SearchInput from '@/components/filterer/item/SearchInput.vue'
import EmptyDataPlaceholder from '@/components/placeholder/EmptyDataPlaceholder.vue'
import LoadingPlaceholder from '@/components/placeholder/LoadingPlaceholder.vue'
import Shop from '@/layouts/Shop.vue'
import type {
    ItemArrangement,
    ItemArrangementOrder,
} from '@/libraries/axios/admin/item'
import {
    getQueryParameters,
    replaceQueryParameters,
} from '@/libraries/query-parameters'
import ItemCard from '@/pages/shop/common/ItemCard.vue'
import { useItemStore } from '@/stores/item.store'
import { Head, usePage } from '@inertiajs/vue3'
import debounce from 'lodash/debounce'
import DataView from 'primevue/dataview'
import { onBeforeUnmount, watch } from 'vue'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: Shop })

const itemStore = useItemStore()
const page = usePage()
const { t } = useI18n()

const arrangementTypes: ItemArrangement[] = [
    'created_at',
    'sold_count',
    'view_count',
    'name',
]
const arrangementOrders: ItemArrangementOrder[] = ['asc', 'desc']

const getIdentifierParameters = (
    parameters: URLSearchParams,
    key: string
): number[] => {
    return [
        ...new Set(
            parameters
                .getAll(key)
                .flatMap((value) => value.split(','))
                .map(Number)
                .filter((value) => Number.isInteger(value) && value > 0)
        ),
    ]
}

const syncFiltersFromUrl = (): void => {
    if (!itemStore.isShopInitialized) {
        return
    }

    const parameters = getQueryParameters(page.url)
    const originIds = getIdentifierParameters(parameters, 'origin')
    const categoryIds = getIdentifierParameters(parameters, 'category')
    const arrangementType = parameters.get('sort_by')
    const arrangementOrder = parameters.get('sort_direction')

    itemStore.filters.keyword = parameters.get('keyword') ?? ''
    itemStore.filters.origins = itemStore.origins.filter((origin) =>
        originIds.includes(Number(origin.id))
    )
    itemStore.filters.categories = itemStore.categories.filter((category) =>
        categoryIds.includes(Number(category.id))
    )
    itemStore.filters.arrangementType = arrangementTypes.includes(
        arrangementType as ItemArrangement
    )
        ? (arrangementType as ItemArrangement)
        : null
    itemStore.filters.arrangementOrder = arrangementOrders.includes(
        arrangementOrder as ItemArrangementOrder
    )
        ? (arrangementOrder as ItemArrangementOrder)
        : 'asc'
}

const syncFiltersToUrl = (): void => {
    if (!itemStore.isShopInitialized) {
        return
    }

    replaceQueryParameters(page.url, {
        keyword: itemStore.filters.keyword.trim() || null,
        origin: itemStore.filters.origins.map((origin) => origin.id),
        category: itemStore.filters.categories.map((category) => category.id),
        sort_by: itemStore.filters.arrangementType,
        sort_direction: itemStore.filters.arrangementType
            ? itemStore.filters.arrangementOrder
            : null,
    })
}

const debouncedFilterQuerySync = debounce(syncFiltersToUrl, 200)

watch([() => page.url, () => itemStore.isShopInitialized], syncFiltersFromUrl, {
    immediate: true,
})

watch(
    () => itemStore.filters.keyword,
    () => {
        debouncedFilterQuerySync()
    }
)

watch(
    () => [
        itemStore.filters.origins.map((origin) => origin.id).join(','),
        itemStore.filters.categories.map((category) => category.id).join(','),
        itemStore.filters.arrangementType,
        itemStore.filters.arrangementOrder,
    ],
    () => {
        debouncedFilterQuerySync.cancel()
        syncFiltersToUrl()
    }
)

onBeforeUnmount(() => {
    debouncedFilterQuerySync.cancel()
})
</script>

<template>
    <div>
        <Head :title="t('shop.item-list.title')" />

        <div class="container mx-auto my-5">
            <LoadingPlaceholder v-if="itemStore.isLoading" />

            <DataView
                v-else
                :rows="50"
                :rows-per-page-options="[50, 100, 150, 200]"
                :value="itemStore.filteredItems"
                layout="grid"
                paginator
            >
                <template #header>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <SearchInput v-model="itemStore.filters.keyword" />

                        <CategorySelect
                            v-model="itemStore.filters.categories"
                            :options="itemStore.categories"
                        />

                        <OriginSelect
                            v-model="itemStore.filters.origins"
                            :options="itemStore.origins"
                        />

                        <ArrangementDropdown
                            v-model="itemStore.filters.arrangementType"
                            :on-order-change="
                                itemStore.onArrangementOrderChange
                            "
                            :order="itemStore.filters.arrangementOrder"
                        />
                    </div>
                </template>

                <template #empty>
                    <EmptyDataPlaceholder :text="t('shop.item-list.empty')" />
                </template>

                <template #grid="{ items }">
                    <div
                        class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 p-3"
                    >
                        <ItemCard
                            v-for="(item, index) in items"
                            :key="item.id"
                            :index="index"
                            :item="item"
                        />
                    </div>
                </template>
            </DataView>
        </div>
    </div>
</template>
