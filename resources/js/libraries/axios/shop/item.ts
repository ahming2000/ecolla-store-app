import shopAjax from '@/libraries/axios/common/shop-ajax'
import { index as itemIndex } from '@/routes/shop/ajax/item'
import type { Item } from '@/types'

export const getShopItems = async (
    barcodes: string[] = []
): Promise<Item[]> => {
    return await shopAjax.get<Item[]>(itemIndex.url(), {
        params: barcodes.length > 0 ? { barcodes } : undefined,
    })
}
