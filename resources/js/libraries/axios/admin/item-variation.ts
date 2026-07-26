import adminAjax from '@/libraries/axios/common/admin-ajax'
import {
    destroy as destroyVariationRoute,
    store as storeVariationRoute,
    update as updateVariationRoute,
} from '@/routes/admin/ajax/item/variation'
import type { Identifier, Variation, VariationFormData } from '@/types'

export const createVariation = async (
    itemId: Identifier,
    data: VariationFormData
): Promise<Variation> => {
    return await adminAjax.post<Variation>(
        storeVariationRoute.url(itemId),
        data
    )
}

export const updateVariation = async (
    itemId: Identifier,
    variationId: Identifier,
    data: VariationFormData
): Promise<Variation> => {
    return await adminAjax.put<Variation>(
        updateVariationRoute.url({
            item: itemId,
            variation: variationId,
        }),
        data
    )
}

export const deleteVariation = async (
    itemId: Identifier,
    variationId: Identifier
): Promise<void> => {
    await adminAjax.delete<void>(
        destroyVariationRoute.url({
            item: itemId,
            variation: variationId,
        })
    )
}
