import shopAjax from '@/libraries/axios/common/shop-ajax'
import { index as originIndex } from '@/routes/shop/ajax/origin'
import type { Origin } from '@/types'

export const getShopOrigins = async (): Promise<Origin[]> => {
    return await shopAjax.get<Origin[]>(originIndex.url())
}
