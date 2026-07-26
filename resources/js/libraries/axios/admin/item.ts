import adminAjax from '@/libraries/axios/common/admin-ajax'
import {
    destroy as destroyItem,
    index as itemIndex,
    store as storeItem,
    update as updateItemRoute,
} from '@/routes/admin/ajax/item'
import { update as updateItemListingRoute } from '@/routes/admin/ajax/item/listing'
import { reset as resetItemSoldCountRoute } from '@/routes/admin/ajax/item/sold-count'
import { reset as resetItemViewCountRoute } from '@/routes/admin/ajax/item/view-count'
import type { Identifier, Item, PaginatedResponse } from '@/types'

export type ItemListing = Pick<Item, 'id' | 'is_listed'>
export type ItemCounters = Pick<Item, 'id' | 'view_count' | 'sold_count'>
export type ItemArrangement =
    'created_at' | 'sold_count' | 'view_count' | 'name'
export type ItemArrangementOrder = 'asc' | 'desc'
export interface AdminItemIndexQuery {
    keyword?: string
    category_ids?: Identifier[]
    out_of_stock?: boolean
    not_listed?: boolean
    sort_by: ItemArrangement
    sort_direction: ItemArrangementOrder
    page: number
    per_page: number
}
export interface UpdateItemDetailData {
    name: string
    name_en: string | null
    desc: string | null
    origin_id: Identifier | null
    category_ids: Identifier[]
}

export const getAdminItems = async (
    query: AdminItemIndexQuery
): Promise<PaginatedResponse<Item>> => {
    return await adminAjax.get<PaginatedResponse<Item>>(
        itemIndex.url({
            query: {
                keyword: query.keyword,
                category_ids: query.category_ids,
                out_of_stock: query.out_of_stock,
                not_listed: query.not_listed,
                sort_by: query.sort_by,
                sort_direction: query.sort_direction,
                page: query.page,
                per_page: query.per_page,
            },
        })
    )
}

export const createItem = async (name: string): Promise<Item> => {
    return await adminAjax.post<Item>(storeItem.url(), { name })
}

export const updateItemDetail = async (
    itemId: Identifier,
    data: UpdateItemDetailData
): Promise<Item> => {
    return await adminAjax.put<Item>(updateItemRoute.url(itemId), data)
}

export const deleteItem = async (itemId: Identifier): Promise<void> => {
    await adminAjax.delete<void>(destroyItem.url(itemId))
}

export const updateItemListing = async (
    itemId: Identifier,
    isListed: boolean
): Promise<ItemListing> => {
    return await adminAjax.patch<ItemListing>(
        updateItemListingRoute.url(itemId),
        {
            is_listed: isListed,
        }
    )
}

export const resetItemViewCount = async (
    itemId: Identifier
): Promise<ItemCounters> => {
    return await adminAjax.patch<ItemCounters>(
        resetItemViewCountRoute.url(itemId)
    )
}

export const resetItemSoldCount = async (
    itemId: Identifier
): Promise<ItemCounters> => {
    return await adminAjax.patch<ItemCounters>(
        resetItemSoldCountRoute.url(itemId)
    )
}
