import { getAdminCategories } from '@/libraries/axios/admin/category'
import type {
    ItemArrangement,
    ItemArrangementOrder,
    UpdateItemDetailData,
} from '@/libraries/axios/admin/item'
import {
    createItem,
    deleteItem,
    getAdminItems,
    resetItemSoldCount,
    resetItemViewCount,
    updateItemDetail,
    updateItemListing,
} from '@/libraries/axios/admin/item'
import {
    attachItemImage,
    removeItemImage,
} from '@/libraries/axios/admin/item-image'
import {
    createVariation,
    deleteVariation,
    updateVariation,
} from '@/libraries/axios/admin/item-variation'
import {
    attachVariationImage,
    removeVariationImage,
} from '@/libraries/axios/admin/item-variation-image'
import { getAdminOrigins } from '@/libraries/axios/admin/origin'
import { getShopCategories } from '@/libraries/axios/shop/category'
import { getShopItems } from '@/libraries/axios/shop/item'
import { getShopOrigins } from '@/libraries/axios/shop/origin'
import type {
    Category,
    Identifier,
    Item,
    Origin,
    ResettableItemCounter,
    VariationFormData,
} from '@/types'
import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

interface ItemFilters {
    keyword: string
    origins: Origin[]
    categories: Category[]
    outOfStock: boolean
    notListed: boolean
    arrangementType: ItemArrangement | null
    arrangementOrder: ItemArrangementOrder
}

interface AdminItemPagination {
    currentPage: number
    lastPage: number
    perPage: number
    total: number
}

export interface AdminItemInitialState {
    keyword?: string
    categoryIds?: Identifier[]
    outOfStock?: boolean
    notListed?: boolean
    arrangementType?: ItemArrangement | null
    arrangementOrder?: ItemArrangementOrder
    page?: number
    perPage?: number
}

