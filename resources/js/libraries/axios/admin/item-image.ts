import adminAjax from '@/libraries/axios/common/admin-ajax'
import {
    destroy as destroyItemImageRoute,
    store as storeItemImageRoute,
} from '@/routes/admin/ajax/item/image'
import type { Identifier, Item } from '@/types'

export const attachItemImage = async (
    itemId: Identifier,
    imageId: Identifier
): Promise<Item> => {
    return await adminAjax.post<Item>(
        storeItemImageRoute.url({
            item: itemId,
            image: imageId,
        })
    )
}

export const removeItemImage = async (
    itemId: Identifier,
    imageId: Identifier
): Promise<Item> => {
    return await adminAjax.delete<Item>(
        destroyItemImageRoute.url({
            item: itemId,
            image: imageId,
        })
    )
}
