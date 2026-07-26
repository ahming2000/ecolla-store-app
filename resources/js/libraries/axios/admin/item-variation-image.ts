import adminAjax from '@/libraries/axios/common/admin-ajax'
import {
    destroy as destroyVariationImageRoute,
    store as storeVariationImageRoute,
} from '@/routes/admin/ajax/item/variation/image'
import type { Identifier, Variation } from '@/types'

export const attachVariationImage = async (
    itemId: Identifier,
    variationId: Identifier,
    imageId: Identifier
): Promise<Variation> => {
    return await adminAjax.post<Variation>(
        storeVariationImageRoute.url({
            item: itemId,
            variation: variationId,
        }),
        { image_id: imageId }
    )
}

export const removeVariationImage = async (
    itemId: Identifier,
    variationId: Identifier
): Promise<Variation> => {
    return await adminAjax.delete<Variation>(
        destroyVariationImageRoute.url({
            item: itemId,
            variation: variationId,
        })
    )
}