export const useItemStore = defineStore('items', () => {
    const items = ref<Item[]>([])
    const categories = ref<Category[]>([])
    const origins = ref<Origin[]>([])

    const isLoading = ref(false)
    const isShopInitialized = ref(false)
    const isAdminPage = ref(false)
    const adminPagination = ref<AdminItemPagination>({
        currentPage: 1,
        lastPage: 1,
        perPage: 50,
        total: 0,
    })
    let latestAdminRequest = 0

    const filteredItems = computed(() => {
        if (items.value.length === 0) {
            return []
        }

        let result = [...items.value]

        if (filters.value.keyword) {
            result = result.filter((item) => {
                return (
                    (item.name && item.name.includes(filters.value.keyword)) ||
                    (item.desc && item.desc.includes(filters.value.keyword)) ||
                    item.variations.some((variation) => {
                        return (
                            variation.barcode.includes(filters.value.keyword) ||
                            variation.name.includes(filters.value.keyword) ||
                            variation.name_en.includes(filters.value.keyword)
                        )
                    })
                )
            })
        }

        if (filters.value.categories.length !== 0) {
            result = result.filter((item) => {
                return item.categories.some((category) => {
                    return filters.value.categories.some((filteredCategory) => {
                        return category.id === filteredCategory.id
                    })
                })
            })
        }

        if (filters.value.origins.length !== 0) {
            result = result.filter((item) => {
                return filters.value.origins.some((origin) => {
                    return item.origin_id === origin.id
                })
            })
        }

        if (filters.value.outOfStock) {
            result = result.filter((item) => {
                return item.total_stock === 0
            })
        }

        if (filters.value.notListed) {
            result = result.filter((item) => {
                return !item.is_listed
            })
        }

        if (filters.value.arrangementType && filters.value.arrangementOrder) {
            if (filters.value.arrangementOrder === 'desc') {
                result = result.sort((a, b) => {
                    if (filters.value.arrangementType === 'sold_count') {
                        return b.sold_count - a.sold_count
                    } else if (filters.value.arrangementType === 'view_count') {
                        return b.view_count - a.view_count
                    } else if (filters.value.arrangementType === 'name') {
                        return b.name.localeCompare(a.name)
                    }

                    return Date.parse(b.created_at) - Date.parse(a.created_at)
                })
            } else {
                result = result.sort((a, b) => {
                    if (filters.value.arrangementType === 'sold_count') {
                        return a.sold_count - b.sold_count
                    } else if (filters.value.arrangementType === 'view_count') {
                        return a.view_count - b.view_count
                    } else if (filters.value.arrangementType === 'name') {
                        return a.name.localeCompare(b.name)
                    }

                    return Date.parse(a.created_at) - Date.parse(b.created_at)
                })
            }
        }

        return result
    })

    const filters = ref<ItemFilters>({
        keyword: '',
        origins: [],
        categories: [],
        outOfStock: false,
        notListed: false,
        arrangementType: null,
        arrangementOrder: 'asc',
    })

    const resetFilters = (
        arrangementType: ItemArrangement | null,
        arrangementOrder: ItemArrangementOrder
    ): void => {
        filters.value = {
            keyword: '',
            origins: [],
            categories: [],
            outOfStock: false,
            notListed: false,
            arrangementType,
            arrangementOrder,
        }
    }

    const initShopPage = async (): Promise<void> => {
        isAdminPage.value = false
        isShopInitialized.value = false
        resetFilters(null, 'asc')

        try {
            isLoading.value = true
            categories.value = await getShopCategories()
            origins.value = await getShopOrigins()
            items.value = await getShopItems()
            isShopInitialized.value = true
        } catch (e) {
            throw e
        } finally {
            isLoading.value = false
        }
    }

    const loadAdminItems = async (
        page = adminPagination.value.currentPage,
        perPage = adminPagination.value.perPage
    ): Promise<void> => {
        const requestId = ++latestAdminRequest

        try {
            isLoading.value = true

            const response = await getAdminItems({
                keyword: filters.value.keyword.trim() || undefined,
                category_ids:
                    filters.value.categories.length > 0
                        ? filters.value.categories.map(
                              (category) => category.id
                          )
                        : undefined,
                out_of_stock: filters.value.outOfStock || undefined,
                not_listed: filters.value.notListed || undefined,
                sort_by: filters.value.arrangementType ?? 'created_at',
                sort_direction: filters.value.arrangementOrder,
                page,
                per_page: perPage,
            })

            if (requestId !== latestAdminRequest) {
                return
            }

            if (response.current_page > response.last_page) {
                await loadAdminItems(response.last_page, response.per_page)

                return
            }

            items.value = response.data
            adminPagination.value = {
                currentPage: response.current_page,
                lastPage: response.last_page,
                perPage: response.per_page,
                total: response.total,
            }
        } catch (e) {
            if (requestId === latestAdminRequest) {
                throw e
            }
        } finally {
            if (requestId === latestAdminRequest) {
                isLoading.value = false
            }
        }
    }

    const initAdminPage = async (
        initialState: AdminItemInitialState = {}
    ): Promise<void> => {
        isAdminPage.value = true
        resetFilters('created_at', 'desc')
        adminPagination.value = {
            currentPage: 1,
            lastPage: 1,
            perPage: initialState.perPage ?? 50,
            total: 0,
        }

        try {
            isLoading.value = true
            const [adminCategories, adminOrigins] = await Promise.all([
                getAdminCategories(),
                getAdminOrigins(),
            ])

            categories.value = adminCategories
            origins.value = adminOrigins

            filters.value.keyword = initialState.keyword ?? ''
            filters.value.categories = categories.value.filter((category) =>
                initialState.categoryIds?.includes(category.id)
            )
            filters.value.outOfStock = initialState.outOfStock ?? false
            filters.value.notListed = initialState.notListed ?? false
            filters.value.arrangementType =
                initialState.arrangementType === undefined
                    ? 'created_at'
                    : initialState.arrangementType
            filters.value.arrangementOrder =
                initialState.arrangementOrder ?? 'desc'

            await loadAdminItems(
                initialState.page ?? 1,
                adminPagination.value.perPage
            )
        } catch (e) {
            throw e
        } finally {
            isLoading.value = false
        }
    }

    const onAdminPageChange = async (
        page: number,
        perPage: number
    ): Promise<void> => {
        await loadAdminItems(page, perPage)
    }

    const onArrangementOrderChange = (): void => {
        if (filters.value.arrangementOrder === 'asc') {
            filters.value.arrangementOrder = 'desc'
        } else if (filters.value.arrangementOrder === 'desc') {
            filters.value.arrangementOrder = 'asc'
        }
    }

    const onCreateItem = async (name: string): Promise<void> => {
        try {
            const createdItem = await createItem(name)

            if (isAdminPage.value) {
                await loadAdminItems(1)
            } else {
                items.value.unshift(createdItem)
            }
        } catch (e) {
            throw e
        }
    }

    const updateStoredItem = (updatedItem: Item): void => {
        const item = items.value.find(
            (currentItem) => currentItem.id === updatedItem.id
        )

        if (item) {
            Object.assign(item, updatedItem)
        }
    }

    const onUploadItemImage = async (
        itemId: Identifier,
        imageId: Identifier
    ): Promise<void> => {
        updateStoredItem(await attachItemImage(itemId, imageId))
    }

    const onRemoveItemImage = async (
        itemId: Identifier,
        imageId: Identifier
    ): Promise<void> => {
        updateStoredItem(await removeItemImage(itemId, imageId))
    }

    const onUpdateListStatus = async (
        itemId: Identifier,
        isListed: boolean
    ): Promise<void> => {
        const updatedListing = await updateItemListing(itemId, isListed)
        const item = items.value.find(
            (currentItem) => currentItem.id === updatedListing.id
        )

        if (item) {
            item.is_listed = updatedListing.is_listed
        }
    }

    const onUpdateItemDetail = async (
        itemId: Identifier,
        data: UpdateItemDetailData
    ): Promise<void> => {
        updateStoredItem(await updateItemDetail(itemId, data))
    }

    const onResetItemMiscellaneousDetail = async (
        itemId: Identifier,
        counter: ResettableItemCounter
    ): Promise<void> => {
        const updatedCounters =
            counter === 'view_count'
                ? await resetItemViewCount(itemId)
                : await resetItemSoldCount(itemId)
        const item = items.value.find(
            (currentItem) => currentItem.id === updatedCounters.id
        )

        if (item) {
            item.view_count = updatedCounters.view_count
            item.sold_count = updatedCounters.sold_count
        }
    }

    const onDeleteItem = async (itemId: Identifier): Promise<void> => {
        await deleteItem(itemId)

        if (isAdminPage.value) {
            await loadAdminItems()
        } else {
            items.value = items.value.filter((item) => item.id !== itemId)
        }
    }

    const updateVariationTotals = (item: Item): void => {
        item.total_stock = item.variations.reduce(
            (total, variation) => total + variation.stock,
            0
        )
        item.total_image_count = item.images.length + item.variations.length
    }

    const onCreateVariation = async (
        itemId: Identifier,
        data: VariationFormData
    ): Promise<Item['variations'][number]> => {
        const createdVariation = await createVariation(itemId, data)
        const item = items.value.find(
            (currentItem) => currentItem.id === itemId
        )

        if (item) {
            item.variations.push(createdVariation)
            updateVariationTotals(item)
        }

        return createdVariation
    }

    const onUpdateVariation = async (
        itemId: Identifier,
        variationId: Identifier,
        data: VariationFormData
    ): Promise<void> => {
        const updatedVariation = await updateVariation(
            itemId,
            variationId,
            data
        )
        const item = items.value.find(
            (currentItem) => currentItem.id === itemId
        )
        const variationIndex = item?.variations.findIndex(
            (variation) => variation.id === variationId
        )

        if (item && variationIndex !== undefined && variationIndex >= 0) {
            item.variations.splice(variationIndex, 1, updatedVariation)
            updateVariationTotals(item)
        }
    }

    const onDeleteVariation = async (
        itemId: Identifier,
        variationId: Identifier
    ): Promise<void> => {
        await deleteVariation(itemId, variationId)

        const item = items.value.find(
            (currentItem) => currentItem.id === itemId
        )

        if (item) {
            item.variations = item.variations.filter(
                (variation) => variation.id !== variationId
            )
            item.is_listed =
                item.variations.length === 0 ? false : item.is_listed
            updateVariationTotals(item)
        }
    }

    const updateStoredVariation = (
        itemId: Identifier,
        updatedVariation: Item['variations'][number]
    ): void => {
        const item = items.value.find(
            (currentItem) => currentItem.id === itemId
        )
        const variationIndex = item?.variations.findIndex(
            (variation) => variation.id === updatedVariation.id
        )

        if (item && variationIndex !== undefined && variationIndex >= 0) {
            item.variations.splice(variationIndex, 1, updatedVariation)
            updateVariationTotals(item)
        }
    }

    const onUploadVariationImage = async (
        itemId: Identifier,
        variationId: Identifier,
        imageId: Identifier
    ): Promise<void> => {
        updateStoredVariation(
            itemId,
            await attachVariationImage(itemId, variationId, imageId)
        )
    }

    const onRemoveVariationImage = async (
        itemId: Identifier,
        variationId: Identifier
    ): Promise<void> => {
        updateStoredVariation(
            itemId,
            await removeVariationImage(itemId, variationId)
        )
    }

    return {
        items,
        categories,
        origins,

        isLoading,
        isShopInitialized,
        adminPagination,

        filteredItems,
        filters,
        onArrangementOrderChange,
        onAdminPageChange,

        initShopPage,
        initAdminPage,
        loadAdminItems,

        onCreateItem,
        onUploadItemImage,
        onRemoveItemImage,
        onUpdateListStatus,
        onUpdateItemDetail,
        onDeleteItem,
        onResetItemMiscellaneousDetail,

        onCreateVariation,
        onUpdateVariation,
        onDeleteVariation,
        onUploadVariationImage,
        onRemoveVariationImage,
    }
})
